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
        'expected_return' => 'decimal:2',
        'return_percentage' => 'decimal:2',
        'investment_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
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
} 