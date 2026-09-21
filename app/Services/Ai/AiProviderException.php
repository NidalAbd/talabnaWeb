<?php

namespace App\Services\Ai;

/** The AI provider could not do what was asked. The user is refunded; [code] is stored for the admin. */
class AiProviderException extends \RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message, public readonly int $httpStatus = 502)
    {
        parent::__construct($message);
    }
}
