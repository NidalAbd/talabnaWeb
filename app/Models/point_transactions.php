<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class point_transactions extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'type',
        'amount',
        'user_id',
        'pal_service_point_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function palServicePoint()
    {
        return $this->belongsTo(palservice_points::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
