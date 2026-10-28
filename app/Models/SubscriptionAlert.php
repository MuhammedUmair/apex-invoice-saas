<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAlert extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'type',
        'title',
        'message',
        'metadata',
        'is_read',
        'email_sent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'signup' => 'bg-blue-50 text-blue-700 border-blue-200',
            'upgrade' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'upgrade_request' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
            'downgrade' => 'bg-amber-50 text-amber-700 border-amber-200',
            'trial_expired' => 'bg-rose-50 text-rose-700 border-rose-200',
            'renewal' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'signup' => 'ph-user-plus',
            'upgrade' => 'ph-trend-up',
            'upgrade_request' => 'ph-credit-card',
            'downgrade' => 'ph-trend-down',
            'trial_expired' => 'ph-hourglass-low',
            'renewal' => 'ph-arrow-clockwise',
            default => 'ph-bell',
        };
    }
}
