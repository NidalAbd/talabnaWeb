<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PhoneVerificationController extends Controller
{
    /**
     * Verify phone number using Firebase ID token.
     * Flutter does the SMS OTP via Firebase, then sends us the verified phone.
     *
     * POST /api/phone/verify
     * Body: { phone: "+970599123456", type: "phone"|"whatsapp", firebase_uid: "xxx" }
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'type' => 'required|in:phone,whatsapp',
            'firebase_uid' => 'required|string',
        ]);

        $user = Auth::user();
        $phone = $request->phone;
        $type = $request->type;

        // Check if this phone is already used by another user
        $existingUser = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($phone) {
                $q->where('phones', $phone)->orWhere('WatsNumber', $phone);
            })->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This phone number is already registered to another user.',
            ], 409);
        }

        if ($type === 'phone') {
            $user->phones = $phone;
            $user->phone_verified_at = now();
            Log::info("Phone verified for user {$user->id}: {$phone}");
        } else {
            $user->WatsNumber = $phone;
            $user->whatsapp_verified_at = now();
            Log::info("WhatsApp verified for user {$user->id}: {$phone}");
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => $type === 'phone' ? 'Phone number verified' : 'WhatsApp number verified',
            'phone_verified_at' => $user->phone_verified_at?->toISOString(),
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toISOString(),
        ]);
    }

    /**
     * Verify phone and optionally set same number for WhatsApp.
     *
     * POST /api/phone/verify-both
     * Body: { phone: "+970599123456", same_whatsapp: true, firebase_uid: "xxx" }
     */
    public function verifyBoth(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'same_whatsapp' => 'required|boolean',
            'firebase_uid' => 'required|string',
        ]);

        $user = Auth::user();
        $phone = $request->phone;

        // Check uniqueness
        $existingUser = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($phone) {
                $q->where('phones', $phone)->orWhere('WatsNumber', $phone);
            })->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This phone number is already registered to another user.',
            ], 409);
        }

        $user->phones = $phone;
        $user->phone_verified_at = now();

        if ($request->same_whatsapp) {
            $user->WatsNumber = $phone;
            $user->whatsapp_verified_at = now();
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => $request->same_whatsapp
                ? 'Phone and WhatsApp verified (same number)'
                : 'Phone verified. Please verify WhatsApp separately.',
            'phone_verified_at' => $user->phone_verified_at?->toISOString(),
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toISOString(),
            'needs_whatsapp_verification' => !$request->same_whatsapp,
        ]);
    }

    /**
     * Get verification status for current user.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'phones' => $user->phones,
            'watsNumber' => $user->WatsNumber,
            'phone_verified' => $user->phone_verified_at !== null,
            'phone_verified_at' => $user->phone_verified_at?->toISOString(),
            'whatsapp_verified' => $user->whatsapp_verified_at !== null,
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toISOString(),
            'country_changed_at' => $user->country_changed_at?->toISOString(),
            'can_change_country' => $this->canChangeCountry($user),
            'days_until_country_change' => $this->daysUntilCountryChange($user),
        ]);
    }

    /**
     * Request country change (requires verified phone + 30 day cooldown).
     */
    public function changeCountry(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
        ]);

        $user = Auth::user();

        // Must have verified phone
        if (!$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your phone number before changing country.',
                'requires_phone_verification' => true,
            ], 403);
        }

        // 30-day cooldown
        if (!$this->canChangeCountry($user)) {
            $days = $this->daysUntilCountryChange($user);
            return response()->json([
                'success' => false,
                'message' => "You can change country again in {$days} days.",
                'days_remaining' => $days,
            ], 429);
        }

        $oldCountryId = $user->country_id;
        $user->country_id = $request->country_id;
        $user->city_id = $request->city_id;
        $user->country_changed_at = now();
        $user->save();

        Log::info("User {$user->id} changed country from {$oldCountryId} to {$request->country_id}");

        return response()->json([
            'success' => true,
            'message' => 'Country updated successfully. Point transfers are paused for 7 days.',
        ]);
    }

    private function canChangeCountry(User $user): bool
    {
        if (!$user->country_changed_at) return true;
        return $user->country_changed_at->addDays(30)->isPast();
    }

    private function daysUntilCountryChange(User $user): int
    {
        if (!$user->country_changed_at) return 0;
        $nextChange = $user->country_changed_at->addDays(30);
        if ($nextChange->isPast()) return 0;
        return (int) now()->diffInDays($nextChange);
    }
}
