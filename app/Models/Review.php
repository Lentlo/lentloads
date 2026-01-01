<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'reviewed_id',
        'listing_id',
        'rating',
        'comment',
        'type',
        'is_verified_purchase',
        'status',
        'seller_response',
        'seller_responded_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'seller_responded_at' => 'datetime',
    ];

    // Relationships
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForSeller($query)
    {
        return $query->where('type', 'seller');
    }

    public function scopeForBuyer($query)
    {
        return $query->where('type', 'buyer');
    }

    // Methods
    public function respond(string $response): void
    {
        $this->update([
            'seller_response' => $response,
            'seller_responded_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->reviewed->updateRating();
        });

        static::updated(function ($review) {
            $review->reviewed->updateRating();
        });

        static::deleted(function ($review) {
            $review->reviewed->updateRating();
        });
    }
}
