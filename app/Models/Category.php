<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'parent_id',
        'order',
        'is_active',
        'is_featured',
        'custom_fields',
        'listings_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'custom_fields' => 'array',
    ];

    protected $appends = ['icon_url', 'image_url', 'full_name', 'total_active_listings_count'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function activeListings(): HasMany
    {
        return $this->hasMany(Listing::class)->where('status', 'active');
    }

    // Accessors
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function getTotalActiveListingsCountAttribute(): int
    {
        $categoryIds = $this->getAllChildrenIds();
        return Listing::whereIn('category_id', $categoryIds)
            ->where('status', 'active')
            ->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // Helper methods
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
    }

    public function updateListingsCount(): void
    {
        $this->listings_count = $this->listings()->where('status', 'active')->count();
        $this->save();
    }

    public function getAncestors(): array
    {
        $ancestors = [];
        $category = $this;
        while ($category->parent) {
            array_unshift($ancestors, $category->parent);
            $category = $category->parent;
        }
        return $ancestors;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = $this->getAncestors();
        $breadcrumb[] = $this;
        return $breadcrumb;
    }
}
