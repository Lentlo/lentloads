<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'sender_id',
        'body',
        'type',
        'offer_amount',
        'offer_status',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'offer_amount' => 'decimal:2',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            $message->uuid = $message->uuid ?? Str::uuid();
        });
    }

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->withTrashed();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOffers($query)
    {
        return $query->where('type', 'offer');
    }

    // Helper methods
    public function isOffer(): bool
    {
        return $this->type === 'offer';
    }

    public function acceptOffer(): void
    {
        $this->update(['offer_status' => 'accepted']);
    }

    public function rejectOffer(): void
    {
        $this->update(['offer_status' => 'rejected']);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}
