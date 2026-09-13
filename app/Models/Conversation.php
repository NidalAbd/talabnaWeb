<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'service_post_id',
        'last_message_body',
        'last_message_sender_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function servicePost(): BelongsTo
    {
        return $this->belongsTo(ServicePost::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** The other participant, relative to the given user id. */
    public function otherUser(int $currentUserId): User
    {
        return $this->user_one_id === $currentUserId ? $this->userTwo : $this->userOne;
    }

    /**
     * Find or create the single conversation between two users (optionally
     * scoped to a service post), always storing the pair with the lower id
     * first so the same thread is found regardless of who initiates.
     */
    public static function between(int $userA, int $userB, ?int $servicePostId = null): self
    {
        [$one, $two] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        return static::firstOrCreate([
            'user_one_id' => $one,
            'user_two_id' => $two,
            'service_post_id' => $servicePostId,
        ]);
    }
}
