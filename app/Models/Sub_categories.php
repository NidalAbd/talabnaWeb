<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sub_categories extends Model
{
    use HasFactory;
    protected $table = 'sub_categories';
    protected $fillable = [
        'categories_id','name',
    ];
    protected $casts = [
        'name' => 'array', // Converts JSON to PHP array automatically
    ];
    protected $primaryKey = 'id';
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class,'categories_id');
    }

    public function servicePosts(): HasMany
    {
        return $this->hasMany(ServicePost::class, 'sub_categories_id');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_subcategory', 'sub_categories_id', 'user_id');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }

}
