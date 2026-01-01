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

    // Accessors
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : $this->url;
    }

    public function getMediumUrlAttribute(): string
    {
        return $this->medium
            ? asset('storage/' . $this->medium)
            : $this->url;
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
