<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ticket_number',
        'workspace_id',
        'user_id',
        'subject',
        'category',
        'priority',
        'status',
        'sla_due_at',
        'assigned_to_user_id',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public static function generateTicketNumber(): string
    {
        return 'TCK-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(10, 99);
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
            'high' => 'bg-amber-100 text-amber-800 border-amber-300',
            'low' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'closed' => 'bg-slate-100 text-slate-600 border-slate-300',
            'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'waiting_on_customer' => 'bg-purple-100 text-purple-800 border-purple-300',
            'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    /**
     * Check if the 24-hour turnaround SLA has expired.
     */
    public function isSlaBreached(): bool
    {
        if (in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }

        if (!$this->sla_due_at) {
            return false;
        }

        return $this->sla_due_at->isPast();
    }

    /**
     * Get human-readable SLA remaining time.
     */
    public function getSlaRemainingAttribute(): string
    {
        if (in_array($this->status, ['resolved', 'closed'])) {
            return 'Resolved within SLA';
        }

        $due = $this->sla_due_at ?? $this->created_at->addHours(24);
        if ($due->isPast()) {
            return 'Overdue by ' . $due->diffForHumans(null, true);
        }

        return $due->diffForHumans(null, false, false, 2);
    }
}
