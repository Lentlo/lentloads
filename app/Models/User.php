<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified',
        'avatar',
        'bio',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'role',
        'status',
        'is_verified_seller',
        'notification_preferences',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'phone_verified' => 'boolean',
        'is_verified_seller' => 'boolean',
        'notification_preferences' => 'array',
        'last_active_at' => 'datetime',
        'rating' => 'decimal:2',
    ];

    protected $appends = ['avatar_url', 'location'];

    // Relationships
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedListings()
    {
        return $this->belongsToMany(Listing::class, 'favorites')
            ->withTimestamps();
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'buyer_id')
            ->orWhere('seller_id', $this->id);
    }

    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    // Accessors
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    public function getLocationAttribute(): string
    {
        return collect([$this->city, $this->state, $this->country])
            ->filter()
            ->implode(', ');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeVerifiedSellers($query)
    {
        return $query->where('is_verified_seller', true);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function canManageListings(): bool
    {
        return $this->isModerator();
    }

    public function updateRating(): void
    {
        $reviews = $this->reviewsReceived()->where('status', 'approved');
        $this->rating = $reviews->avg('rating') ?? 0;
        $this->total_reviews = $reviews->count();
        $this->save();
    }

    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
