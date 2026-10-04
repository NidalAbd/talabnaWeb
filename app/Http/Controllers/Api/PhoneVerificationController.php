<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Phone and WhatsApp verification (rebuilt 2026-10-04).
 *
 * The app sends the SMS code through Firebase phone sign-in; once the user enters it, Firebase
 * gives the app an ID token whose `phone_number` claim is the verified number. We verify that
 * token here (the old flow trusted a firebase_uid sent by the client, so anyone could claim any
 * number).
 *
 * Rules:
 * - One number belongs to one account (phone or WhatsApp column of anyone else).
 * - A number replaced by its owner is held for 30 days; one from a deleted account for 90 days.
 * - A verified number can be replaced once every 30 days (per type).
 * - At most 3 SMS codes a day per account and per number (request-code is called before the
 *   app asks Firebase to send one).
 * - type "both" (phone and WhatsApp are the same number) needs one code.
 * - Verifying the phone sets the account's country from its dialling code, within the
 *   30-day country-change rule.
 * - A user can say they don't use WhatsApp; the profile is complete without it.
 */
class PhoneVerificationController extends Controller
{
    /** Admin-controlled (Admin -> Pricing -> Phone verification). */
    private function cooldownDays(): int
    {
        return max(1, (int) \App\Models\AppSetting::get('verification.change_cooldown_days', 30));
    }

    private function codesPerDay(): int
    {
        return max(1, (int) \App\Models\AppSetting::get('verification.codes_per_day', 5));
    }

    /** POST /api/phone/request-code  { phone, type: phone|whatsapp|both } */
    public function requestCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'type' => 'required|in:phone,whatsapp,both',
        ]);
        $user = Auth::user();
        $phone = $this->normalize($data['phone']);
        if (! $phone) {
            return $this->fail('invalid_phone', 'Enter the number with its country code, e.g. +201001234567.', 422);
        }

        if ($error = $this->checkClaimable($user, $phone, $data['type'])) {
            return $error;
        }

        foreach (["phone-code:user:{$user->id}", "phone-code:number:{$phone}"] as $key) {
            if (RateLimiter::tooManyAttempts($key, $this->codesPerDay())) {
                return $this->fail('too_many_codes', 'Too many codes today. Please try again tomorrow.', 429, [
                    'retry_after_seconds' => RateLimiter::availableIn($key),
                ]);
            }
        }
        foreach (["phone-code:user:{$user->id}", "phone-code:number:{$phone}"] as $key) {
            RateLimiter::hit($key, 86400);
        }
        // Lets the app hand this attempt back if Firebase then fails to send the SMS.
        Cache::put("phone-code:pending:{$user->id}", $phone, now()->addMinutes(10));

        return response()->json(['success' => true, 'phone' => $phone]);
    }

    /**
     * POST /api/phone/code-failed — Firebase could not send the SMS (reCAPTCHA, network,
     * device blocked…), so this attempt shouldn't use up the day's codes. Only for the last
     * requested number, and at most twice a day, so it can't be used to skip the limit.
     */
    public function codeFailed(): JsonResponse
    {
        $user = Auth::user();
        $phone = Cache::pull("phone-code:pending:{$user->id}");
        $refundsKey = "phone-code:refunds:{$user->id}";
        if (! $phone || RateLimiter::tooManyAttempts($refundsKey, 2)) {
            return response()->json(['success' => true, 'refunded' => false]);
        }
        RateLimiter::hit($refundsKey, 86400);
        foreach (["phone-code:user:{$user->id}", "phone-code:number:{$phone}"] as $key) {
            $attempts = RateLimiter::attempts($key);
            if ($attempts > 0) {
                RateLimiter::clear($key);
                for ($i = 1; $i < $attempts; $i++) {
                    RateLimiter::hit($key, 86400);
                }
            }
        }

        return response()->json(['success' => true, 'refunded' => true]);
    }

    /** POST /api/phone/verify  { phone, type: phone|whatsapp|both, firebase_id_token } */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'type' => 'required|in:phone,whatsapp,both',
            'firebase_id_token' => 'required|string',
        ]);
        $user = Auth::user();
        $phone = $this->normalize($data['phone']);
        if (! $phone) {
            return $this->fail('invalid_phone', 'Enter the number with its country code.', 422);
        }

        try {
            $token = app(\Kreait\Firebase\Contract\Auth::class)->verifyIdToken($data['firebase_id_token']);
            $verifiedPhone = $this->normalize((string) $token->claims()->get('phone_number'));
        } catch (\Throwable $e) {
            Log::warning('phone.verify: invalid Firebase token', ['user' => $user->id, 'error' => $e->getMessage()]);

            return $this->fail('invalid_code', 'The verification could not be confirmed. Please try again.', 422);
        }
        if ($verifiedPhone !== $phone) {
            return $this->fail('phone_mismatch', 'The verified number does not match.', 422);
        }

        if ($error = $this->checkClaimable($user, $phone, $data['type'])) {
            return $error;
        }

        DB::transaction(function () use ($user, $phone, $data) {
            $types = $data['type'] === 'both' ? ['phone', 'whatsapp'] : [$data['type']];
            foreach ($types as $type) {
                [$column, $verifiedAt, $changedAt] = $this->columns($type);
                $old = $user->{$column};
                if ($old && $old !== $phone && $user->{$verifiedAt}) {
                    // The replaced number stays reserved for its owner for 30 days.
                    $this->hold($old, $user->id, 'changed', 30);
                    $user->{$changedAt} = now();
                }
                $user->{$column} = $phone;
                $user->{$verifiedAt} = now();
                if ($type === 'whatsapp') {
                    $user->no_whatsapp = false;
                }
            }
            // The same number in the other field is verified by the same code
            // (WhatsApp verified and equal to the phone = phone verified, and the reverse).
            foreach (['phone', 'whatsapp'] as $other) {
                [$column, $verifiedAt] = $this->columns($other);
                if (! in_array($other, $types, true) && $user->{$column} === $phone) {
                    $user->{$verifiedAt} = now();
                    $types[] = $other;
                }
            }
            if (in_array('phone', $types, true)) {
                $this->applyCountryFromPhone($user, $phone);
            }
            $user->save();
        });

        return response()->json(['success' => true] + $this->statusPayload($user->fresh()));
    }

    /** Kept for older app builds: same as verify with type both / phone. */
    public function verifyBoth(Request $request): JsonResponse
    {
        $request->merge(['type' => $request->boolean('same_whatsapp') ? 'both' : 'phone']);

        return $this->verify($request);
    }

    /** POST /api/phone/no-whatsapp  { no_whatsapp: bool } */
    public function noWhatsapp(Request $request): JsonResponse
    {
        $data = $request->validate(['no_whatsapp' => 'required|boolean']);
        $user = Auth::user();
        $user->no_whatsapp = $data['no_whatsapp'];
        if ($data['no_whatsapp']) {
            if ($user->WatsNumber && $user->whatsapp_verified_at && $user->WatsNumber !== $user->phones) {
                $this->hold($user->WatsNumber, $user->id, 'changed', 30);
            }
            $user->WatsNumber = null;
            $user->whatsapp_verified_at = null;
        }
        $user->save();

        return response()->json(['success' => true] + $this->statusPayload($user));
    }

    public function status(): JsonResponse
    {
        return response()->json($this->statusPayload(Auth::user()));
    }

    /** Request country change (requires verified phone + 30 day cooldown). */
    public function changeCountry(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
        ]);

        $user = Auth::user();

        if (! $user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your phone number before changing country.',
                'requires_phone_verification' => true,
            ], 403);
        }

        if (! $this->canChangeCountry($user)) {
            $days = $this->daysUntil($user->country_changed_at);

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

    /** Reserve the account's numbers when it is deleted (90 days). */
    public static function holdNumbersOfDeletedUser(User $user): void
    {
        foreach (array_unique(array_filter([$user->phones, $user->WatsNumber])) as $number) {
            (new self())->hold($number, $user->id, 'deleted', 90);
        }
    }

    // ------------------------------------------------------------------------------------------

    private function checkClaimable(User $user, string $phone, string $type): ?JsonResponse
    {
        $takenByOther = $this->numberUsedByOther($user, $phone);
        if ($takenByOther) {
            return $this->fail('number_taken', 'This number is already used by another account.', 409);
        }

        $held = DB::table('phone_number_holds')
            ->where('phone', $phone)
            ->where('hold_until', '>', now())
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', '!=', $user->id))
            ->exists();
        if ($held) {
            return $this->fail('number_on_hold', 'This number was recently used by another account and is not available yet.', 409);
        }

        $types = $type === 'both' ? ['phone', 'whatsapp'] : [$type];
        foreach ($types as $t) {
            [$column, $verifiedAt, $changedAt] = $this->columns($t);
            $replacing = $user->{$verifiedAt} && $user->{$column} && $user->{$column} !== $phone;
            if ($replacing && $user->{$changedAt} && $user->{$changedAt}->copy()->addDays($this->cooldownDays())->isFuture()) {
                $days = $this->daysUntil($user->{$changedAt});

                return $this->fail('change_cooldown', "You can change this number again in {$days} days.", 429, [
                    'days_remaining' => $days,
                    'type' => $t,
                ]);
            }
        }

        return null;
    }

    private function applyCountryFromPhone(User $user, string $phone): void
    {
        $country = $this->countryForPhone($phone);
        if (! $country || (int) $user->country_id === (int) $country->id) {
            return;
        }
        if ($user->country_id && ! $this->canChangeCountry($user)) {
            return; // within the 30-day country lock: keep the current country
        }
        $user->country_id = $country->id;
        $user->city_id = null;
        $user->country_changed_at = now();
    }

    /** Longest dialling-code match: countries.country_code is stored like "0020", "00970". */
    private function countryForPhone(string $phone): ?object
    {
        $digits = substr($phone, 1);
        $best = null;
        $bestLength = 0;
        foreach (DB::table('countries')->whereNotNull('country_code')->get(['id', 'country_code']) as $country) {
            $code = ltrim((string) $country->country_code, '+0');
            if ($code !== '' && str_starts_with($digits, $code) && strlen($code) > $bestLength) {
                $best = $country;
                $bestLength = strlen($code);
            }
        }

        return $best;
    }

    private function hold(string $phone, ?int $userId, string $reason, int $days): void
    {
        DB::table('phone_number_holds')->insert([
            'phone' => $phone,
            'user_id' => $userId,
            'reason' => $reason,
            'hold_until' => now()->addDays($days),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** E.164: "+" and 8-15 digits. Accepts "00" prefixes and spaces/dashes. */
    /**
     * Profiles store numbers in many formats (00970…, +970…, 0598…), so an exact match misses
     * the same number written differently. Match on the last 9 digits, then compare normalized.
     */
    private function numberUsedByOther(User $user, string $phone): bool
    {
        $tail = substr(preg_replace('/\D+/', '', $phone), -9);
        $candidates = User::where('id', '!=', $user->id)
            ->where(fn ($q) => $q->where('phones', 'like', "%{$tail}")->orWhere('WatsNumber', 'like', "%{$tail}"))
            ->get(['id', 'phones', 'WatsNumber', 'country_id']);
        foreach ($candidates as $other) {
            foreach ([$other->phones, $other->WatsNumber] as $stored) {
                if (! $stored) {
                    continue;
                }
                if ($this->normalize($stored) === $phone) {
                    return true;
                }
                // Local format (0598…) with no country code: same number if the rest matches.
                if (str_starts_with(trim($stored), '0') && ! str_starts_with(trim($stored), '00')
                    && str_ends_with($phone, ltrim(preg_replace('/\D+/', '', $stored), '0'))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalize(string $raw): ?string
    {
        $raw = trim($raw);
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($raw, '00')) {
            $digits = substr($digits, 2);
        } elseif (! str_starts_with($raw, '+') && str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return preg_match('/^[1-9]\d{7,14}$/', $digits) ? '+'.$digits : null;
    }

    private function columns(string $type): array
    {
        return $type === 'whatsapp'
            ? ['WatsNumber', 'whatsapp_verified_at', 'whatsapp_changed_at']
            : ['phones', 'phone_verified_at', 'phone_changed_at'];
    }

    private function statusPayload(User $user): array
    {
        return [
            'method' => \App\Models\AppSetting::get('verification.method', 'sms'),
            'phones' => $user->phones,
            'watsNumber' => $user->WatsNumber,
            'phone_verified' => $user->phone_verified_at !== null,
            'phone_verified_at' => $user->phone_verified_at?->toISOString(),
            'whatsapp_verified' => $user->whatsapp_verified_at !== null,
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toISOString(),
            'no_whatsapp' => (bool) $user->no_whatsapp,
            'days_until_phone_change' => $this->daysUntil($user->phone_changed_at),
            'days_until_whatsapp_change' => $this->daysUntil($user->whatsapp_changed_at),
            'country_id' => $user->country_id,
            'country_changed_at' => $user->country_changed_at?->toISOString(),
            'can_change_country' => $this->canChangeCountry($user),
            'days_until_country_change' => $this->daysUntil($user->country_changed_at),
        ];
    }

    private function canChangeCountry(User $user): bool
    {
        return ! $user->country_changed_at || $user->country_changed_at->copy()->addDays($this->cooldownDays())->isPast();
    }

    private function daysUntil($changedAt): int
    {
        if (! $changedAt) {
            return 0;
        }
        $next = $changedAt->copy()->addDays($this->cooldownDays());

        return $next->isPast() ? 0 : (int) ceil(now()->diffInHours($next) / 24);
    }

    private function fail(string $code, string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'code' => $code, 'message' => $message] + $extra, $status);
    }
}
