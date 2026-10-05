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
        'last_message_body' => \App\Casts\EncryptedText::class,
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
        $pair = static::where('user_one_id', $one)->where('user_two_id', $two);

        // One direct chat per pair of people. The listing they're talking about is just
        // the chat's current topic (and is sent as a card), not a separate thread.
        if ($servicePostId && ($same = (clone $pair)->where('service_post_id', $servicePostId)->first())) {
            return $same;
        }
        $latest = (clone $pair)->orderByDesc('last_message_at')->orderByDesc('id')->first();
        if (!$latest) {
            return static::create(['user_one_id' => $one, 'user_two_id' => $two, 'service_post_id' => $servicePostId]);
        }
        if ($servicePostId) {
            $latest->update(['service_post_id' => $servicePostId]);
        }

        return $latest;
    }

    /** Make [$servicePostId] the chat's topic, unless another chat of this pair already uses it. */
    public function switchTopic(int $servicePostId): void
    {
        if ($this->service_post_id === $servicePostId) return;
        $taken = static::where('user_one_id', $this->user_one_id)->where('user_two_id', $this->user_two_id)
            ->where('service_post_id', $servicePostId)->where('id', '!=', $this->id)->exists();
        if (!$taken) $this->update(['service_post_id' => $servicePostId]);
    }
}
