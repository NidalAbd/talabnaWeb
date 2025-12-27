<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected int $currentBalance;
    protected int $requestedAmount;

    public function __construct(int $currentBalance, int $requestedAmount)
    {
        $this->currentBalance = $currentBalance;
        $this->requestedAmount = $requestedAmount;

        parent::__construct(
            "Insufficient balance. Current: {$currentBalance}, Requested: {$requestedAmount}"
        );
    }

    public function getCurrentBalance(): int
    {
        return $this->currentBalance;
    }

    public function getRequestedAmount(): int
    {
        return $this->requestedAmount;
    }

    public function getDetails(): array
    {
        return [
            'current_balance' => $this->currentBalance,
            'requested_amount' => $this->requestedAmount,
            'shortage' => $this->requestedAmount - $this->currentBalance,
        ];
    }
}
