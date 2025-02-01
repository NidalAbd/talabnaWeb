<?php

namespace App\Models;
use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    public $guarded = [];
    protected $table='permissions';
    public function users() {

        return $this->belongsToMany(User::class,'permission_user');

    }
}
