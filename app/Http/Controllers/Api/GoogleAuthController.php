<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Photos;
use App\Models\Notification;
use App\Models\Role;
use App\Models\cities;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Google\Client as GoogleClient;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google authentication from the Flutter app
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Handle Google authentication from the Flutter app
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleAuth(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'access_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photo_url' => 'nullable|string',
            'device_token' => 'nullable|string', // Add validation for device_token
        ]);

        if ($validator->fails()) {
            Log::error('Google auth validation failed: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            Log::info('Received Google auth request from: ' . $request->email);

            // Verify Google ID token - UNCOMMENT THIS FOR PRODUCTION!
            // This is critical for security to verify that the token is valid
            $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                Log::error('Invalid Google ID token');
                return response()->json(['error' => 'Invalid Google ID token'], 401);
            }

            Log::info('Google ID token verified successfully for: ' . $request->email);

            // Get device token from request
            $deviceToken = $request->input('device_token');
            $hasValidDeviceToken = !is_null($deviceToken) && trim($deviceToken) !== '';

            // Find existing user or create new one
            $user = User::where('email', $request->email)->first();
            $isNewUser = false;

            if (!$user) {
                Log::info('Creating new user for Google sign-in: ' . $request->email);
                $isNewUser = true;

                // Get default country and city
                $country = Countries::findOrFail(1);
                $city = null;
                if ($country) {
                    $city = Cities::findOrFail(1);
                }

                // Create user data array
                $userData = [
                    'name' => $request->name,
                    'user_name' => $this->generateUsername($request->name),
                    'email' => $request->email,
                    'password' => Hash::make(Str::random(16)), // Random password for OAuth users

                    // Use a value from Google payload instead of generating a random UUID
                    'google_id' => $payload['sub'] ?? Str::uuid()->toString(),

                    'email_verified_at' => now(), // Email is verified by Google
                    'is_active' => 'active',
                    'gender' => 'ذكر', // Default gender
                    'auth_type' => 'google',
                    'data_saver_enabled' => false,
                ];

                // Set country and city if available
                if ($country) {
                    $userData['country_id'] = $country->id;
                }
                if ($city) {
                    $userData['city_id'] = $city->id;
                }

                // Add FCM token if available
                if ($hasValidDeviceToken) {
                    $userData['fcm_token'] = $deviceToken;
                    Log::info('Added FCM token during Google registration', [
                        'email' => $request->email,
                        'token_length' => strlen($deviceToken)
                    ]);
                } else {
                    Log::info('No valid FCM token provided during Google registration', [
                        'email' => $request->email
                    ]);
                }

                // Create the user
                $user = User::create($userData);

                // Create a default avatar
                $photoUrl = $request->photo_url;
                if (!empty($photoUrl)) {
                    $photo = new Photos([
                        'src' => $photoUrl,
                        'is_external' => true  // Add this line for Google photos
                    ]);
                } else {
                    $photo = new Photos([
                        'src' => fake()->randomElement(['storage/photos/avatar1.png', 'storage/photos/avatar2.png', 'storage/photos/avatar3.png', 'storage/photos/avatar4.png', 'storage/photos/avatar5.png']),
                    ]);
                }
                $user->photos()->save($photo);

                // Assign default 'user' role and permissions (same as RegisterController)
                $role = Role::where('name', 'user')->first();
                if ($role) {
                    $user->attachRole($role);
                    $user->syncPermissions($role->permissions);

                    Log::info('Role and permissions assigned to Google user', [
                        'user_id' => $user->id,
                        'role' => $role->name,
                        'permission_count' => $role->permissions->count(),
                    ]);
                } else {
                    Log::error('Default "user" role not found — Google user has no role', [
                        'user_id' => $user->id,
                        'available_roles' => Role::pluck('name')->toArray(),
                    ]);
                }

                // Create welcome notification
                $message = json_encode([
                    'en' => "🎉 Welcome to our app! We're thrilled to have you here.",
                    'ar' => "🎉 مرحبًا بك في تطبيقنا! نحن سعداء بانضمامك إلينا."
                ]);

                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $user->id,
                    'type' => 'login'
                ]);
                $user->notifications()->save($notification);
            } else {
                // Update existing user's Google info
                Log::info('Updating existing user for Google sign-in: ' . $user->id);

                // If the user doesn't have a google_id yet, update it from this login
                if (empty($user->google_id)) {
                    $user->google_id = $payload['sub'] ?? Str::uuid()->toString();
                    $user->auth_type = 'google';
                }

                // Update profile photo if provided
                if ($request->photo_url) {
                    $existingPhoto = $user->photos()->first();
                    if (!$existingPhoto) {
                        $photo = new Photos([
                            'src' => $request->photo_url,
                            'is_external' => true,
                        ]);
                        $user->photos()->save($photo);
                    }
                }

                // Update FCM token if provided and valid
                if ($hasValidDeviceToken) {
                    $user->fcm_token = $deviceToken;
                    Log::info('Updated FCM token during Google sign-in', [
                        'user_id' => $user->id,
                        'token_length' => strlen($deviceToken)
                    ]);
                }

                $user->save();
            }

            // Create token for the user - add a longer expiration
            $tokenResult = $user->createToken('google-auth-token');
            $token = $tokenResult->accessToken; // Use accessToken instead of plainTextToken

            // Return user info and token
            Log::info('Google sign-in successful for user: ' . $user->id);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->photos()->first() ? $user->photos()->first()->src : null,
                    'profile_photo_is_external' => $user->photos()->first() ? $user->photos()->first()->is_external : false,
                    'gender' => $user->gender,
                    'country_id' => $user->country_id,
                    'city_id' => $user->city_id,
                    'phones' => $user->phones,
                    'WatsNumber' => $user->WatsNumber,
                    'date_of_birth' => $user->date_of_birth,
                    'is_active' => $user->is_active,
                    'data_saver_enabled' => $user->data_saver_enabled,
                ],
                'token' => $token,
                'is_new_user' => $isNewUser,
                'message' => 'User authenticated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Google authentication error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Generate a username based on the name
     *
     * @param string $name
     * @return string
     */
    private function generateUsername($name)
    {
        // Convert name to lowercase and remove spaces
        $baseUsername = strtolower(str_replace(' ', '', $name));

        // Remove any non-alphanumeric characters
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);

        // If the username is empty after cleaning, use a default
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        // Add a random number to make it unique
        $username = $baseUsername . rand(100, 9999);

        // Check if username exists
        while (User::where('user_name', $username)->exists()) {
            $username = $baseUsername . rand(100, 9999);
        }

        return $username;
    }

    /**
     * Logout user and revoke token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the request
            $request->user()->currentAccessToken()->delete();

            // Clear FCM token if needed
            $user = $request->user();
            $user->fcm_token = null;
            $user->save();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    /**
     * Toggle data saver mode for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleDataSaver(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get authenticated user
            $user = Auth::user();

            // Update data saver setting
            $user->data_saver_enabled = $request->enabled;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data saver setting updated successfully',
                'data' => [
                    'data_saver_enabled' => $user->data_saver_enabled
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update data saver setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
