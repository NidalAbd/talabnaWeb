<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'summary',
        'skills',
        'experience_years',
        'experience_level',
        'education_level',
        'desired_employment_type',
        'desired_salary_min',
        'desired_salary_max',
        'desired_sub_categories_id',
    ];

    protected $casts = [
        'skills' => 'array',
        'experience_years' => 'integer',
        'desired_salary_min' => 'integer',
        'desired_salary_max' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function desiredSubCategory(): BelongsTo
    {
        return $this->belongsTo(Sub_categories::class, 'desired_sub_categories_id');
    }
}
