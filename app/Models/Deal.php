<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A sale both sides confirmed in chat — counted on profiles as trust ("12 confirmed deals"). */
class Deal extends Model
{
    protected $fillable = ['conversation_id', 'service_post_id', 'seller_id', 'buyer_id', 'proposed_by', 'status', 'confirmed_at'];

    protected $casts = ['confirmed_at' => 'datetime'];

    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'seller_id' => $this->seller_id,
            'buyer_id' => $this->buyer_id,
            'proposed_by' => $this->proposed_by,
            'service_post_id' => $this->service_post_id,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
        ];
    }

    /** ['sold' => n, 'bought' => n, 'total' => n] confirmed deals of a user. */
    public static function statsFor(int $userId): array
    {
        $sold = self::where('seller_id', $userId)->where('status', 'confirmed')->count();
        $bought = self::where('buyer_id', $userId)->where('status', 'confirmed')->count();

        return ['sold' => $sold, 'bought' => $bought, 'total' => $sold + $bought];
    }
}
