<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** "blocker" no longer sees content from, and cannot exchange messages with, "blocked". */
class UserBlock extends Model
{
    protected $fillable = ['blocker_id', 'blocked_id'];

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}
