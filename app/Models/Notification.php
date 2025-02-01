<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'read',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(point_transactions::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reportable');
    }

    public function servicePosts()
    {
        return $this->hasMany(ServicePost::class, 'sub_categories_id');
    }

    public function sub_categories()
    {
        return $this->hasMany(Sub_categories::class);
    }

}
