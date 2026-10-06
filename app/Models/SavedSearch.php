<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $fillable = ['user_id', 'query', 'filters', 'muted', 'last_checked_at', 'last_notified_at'];

    protected $casts = [
        'filters' => 'array',
        'muted' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];
}
