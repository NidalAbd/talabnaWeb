<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class point_purchase_requests extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'points_requested', 'price_per_point', 'total_price', 'status'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
