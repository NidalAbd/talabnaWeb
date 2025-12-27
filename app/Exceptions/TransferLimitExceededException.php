<?php

namespace App\Exceptions;

use Exception;

class TransferLimitExceededException extends Exception
{
    protected string $limitType;
    protected int $currentValue;
    protected int $maxValue;

    /**
     * @param string $limitType 'per_transfer', 'daily_count', or 'daily_amount'
     * @param int $currentValue The current/attempted value
     * @param int $maxValue The maximum allowed value
     */
    public function __construct(string $limitType, int $currentValue, int $maxValue)
    {
        $this->limitType = $limitType;
        $this->currentValue = $currentValue;
        $this->maxValue = $maxValue;

        $message = match ($limitType) {
            'per_transfer' => "Transfer amount ({$currentValue}) exceeds maximum per transfer ({$maxValue})",
            'daily_count' => "Daily transfer count ({$currentValue}) has reached the limit ({$maxValue})",
            'daily_amount' => "Daily transfer amount ({$currentValue}) would exceed limit ({$maxValue})",
            default => "Transfer limit exceeded",
        };

        parent::__construct($message);
    }

    public function getLimitType(): string
    {
        return $this->limitType;
    }

    public function getCurrentValue(): int
    {
        return $this->currentValue;
    }

    public function getMaxValue(): int
    {
        return $this->maxValue;
    }

    public function getDetails(): array
    {
        return [
            'limit_type' => $this->limitType,
            'current_value' => $this->currentValue,
            'max_value' => $this->maxValue,
            'exceeded_by' => max(0, $this->currentValue - $this->maxValue),
        ];
    }
}
