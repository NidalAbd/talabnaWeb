<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per AI action: its price in points and whether it is switched on. */
class AiFeature extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'points_cost', 'enabled', 'description'];
    protected $casts = ['enabled' => 'boolean', 'points_cost' => 'integer'];
}
