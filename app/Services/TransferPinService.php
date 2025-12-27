<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TransferPinService
{
    public const PIN_LOCKOUT_DURATION_MINUTES = 30;
    public const MAX_PIN_ATTEMPTS = 3;

    /**
     * Set a new transfer PIN for the user
     */
    public function setPin(User $user, string $pin): void
    {
        if (!$this->isValidPinFormat($pin)) {
            throw new \InvalidArgumentException('PIN must be exactly 4 digits');
        }

        $user->transfer_pin_hash = Hash::make($pin);
        $user->transfer_pin_set_at = now();
        $user->failed_pin_attempts = 0;
        $user->pin_locked_until = null;
        $user->save();
    }

    /**
     * Verify the user's transfer PIN
     */
    public function verifyPin(User $user, string $pin): bool
    {
        if ($this->isLocked($user)) {
            return false;
        }

        if (!$this->hasPin($user)) {
            return false;
        }

        if (Hash::check($pin, $user->transfer_pin_hash)) {
            // Reset failed attempts on successful verification
            $this->resetFailedAttempts($user);
            return true;
        }

        // Record failed attempt
        $this->recordFailedAttempt($user);
        return false;
    }

    /**
     * Check if the user has a transfer PIN set
     */
    public function hasPin(User $user): bool
    {
        return !empty($user->transfer_pin_hash);
    }

    /**
     * Check if the user's PIN is locked due to too many failed attempts
     */
    public function isLocked(User $user): bool
    {
        if ($user->pin_locked_until === null) {
            return false;
        }

        if (now()->greaterThan($user->pin_locked_until)) {
            // Lockout period has expired, reset it
            $this->resetLockout($user);
            return false;
        }

        return true;
    }

    /**
     * Get the lockout expiration time
     */
    public function getLockoutTime(User $user): ?\Carbon\Carbon
    {
        if (!$this->isLocked($user)) {
            return null;
        }

        return $user->pin_locked_until;
    }

    /**
     * Record a failed PIN attempt
     */
    public function recordFailedAttempt(User $user): void
    {
        $user->failed_pin_attempts++;

        if ($user->failed_pin_attempts >= self::MAX_PIN_ATTEMPTS) {
            $user->pin_locked_until = now()->addMinutes(self::PIN_LOCKOUT_DURATION_MINUTES);
        }

        $user->save();
    }

    /**
     * Reset the lockout status
     */
    public function resetLockout(User $user): void
    {
        $user->failed_pin_attempts = 0;
        $user->pin_locked_until = null;
        $user->save();
    }

    /**
     * Reset failed attempts counter
     */
    public function resetFailedAttempts(User $user): void
    {
        if ($user->failed_pin_attempts > 0) {
            $user->failed_pin_attempts = 0;
            $user->save();
        }
    }

    /**
     * Get the number of remaining PIN attempts
     */
    public function getRemainingAttempts(User $user): int
    {
        return max(0, self::MAX_PIN_ATTEMPTS - $user->failed_pin_attempts);
    }

    /**
     * Change the user's PIN (requires current PIN verification)
     */
    public function changePin(User $user, string $currentPin, string $newPin): bool
    {
        if (!$this->verifyPin($user, $currentPin)) {
            return false;
        }

        $this->setPin($user, $newPin);
        return true;
    }

    /**
     * Validate PIN format (4 digits)
     */
    private function isValidPinFormat(string $pin): bool
    {
        return preg_match('/^[0-9]{4}$/', $pin) === 1;
    }

    /**
     * Get PIN status for a user
     */
    public function getPinStatus(User $user): array
    {
        $isLocked = $this->isLocked($user);

        return [
            'has_pin' => $this->hasPin($user),
            'is_locked' => $isLocked,
            'locked_until' => $isLocked ? $user->pin_locked_until?->toIso8601String() : null,
            'failed_attempts' => $user->failed_pin_attempts,
            'remaining_attempts' => $this->getRemainingAttempts($user),
        ];
    }
}
