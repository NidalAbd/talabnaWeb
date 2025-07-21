<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessRevenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'revenue_title',
        'revenue_description',
        'amount',
        'currency',
        'revenue_type', // point_sales, advertising, premium_features, other
        'revenue_date',
        'payment_method',
        'customer_name',
        'invoice_number',
        'status', // received, pending, failed
        'point_package_id', // if from point sales
        'user_id', // if from specific user
        'notes'
    ];

    protected $casts = [
        'revenue_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function pointPackage()
    {
        return $this->belongsTo(PointPackage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('revenue_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('revenue_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
} 