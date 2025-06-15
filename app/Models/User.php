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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
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
        'data_saver_enabled',
        'google_id',
        'auth_type',
        'fcm_token',
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
        'data_saver_enabled' => 'boolean',
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

    public function adminlte_profile_url()
    {
        // Using your existing user.profile route instead of a non-existent 'profile' route
        return route('user.profile', ['user' => $this->id]);
    }

    public function adminlte_desc()
    {
        // You can customize this with any user attribute that would work as a description
        return $this->role ?? $this->email ?? 'User';
    }

    public function adminlte_image()
    {
        // Get the first non-video photo from the user
        $profilePhoto = $this->photos()->where('isVideo', false)->first();

        if ($profilePhoto) {
            // If it's an external image (like from a URL), return the src directly
            if ($profilePhoto->is_external) {
                return $profilePhoto->src;
            }

            // If it's a local file, prepend with asset()
            return asset($profilePhoto->src);
        }

        // Return default image if no photo is found
        return asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    public function getProfileImageAttribute()
    {
        return $this->adminlte_image();
    }

    /**
     * Helper method to check if user has a profile photo
     */
    public function hasProfilePhoto()
    {
        return $this->photos()->where('isVideo', false)->exists();
    }

    public function bannedDevices(): HasMany
    {
        return $this->hasMany(BannedDevice::class);
    }

    /**
     * Get the ban history records for this user.
     */
    public function banHistories(): HasMany
    {
        return $this->hasMany(BanHistory::class);
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->is_active === 'banned';
    }

    /**
     * Ban this user.
     *
     * @param string $reason The reason for banning
     * @param string|null $deviceId The device ID to ban
     * @param array $deviceDetails Additional device details
     * @param int|null $performedBy ID of admin who performed this action
     * @return BannedDevice|null The newly created banned device record
     */
    public function ban(string $reason, ?string $deviceId = null, array $deviceDetails = [], ?int $performedBy = null): ?BannedDevice
    {
        // Update user status
        $this->is_active = 'banned';
        $this->save();

        // Record in history if BanHistory model exists
        if (class_exists(BanHistory::class)) {
            BanHistory::create([
                'user_id' => $this->id,
                'banned_device_id' => null,
                'action' => 'ban',
                'performed_by' => $performedBy,
                'reason' => $reason,
            ]);
        }

        // Create a banned device record if device ID is provided
        if ($deviceId) {
            $bannedDevice = BannedDevice::create([
                'user_id' => $this->id,
                'device_id' => $deviceId,
                'device_name' => $deviceDetails['device_name'] ?? null,
                'device_brand' => $deviceDetails['device_brand'] ?? null,
                'device_model' => $deviceDetails['device_model'] ?? null,
                'os_version' => $deviceDetails['os_version'] ?? null,
                'ip_address' => $deviceDetails['ip_address'] ?? null,
                'fcm_token' => $this->fcm_token,
                'email' => $this->email,
                'phone' => $this->phones,
                'ban_reason' => $reason,
                'banned_at' => now(),
            ]);

            // Record in history if BanHistory model exists
            if (class_exists(BanHistory::class)) {
                BanHistory::create([
                    'user_id' => $this->id,
                    'banned_device_id' => $bannedDevice->id,
                    'action' => 'ban',
                    'performed_by' => $performedBy,
                    'reason' => $reason,
                ]);
            }

            return $bannedDevice;
        }

        return null;
    }

    /**
     * Unban this user.
     *
     * @param string|null $reason The reason for unbanning
     * @param int|null $performedBy ID of admin who performed this action
     */
    public function unban(?string $reason = null, ?int $performedBy = null): void
    {
        $this->is_active = 'active';
        $this->save();

        // Record in history if BanHistory model exists
        if (class_exists(BanHistory::class)) {
            BanHistory::create([
                'user_id' => $this->id,
                'banned_device_id' => null,
                'action' => 'unban',
                'performed_by' => $performedBy,
                'reason' => $reason ?? 'Manual unban',
            ]);
        }

        // Set unban_at for all the user's banned devices
        $this->bannedDevices()->update(['unban_at' => now()]);
    }
}
