<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Categories extends Model
{
    use HasFactory;

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
}
