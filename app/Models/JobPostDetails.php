<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPostDetails extends Model
{
    use HasFactory;

    protected $table = 'job_post_details';

    protected $fillable = [
        'service_post_id',
        'employment_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'required_skills',
        'education_level',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'salary_min' => 'integer',
        'salary_max' => 'integer',
    ];

    public function servicePost(): BelongsTo
    {
        return $this->belongsTo(ServicePost::class);
    }
}
