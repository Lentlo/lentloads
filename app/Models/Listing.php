<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'price_type',
        'condition',
        'brand',
        'model',
        'year',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
        'is_featured',
        'is_urgent',
        'is_highlighted',
        'featured_until',
        'attributes',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'is_highlighted' => 'boolean',
        'attributes' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'featured_until' => 'datetime',
    ];

    protected $appends = ['primary_image_url', 'location', 'formatted_price'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            $listing->uuid = $listing->uuid ?? Str::uuid();
            if (empty($listing->slug)) {
                $listing->slug = Str::slug($listing->title) . '-' . Str::random(8);
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ListingImage::class)->where('is_primary', true);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function packages()
    {
        return $this->hasMany(UserPackage::class);
    }

    public function activePackages()
    {
        return $this->packages()->where('status', 'active')->where('expires_at', '>', now());
    }

    // Accessors
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $image = $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();

        if ($image) {
            return asset('storage/' . $image->path);
        }
        return asset('images/no-image.png');
    }

    public function getLocationAttribute(): string
    {
        return collect([$this->city, $this->state])
            ->filter()
            ->implode(', ');
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_type === 'free') {
            return 'Free';
        }
        if ($this->price_type === 'contact') {
            return 'Contact for price';
        }

        $formatted = '₹' . number_format($this->price, 0);
        if ($this->price_type === 'negotiable') {
            $formatted .= ' (Negotiable)';
        }
        return $formatted;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                    ->orWhere('featured_until', '>', now());
            });
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeInCategory($query, $categoryId)
    {
        $category = Category::find($categoryId);
        if ($category) {
            $categoryIds = $category->getAllChildrenIds();
            return $query->whereIn('category_id', $categoryIds);
        }
        return $query->where('category_id', $categoryId);
    }

    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 50)
    {
        $haversine = "(6371 * acos(cos(radians($latitude))
                     * cos(radians(latitude))
                     * cos(radians(longitude) - radians($longitude))
                     + sin(radians($latitude))
                     * sin(radians(latitude))))";

        return $query->selectRaw("*, {$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw("distance < ?", [$radiusKm])
            ->orderBy('distance');
    }

    // Helper methods
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementContacts(): void
    {
        $this->increment('contacts_count');
    }

    public function updateFavoritesCount(): void
    {
        $this->favorites_count = $this->favorites()->count();
        $this->save();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function markAsSold(): void
    {
        $this->update(['status' => 'sold']);
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'active',
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function isOwner(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }
}
