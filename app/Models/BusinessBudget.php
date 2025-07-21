<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_title',
        'budget_description',
        'total_budget',
        'currency',
        'budget_period', // monthly, quarterly, yearly
        'start_date',
        'end_date',
        'category', // overall, marketing, development, operations, etc.
        'status', // active, completed, cancelled
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'created_by',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_budget' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function expenses()
    {
        return $this->hasMany(BusinessExpense::class, 'budget_id');
    }

    public function getFormattedTotalBudgetAttribute()
    {
        return '$' . number_format($this->total_budget, 2);
    }

    public function getFormattedSpentAmountAttribute()
    {
        return '$' . number_format($this->spent_amount, 2);
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return '$' . number_format($this->remaining_amount, 2);
    }

    public function getUtilizationPercentageAttribute()
    {
        if ($this->total_budget > 0) {
            return round(($this->spent_amount / $this->total_budget) * 100, 2);
        }
        return 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('budget_period', $period);
    }
} 