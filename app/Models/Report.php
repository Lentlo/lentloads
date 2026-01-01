<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const REASONS = [
        'spam' => 'Spam',
        'fake' => 'Fake listing',
        'offensive' => 'Offensive content',
        'prohibited_item' => 'Prohibited item',
        'wrong_category' => 'Wrong category',
        'duplicate' => 'Duplicate listing',
        'fraud' => 'Fraud/Scam',
        'harassment' => 'Harassment',
        'other' => 'Other',
    ];

    // Relationships
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    // Methods
    public function markAsReviewed(User $admin, string $status, ?string $notes = null): void
    {
        $this->update([
            'status' => $status,
            'reviewed_by' => $admin->id,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }
}
