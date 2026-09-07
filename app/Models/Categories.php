<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Categories extends Model
{
    use HasFactory;
    protected $table = 'categories'; // تأكد من أن الاسم مطابق لقاعدة البيانات
    protected $fillable = [
        'name',
        'is_featured',
        'is_popular',
        'is_job_category',
    ];
    protected $casts = [
        'name' => 'array', // Automatically decode JSON to an array
        'is_job_category' => 'boolean',
    ];
    public function sub_categories()
    {
        return $this->hasMany(Sub_categories::class);
    }

    public function servicePosts()
    {
        return $this->hasMany(ServicePost::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }


    public function subCategory()
    {
        return $this->hasMany(sub_categories::class,'sub_category_id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeJobCategory($query)
    {
        return $query->where('is_job_category', true);
    }
}
