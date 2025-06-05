<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class palservice_points extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'points',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transactions()
    {
        return $this->hasMany(point_transactions::class);
    }
}
