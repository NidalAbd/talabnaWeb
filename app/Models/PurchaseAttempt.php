<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One tap on "Buy" in the app and what happened to it (see the migration). */
class PurchaseAttempt extends Model
{
    public const OPEN = ['attempted', 'pending'];
    public const STATUSES = ['attempted', 'pending', 'completed', 'cancelled', 'failed', 'abandoned'];

    protected $fillable = [
        'user_id', 'product_id', 'platform', 'status', 'error_code', 'error_message',
        'price', 'currency', 'app_version', 'purchase_request_id', 'resolved_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = ['status' => 'attempted'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The store payment was verified on our server: close the user's latest
     * open attempt for that product — even if the app never reported back.
     */
    public static function markCompletedByServer(int $userId, ?string $productId, ?int $purchaseRequestId = null): void
    {
        if (!$productId) return;
        $attempt = self::where('user_id', $userId)->where('product_id', $productId)
            ->whereIn('status', array_merge(self::OPEN, ['abandoned', 'failed']))
            ->where('created_at', '>=', now()->subDays(3))
            ->latest('id')->first();
        if ($attempt) {
            $attempt->update(['status' => 'completed', 'resolved_at' => now(), 'purchase_request_id' => $purchaseRequestId,
                'error_code' => null, 'error_message' => null]);
        } else {
            // Bought from an older app version that doesn't log attempts.
            self::create(['user_id' => $userId, 'product_id' => $productId, 'status' => 'completed',
                'platform' => 'unknown', 'resolved_at' => now(), 'purchase_request_id' => $purchaseRequestId]);
        }
    }
}
