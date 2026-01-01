<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'query',
        'category_id',
        'city',
        'min_price',
        'max_price',
        'filters',
        'notify_email',
        'notify_push',
        'notify_frequency',
        'last_notified_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'notify_email' => 'boolean',
        'notify_push' => 'boolean',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'last_notified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Methods
    public function getMatchingListings()
    {
        $query = Listing::active()->notExpired();

        if ($this->query) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->query}%")
                    ->orWhere('description', 'like', "%{$this->query}%");
            });
        }

        if ($this->category_id) {
            $query->inCategory($this->category_id);
        }

        if ($this->city) {
            $query->inCity($this->city);
        }

        if ($this->min_price || $this->max_price) {
            $query->priceRange($this->min_price, $this->max_price);
        }

        if ($this->last_notified_at) {
            $query->where('published_at', '>', $this->last_notified_at);
        }

        return $query->latest('published_at')->get();
    }

    public function markNotified(): void
    {
        $this->update(['last_notified_at' => now()]);
    }
}
