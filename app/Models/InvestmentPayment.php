<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'payment_amount',
        'payment_date',
        'payment_type', // return, dividend, interest
        'payment_method', // bank_transfer, check, cash
        'reference_number',
        'notes',
        'status' // completed, pending, failed
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2'
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->payment_amount, 2);
    }
} 