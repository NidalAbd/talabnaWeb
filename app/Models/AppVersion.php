<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'latest_version',
        'latest_build_number',
        'minimum_build_number',
        'is_mandatory',
        'update_url',
        'message',
    ];

    protected $casts = [
        'latest_build_number' => 'integer',
        'minimum_build_number' => 'integer',
        'is_mandatory' => 'boolean',
    ];
}
