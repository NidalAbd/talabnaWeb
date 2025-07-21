<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_name',
        'investor_email',
        'investment_amount',
        'currency',
        'investment_type', // equity, loan, grant
        'investment_date',
        'expected_roi',
        'investment_period',
        'investor_share',
        'owner_share',
        'agreement_terms',
        'status', // active, completed, pending, profitable
        'purpose', // license, advertising, salary, development, etc.
        'notes',
        'contract_file',
        'payment_schedule',
        'next_payment_date',
        'total_paid',
        'remaining_amount',
        'profit_generated',
        'profit_distributed',
        'profit_remaining'
    ];

    protected $casts = [
        'investment_date' => 'date',
        'next_payment_date' => 'date',
        'expected_roi' => 'decimal:2',
        'investment_period' => 'integer',
        'investor_share' => 'decimal:2',
        'owner_share' => 'decimal:2',
        'investment_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'profit_generated' => 'decimal:2',
        'profit_distributed' => 'decimal:2',
        'profit_remaining' => 'decimal:2',
        'payment_schedule' => 'array'
    ];

    public function payments()
    {
        return $this->hasMany(InvestmentPayment::class);
    }

    public function expenses()
    {
        return $this->hasMany(BusinessExpense::class);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->investment_amount, 2);
    }

    public function getFormattedRemainingAttribute()
    {
        return '$' . number_format($this->remaining_amount, 2);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->investment_amount > 0) {
            return round(($this->total_paid / $this->investment_amount) * 100, 2);
        }
        return 0;
    }

    public function getProfitSharingAttribute()
    {
        return [
            'investor_share' => $this->investor_share,
            'owner_share' => $this->owner_share,
            'investor_amount' => $this->profit_generated * ($this->investor_share / 100),
            'owner_amount' => $this->profit_generated * ($this->owner_share / 100)
        ];
    }

    public function getExpectedProfitAttribute()
    {
        return $this->investment_amount * ($this->expected_roi / 100);
    }

    public function getMonthsRemainingAttribute()
    {
        $monthsPassed = $this->investment_date->diffInMonths(now());
        return max(0, $this->investment_period - $monthsPassed);
    }

    public function isProfitable()
    {
        return $this->profit_generated > 0;
    }

    public function canDistributeProfit()
    {
        return $this->status === 'active' && $this->profit_generated > 0;
    }

    public function distributeProfit($amount)
    {
        $this->profit_distributed += $amount;
        $this->profit_remaining = $this->profit_generated - $this->profit_distributed;
        $this->save();
    }
} 