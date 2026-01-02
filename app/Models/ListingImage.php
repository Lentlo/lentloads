<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'path',
        'thumbnail',
        'medium',
        'original_name',
        'size',
        'mime_type',
        'order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $appends = ['url', 'thumbnail_url', 'medium_url'];

    // Relationships
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    // Accessors - with file existence checks and fallbacks
    public function getUrlAttribute(): string
    {
        $disk = Storage::disk('public');

        // Try original, then medium, then thumbnail
        if ($this->path && $disk->exists($this->path)) {
            return asset('storage/' . $this->path);
        }
        if ($this->medium && $disk->exists($this->medium)) {
            return asset('storage/' . $this->medium);
        }
        if ($this->thumbnail && $disk->exists($this->thumbnail)) {
            return asset('storage/' . $this->thumbnail);
        }

        // Return placeholder if no files exist
        return 'https://placehold.co/800x600/6B7280/FFFFFF?text=No+Image';
    }

    public function getThumbnailUrlAttribute(): string
    {
        $disk = Storage::disk('public');

        if ($this->thumbnail && $disk->exists($this->thumbnail)) {
            return asset('storage/' . $this->thumbnail);
        }
        if ($this->medium && $disk->exists($this->medium)) {
            return asset('storage/' . $this->medium);
        }
        if ($this->path && $disk->exists($this->path)) {
            return asset('storage/' . $this->path);
        }

        return 'https://placehold.co/300x300/6B7280/FFFFFF?text=No+Image';
    }

    public function getMediumUrlAttribute(): string
    {
        $disk = Storage::disk('public');

        if ($this->medium && $disk->exists($this->medium)) {
            return asset('storage/' . $this->medium);
        }
        if ($this->path && $disk->exists($this->path)) {
            return asset('storage/' . $this->path);
        }
        if ($this->thumbnail && $disk->exists($this->thumbnail)) {
            return asset('storage/' . $this->thumbnail);
        }

        return 'https://placehold.co/800x600/6B7280/FFFFFF?text=No+Image';
    }

    // Methods
    public function deleteFiles(): void
    {
        Storage::disk('public')->delete([
            $this->path,
            $this->thumbnail,
            $this->medium,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            $image->deleteFiles();
        });
    }
}
