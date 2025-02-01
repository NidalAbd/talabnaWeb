<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Google\Service\Dfareporting\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable;

    use LaratrustUserTrait;
    use Notifiable, CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $appends = ['pointsBalance'];

    public function Permission()
    {
        return $this->belongsToMany(Permission::class,'permission_user');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'photo',
        'name',
        'gender',
        'city',
        'date_of_birth',
        'location_latitudes',
        'location_longitudes',
        'phones',
        'WatsNumber',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'pointsBalance' => 'integer',
    ];

    public function servicePosts(): HasMany
    {
        return $this->hasMany(ServicePost::class);
    }
    public function subcategories()
    {
        return $this->belongsToMany(Sub_categories::class, 'user_subcategory', 'user_id', 'sub_categories_id');
    }
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(cities::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function isFollowerBy(): bool
    {
        return $this->followers()->where('user_id', $this->id)->exists();
    }

    public function isFollowingBy(User $user): bool
    {
        return $this->following()->where('user_id', $user->id)->exists();
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function getPointsBalanceAttribute()
    {
        return palservice_points::where('user_id', $this->id)->sum('point');
    }
}
