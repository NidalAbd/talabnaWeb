<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class point_purchase_requests extends Model
{
    protected static function booted(): void
    {
        // A store purchase our server verified closes the matching attempt.
        static::created(function ($r) {
            if ($r->status === 'approved' && ($r->google_product_id || $r->apple_product_id)) {
                try {
                    \App\Models\PurchaseAttempt::markCompletedByServer((int) $r->user_id, $r->google_product_id ?: $r->apple_product_id, (int) $r->id);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('purchase attempt link failed: ' . $e->getMessage());
                }
            }
        });
    }

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
        'apple_transaction_id',
        'apple_product_id',
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
