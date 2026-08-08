<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Self-service account deletion — required by Google Play's Account
 * Deletion policy for any app that lets users create an account
 * (Play console: App content > Data safety > Account deletion).
 *
 * Deletes the caller's own account only (never takes a user id from the
 * request) and everything tied to it. Most child tables already cascade
 * at the DB level (see the `onDelete('cascade')` foreign keys on
 * service_posts, comments, favorites, followers, notifications, etc.),
 * so this controller only has to handle the two things that don't:
 * photo files on disk (Photos is polymorphic, no FK) and Passport
 * access tokens (no FK on oauth_access_tokens.user_id).
 */
class AccountController extends Controller
{
    private const DEFAULT_AVATARS = [
        'photos/avatar1.png',
        'photos/avatar2.png',
        'photos/avatar3.png',
        'photos/avatar4.png',
        'photos/avatar5.png',
    ];

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Password-based accounts must confirm their password before a
        // destructive, irreversible action. OAuth-only accounts (Google /
        // Facebook, no local password) skip this — the bearer token already
        // proves identity and there's no password for them to enter.
        if (empty($user->auth_type)) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            if (!Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Incorrect password.'], 401);
            }
        }

        $userId = $user->id;

        try {
            DB::transaction(function () use ($user) {
                $this->deletePhotosFor($user);
                foreach ($user->servicePosts()->get() as $post) {
                    $this->deletePhotosFor($post);
                }

                // No FK on oauth_access_tokens.user_id — revoke explicitly
                // instead of relying on cascade.
                foreach ($user->tokens as $token) {
                    $token->revoke();
                }

                // Hard-delete. Everything else (service posts, comments,
                // favorites, followers/following, reports, point
                // transactions, notifications, banned devices, etc.) is
                // cleaned up by the DB's own onDelete cascade/set-null
                // foreign keys.
                $user->delete();
            });
        } catch (\Throwable $e) {
            Log::error('account.delete failed', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Could not delete your account. Please try again or contact support.',
            ], 500);
        }

        Log::info('account.delete succeeded', ['user_id' => $userId]);

        return response()->json(['message' => 'Account deleted.']);
    }

    /** Deletes every Photos row for $owner, unlinking the file on the
     *  public disk first (skipping the shared default-avatar images). */
    private function deletePhotosFor(User|ServicePost $owner): void
    {
        foreach ($owner->photos as $photo) {
            if (
                $photo->src
                && !in_array($photo->src, self::DEFAULT_AVATARS, true)
                && Storage::disk('public')->exists($photo->src)
            ) {
                Storage::disk('public')->delete($photo->src);
            }
            $photo->delete();
        }
    }
}
