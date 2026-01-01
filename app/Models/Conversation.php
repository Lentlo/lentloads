<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'listing_id',
        'buyer_id',
        'seller_id',
        'buyer_last_read_at',
        'seller_last_read_at',
        'buyer_deleted',
        'seller_deleted',
        'is_blocked',
        'blocked_by',
    ];

    protected $casts = [
        'buyer_last_read_at' => 'datetime',
        'seller_last_read_at' => 'datetime',
        'buyer_deleted' => 'boolean',
        'seller_deleted' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    protected $appends = ['unread_count'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            $conversation->uuid = $conversation->uuid ?? Str::uuid();
        });
    }

    // Relationships
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    // Accessors
    public function getUnreadCountAttribute(): int
    {
        return $this->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', auth()->id())
            ->count();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)->where('buyer_deleted', false);
        })->orWhere(function ($q) use ($userId) {
            $q->where('seller_id', $userId)->where('seller_deleted', false);
        });
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked', false);
    }

    // Helper methods
    public function getOtherUser(User $user): User
    {
        return $this->buyer_id === $user->id ? $this->seller : $this->buyer;
    }

    public function isParticipant(User $user): bool
    {
        return $this->buyer_id === $user->id || $this->seller_id === $user->id;
    }

    public function markAsRead(User $user): void
    {
        if ($this->buyer_id === $user->id) {
            $this->update(['buyer_last_read_at' => now()]);
        } else {
            $this->update(['seller_last_read_at' => now()]);
        }

        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function deleteFor(User $user): void
    {
        if ($this->buyer_id === $user->id) {
            $this->update(['buyer_deleted' => true]);
        } else {
            $this->update(['seller_deleted' => true]);
        }
    }

    public function block(User $user): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_by' => $user->id,
        ]);
    }

    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_by' => null,
        ]);
    }
}
