<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LtmTranslation extends Model
{
    protected $table = 'ltm_translations';
    protected $fillable = [
        'status', 'locale', 'group', 'key', 'value',
    ];
} 