<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'transactionable_type',
        'transactionable_id',
        'amount',
        'currency',
        'type',
        'status',
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'gateway_response',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->uuid = $transaction->uuid ?? Str::uuid();
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function refund(): void
    {
        $this->update(['status' => 'refunded']);
    }
}
