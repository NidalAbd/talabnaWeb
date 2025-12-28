<?php

namespace App\Exceptions;

use Exception;

class CountryMismatchException extends Exception
{
    public function __construct(string $message = 'Point transfers are only allowed between users in the same country')
    {
        parent::__construct($message);
    }
}
