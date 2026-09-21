<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\cities;
use App\Models\countries;
use App\Models\Notification;
use App\Models\Photos;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AppleIdTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Sign in with Apple (App Store guideline 4.8: the app offers Google/Facebook login, so an
 * equivalent privacy-preserving option is required). The response mirrors
 * GoogleAuthController::handleGoogleAuth so the Flutter app can reuse its parsing.
 */
class AppleAuthController extends Controller
{
    public function __construct(private AppleIdTokenVerifier $verifier)
    {
    }

    public function handleAppleAuth(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identity_token' => 'required|string|max:8000',
            'authorization_code' => 'nullable|string|max:2000',
            'nonce' => 'nullable|string|max:255',
            // Apple only reveals the name on the FIRST authorisation, so the app forwards it now.
            'name' => 'nullable|string|max:255',
            'device_token' => 'nullable|string',
            'referral_code' => 'nullable|string|max:8',
        ]);

        $claims = $this->verifier->verify($data['identity_token'], $data['nonce'] ?? null);
        if (! $claims) {
            return response()->json(['error' => 'Invalid Apple identity token'], 401);
        }

        $appleId = (string) $claims['sub'];
        $email = isset($claims['email']) ? strtolower(trim((string) $claims['email'])) : null;
        $emailVerified = AppleIdTokenVerifier::emailVerified($claims);

        $isNewUser = false;
        $user = User::where('apple_id', $appleId)->first();

        // Link to an existing account (Google/email) only when Apple itself vouches for the email.
        if (! $user && $email && $emailVerified) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->apple_id = $appleId;
            }
        }

        if (! $user) {
            if (! $email) {
                return response()->json([
                    'error' => 'Apple did not share an email address. Remove Talabna from Settings > Apple ID > '
                        .'Sign in with Apple and try again, or use another sign-in method.',
                ], 422);
            }
            // An account owns this email but Apple did not verify it, so never hand it over.
            if (User::where('email', $email)->exists()) {
                return response()->json(['error' => 'An account with this email already exists. Sign in with your original method.'], 409);
            }
            $user = $this->createUser($appleId, $email, $emailVerified, $data);
            $isNewUser = true;
        }

        if ($user->is_active === 'banned') {
            return response()->json(['error' => 'This account has been banned.'], 403);
        }

        if (! empty($data['device_token'])) {
            $user->fcm_token = $data['device_token'];
        }
        if ($user->isDirty()) {
            $user->save();
        }

        // Keep Apple's refresh token so account deletion can revoke it (no-op until the .p8 key is set).
        app(\App\Services\Auth\AppleTokenRevoker::class)->remember($user, $data['authorization_code'] ?? null);

        \App\Services\Auth\AuthTracker::record($user->id, 'apple', $request, $isNewUser);
        $token = $user->createToken('apple-auth-token')->accessToken;
        $photo = $user->photos()->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'profile_photo_url' => $photo?->src,
                'profile_photo_is_external' => $photo ? (bool) $photo->is_external : false,
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
    }

    private function createUser(string $appleId, string $email, bool $emailVerified, array $data): User
    {
        $name = trim((string) ($data['name'] ?? '')) ?: Str::before($email, '@');
        $country = countries::first();
        $city = $country ? cities::where('country_id', $country->id)->first() : null;

        $user = User::create([
            'name' => $name,
            'user_name' => $this->generateUsername($name),
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // OAuth users never use a password
            'apple_id' => $appleId,
            'email_verified_at' => $emailVerified ? now() : null,
            'is_active' => 'active',
            'gender' => 'ذكر',
            'auth_type' => 'apple',
            'data_saver_enabled' => false,
            'country_id' => $country?->id,
            'city_id' => $city?->id,
            'fcm_token' => ! empty($data['device_token']) ? $data['device_token'] : null,
        ]);

        $avatars = ['avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png', 'avatar5.png'];
        $user->photos()->save(new Photos(['src' => 'storage/photos/'.$avatars[array_rand($avatars)]]));

        // Same default role/permissions as email and Google sign-up.
        $role = Role::where('name', 'user')->first();
        if ($role) {
            $user->roles()->attach($role->id, ['user_type' => get_class($user)]);
            $permSync = [];
            foreach ($role->permissions->pluck('id') as $pid) {
                $permSync[$pid] = ['user_type' => get_class($user)];
            }
            $user->permissions()->sync($permSync);
        } else {
            Log::error('Default "user" role not found — Apple user has no role', ['user_id' => $user->id]);
        }

        $user->notifications()->save(new Notification([
            'message' => json_encode([
                'en' => "🎉 Welcome to our app! We're thrilled to have you here.",
                'ar' => '🎉 مرحبًا بك في تطبيقنا! نحن سعداء بانضمامك إلينا.',
            ]),
            'type' => 'login',
        ]));

        if (! empty($data['referral_code'])) {
            User::processReferral($user, $data['referral_code']);
        }

        return $user;
    }

    private function generateUsername(string $name): string
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower(str_replace(' ', '', $name))) ?: 'user';
        do {
            $username = $base.rand(100, 9999);
        } while (User::where('user_name', $username)->exists());

        return $username;
    }
}
