<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'read_at',
        'type',
        'meta',
        'reply_to_id',
        'reactions',
        'deleted_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'meta' => 'array',
        'reactions' => 'array',
        'body' => \App\Casts\EncryptedText::class,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /** Short preview for conversation lists and pushes. */
    public function preview(): string
    {
        if ($this->deleted_at) return '🚫 Message deleted';
        return match ($this->type) {
            'image' => '📷 Photo' . ($this->body ? ': ' . $this->body : ''),
            'voice' => '🎤 Voice message',
            'location' => '📍 Location',
            'post' => '🔗 ' . ($this->meta['title'] ?? 'Listing'),
            'system' => (string) $this->body,
            default => (string) $this->body,
        };
    }

    public function toPublic(): array
    {
        $deleted = (bool) $this->deleted_at;
        $reply = $this->relationLoaded('replyTo') ? $this->replyTo : null;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'type' => $this->type ?? 'text',
            'body' => $deleted ? null : $this->body,
            'meta' => $deleted ? null : $this->meta,
            'deleted' => $deleted,
            'reply_to' => $reply ? [
                'id' => $reply->id,
                'sender_id' => $reply->sender_id,
                'type' => $reply->type ?? 'text',
                'preview' => $reply->preview(),
            ] : null,
            'reactions' => $this->reactions ?: (object) [],
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
