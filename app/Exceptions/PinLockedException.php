<?php

namespace App\Exceptions;

use Carbon\Carbon;
use Exception;

class PinLockedException extends Exception
{
    protected Carbon $lockedUntil;

    public function __construct(Carbon $lockedUntil)
    {
        $this->lockedUntil = $lockedUntil;
        parent::__construct('PIN is locked due to too many failed attempts');
    }

    public function getLockedUntil(): Carbon
    {
        return $this->lockedUntil;
    }

    public function getRemainingMinutes(): int
    {
        return max(0, now()->diffInMinutes($this->lockedUntil, false));
    }

    public function getDetails(): array
    {
        return [
            'locked_until' => $this->lockedUntil->toIso8601String(),
            'remaining_minutes' => $this->getRemainingMinutes(),
        ];
    }
}
