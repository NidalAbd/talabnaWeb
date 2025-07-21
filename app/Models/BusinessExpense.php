<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_title',
        'expense_description',
        'amount',
        'currency',
        'expense_category', // license, advertising, salary, development, office, marketing, legal, etc.
        'expense_date',
        'payment_method',
        'vendor_name',
        'invoice_number',
        'receipt_file',
        'status', // pending, approved, paid, rejected
        'approved_by',
        'approved_at',
        'investment_id', // if funded by specific investment
        'budget_id', // if allocated to specific budget
        'notes',
        'recurring', // monthly, quarterly, yearly, one-time
        'next_due_date'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'next_due_date' => 'date',
        'amount' => 'decimal:2',
        'recurring' => 'boolean'
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function budget()
    {
        return $this->belongsTo(BusinessBudget::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('expense_category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }
} 