<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAnnouncement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_role',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'warning' => 'bg-amber-100 text-amber-800 border-amber-300',
            'danger' => 'bg-rose-100 text-rose-800 border-rose-300',
            'success' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default => 'bg-blue-100 text-blue-800 border-blue-300',
        };
    }
}
