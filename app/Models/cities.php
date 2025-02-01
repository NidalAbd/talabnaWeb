<?php

namespace App\Models;

use Google\Service\Dfareporting\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class cities extends Model
{
    use HasFactory;

    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class);
    }
}
