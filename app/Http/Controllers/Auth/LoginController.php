<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Photos;
use App\Models\Notification;
use App\Models\Role;
use App\Models\cities;
use App\Models\countries;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        Log::info('User authenticated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'auth_type' => $user->auth_type,
            'is_admin' => $user->is_admin,
            'has_role_admin' => $user->hasRole('admin')
        ]);

        // Check if user is admin
        if ($user->is_admin || $user->hasRole('admin')) { // Adjust this condition based on your admin setup
            Log::info('Redirecting admin user to dashboard');
            return redirect()->intended('/dashboard');
        }

        // For Google users, redirect to a specific page or dashboard
        if ($user->auth_type === 'google') {
            Log::info('Redirecting Google user to dashboard');
            return redirect()->intended('/dashboard');
        }

        // Regular users go to welcome page
        Log::info('Redirecting regular user to home page');
        return redirect()->intended('/');
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        // Add debugging
        Log::info('Google callback received', [
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google user data retrieved', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            $isNewUser = false;

            if (!$user) {
                Log::info('Creating new user for Google sign-in: ' . $googleUser->getEmail());
                $isNewUser = true;

                try {
                    // Get default country and city
                    $country = countries::findOrFail(1);
                    $city = null;
                    if ($country) {
                        $city = cities::findOrFail(1);
                    }

                    Log::info('Country and city found', [
                        'country_id' => $country ? $country->id : null,
                        'city_id' => $city ? $city->id : null
                    ]);

                    // Create user data array
                    $userData = [
                        'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                        'user_name' => $this->generateUsername($googleUser->getName() ?? $googleUser->getNickname() ?? 'user'),
                        'email' => $googleUser->getEmail(),
                        'password' => Hash::make(Str::random(16)),
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => now(),
                        'is_active' => 'active',
                        'auth_type' => 'google',
                        'gender' => 'ذكر', // Default gender
                        'data_saver_enabled' => false,
                    ];

                    // Set country and city if available
                    if ($country) {
                        $userData['country_id'] = $country->id;
                    }
                    if ($city) {
                        $userData['city_id'] = $city->id;
                    }

                    Log::info('Creating user with data', $userData);

                    // Create the user
                    $user = User::create($userData);

                    Log::info('User created successfully', ['user_id' => $user->id]);

                    // Create profile photo
                    $photoUrl = $googleUser->getAvatar();
                    if (!empty($photoUrl)) {
                        $photo = new Photos([
                            'src' => $photoUrl,
                            'is_external' => true
                        ]);
                    } else {
                        $photo = new Photos([
                            'src' => fake()->randomElement([
                                'storage/photos/avatar1.png',
                                'storage/photos/avatar2.png',
                                'storage/photos/avatar3.png',
                                'storage/photos/avatar4.png',
                                'storage/photos/avatar5.png'
                            ]),
                        ]);
                    }
                    $user->photos()->save($photo);

                    Log::info('Profile photo created');

                    // Assign role to user
                    if (class_exists('App\\Models\\Role')) {
                        $role = Role::where('name', 'user')->first();
                        if ($role) {
                            try {
                                // Manually attach role
                                $user->roles()->attach($role->id, ['user_type' => get_class($user)]);

                                // Get all permissions associated with this role
                                $permissions = $role->permissions;

                                // Sync permissions
                                if ($permissions->isNotEmpty()) {
                                    $permissionIds = $permissions->pluck('id')->toArray();
                                    $user->permissions()->sync($permissionIds);
                                }

                                Log::info('Role and permissions assigned');
                            } catch (\Exception $e) {
                                Log::error('Role and permission sync failed', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
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

                    Log::info('Welcome notification created');

                } catch (\Exception $e) {
                    Log::error('Error creating new user', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                // Update existing user's Google info
                Log::info('Updating existing user for Google sign-in: ' . $user->id);

                // If the user doesn't have a google_id yet, update it
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                    $user->auth_type = 'google';
                }

                // Update profile photo if provided and user doesn't have one
                $photoUrl = $googleUser->getAvatar();
                if ($photoUrl) {
                    $existingPhoto = $user->photos()->first();
                    if (!$existingPhoto) {
                        $photo = new Photos([
                            'src' => $photoUrl,
                            'is_external' => true,
                        ]);
                        $user->photos()->save($photo);
                    }
                }

                $user->save();
            }

            // Log the user in
            auth()->login($user, true);

            Log::info('Google login successful', [
                'user_id' => $user->id,
                'is_new_user' => $isNewUser
            ]);

            // Redirect based on role
            return $this->authenticated(request(), $user);
        } catch (\Exception $e) {
            Log::error('Google authentication error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('login')->withErrors(['google' => 'Google login failed: ' . $e->getMessage()]);
        }
    }

    // Helper to generate a unique username
    private function generateUsername($name)
    {
        $baseUsername = strtolower(str_replace(' ', '', $name));
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }
        $username = $baseUsername . rand(100, 9999);
        while (User::where('user_name', $username)->exists()) {
            $username = $baseUsername . rand(100, 9999);
        }
        return $username;
    }
}
