<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = ['conversation_id', 'service_post_id', 'seller_id', 'buyer_id', 'from_user_id', 'amount', 'currency', 'status', 'parent_id'];

    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'from_user_id' => $this->from_user_id,
            'seller_id' => $this->seller_id,
            'buyer_id' => $this->buyer_id,
            'service_post_id' => $this->service_post_id,
            'parent_id' => $this->parent_id,
        ];
    }
}
