<?php

namespace App\Exceptions;

use Exception;

class InvalidPinException extends Exception
{
    protected int $attemptsRemaining;

    public function __construct(string $message, int $attemptsRemaining)
    {
        $this->attemptsRemaining = $attemptsRemaining;
        parent::__construct($message);
    }

    public function getAttemptsRemaining(): int
    {
        return $this->attemptsRemaining;
    }

    public function getDetails(): array
    {
        return [
            'attempts_remaining' => $this->attemptsRemaining,
            'will_lock' => $this->attemptsRemaining === 0,
        ];
    }
}
