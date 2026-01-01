<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'is_featured',
        'is_urgent',
        'is_highlighted',
        'boost_score',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'is_highlighted' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('price');
    }
}
