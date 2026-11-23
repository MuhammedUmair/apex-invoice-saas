<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'workspace_id',
        'role',
        'content',
        'mode',
        'audio_duration_seconds',
        'interrupted',
        'tool_calls',
        'tool_results',
    ];

    protected $casts = [
        'interrupted' => 'boolean',
        'audio_duration_seconds' => 'integer',
        'tool_calls' => 'array',
        'tool_results' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
