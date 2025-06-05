<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'reporter_id',
        'reportable_id',
        'reportable_type'
    ];

    public const REASONS = [
        'inappropriate_content',
        'fake_information',
        'scam',
        'other',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function reportable()
    {
        return $this->morphTo();
    }
}
