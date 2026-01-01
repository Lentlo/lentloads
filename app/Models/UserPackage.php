<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'package_id',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function applyToListing(): void
    {
        $this->listing->update([
            'is_featured' => $this->listing->is_featured || $this->package->is_featured,
            'is_urgent' => $this->listing->is_urgent || $this->package->is_urgent,
            'is_highlighted' => $this->listing->is_highlighted || $this->package->is_highlighted,
            'featured_until' => $this->expires_at,
        ]);
    }
}
