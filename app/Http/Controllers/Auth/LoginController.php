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
        // Check if user is admin
        if ($user->is_admin || $user->hasRole('admin')) { // Adjust this condition based on your admin setup
            return redirect()->intended('/dashboard');
        }

        // Regular users go to welcome page
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
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            $isNewUser = false;

            if (!$user) {
                Log::info('Creating new user for Google sign-in: ' . $googleUser->getEmail());
                $isNewUser = true;

                // Get default country and city
                $country = countries::findOrFail(1);
                $city = null;
                if ($country) {
                    $city = cities::findOrFail(1);
                }

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

                // Create the user
                $user = User::create($userData);

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
