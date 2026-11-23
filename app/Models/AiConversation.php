<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'session_id',
        'title',
        'channel',
        'status',
        'language',
        'duration_seconds',
        'resolved',
        'rating',
        'feedback',
        'escalated_ticket_id',
        'context_metadata',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'rating' => 'integer',
        'duration_seconds' => 'integer',
        'context_metadata' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    public function voiceSessions(): HasMany
    {
        return $this->hasMany(AiVoiceSession::class, 'conversation_id');
    }

    public function escalatedTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'escalated_ticket_id');
    }
}
