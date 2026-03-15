<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class point_purchase_requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points_requested',
        'price_per_point',
        'total_price',
        'status',
        'idempotency_key',
        'auto_approved',
        'approval_type',
        'point_package_id',
        'discount_applied',
        'client_secret',
        'google_order_id',
        'google_product_id',
        'google_purchase_token',
    ];

    protected $casts = [
        'auto_approved' => 'boolean',
        'discount_applied' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(PointPackage::class, 'point_package_id');
    }
}
