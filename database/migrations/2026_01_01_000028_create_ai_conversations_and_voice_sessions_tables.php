<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_conversations')) {
            Schema::create('ai_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('session_id', 100)->index();
                $table->string('title', 255)->default('Support Conversation');
                $table->string('channel', 20)->default('text'); // text, voice, hybrid
                $table->string('status', 20)->default('active'); // active, closed, escalated
                $table->string('language', 10)->default('en');
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->boolean('resolved')->default(false);
                $table->tinyInteger('rating')->nullable(); // 1 = helpful, -1 = unhelpful
                $table->text('feedback')->nullable();
                $table->uuid('escalated_ticket_id')->nullable();
                $table->json('context_metadata')->nullable();
                $table->timestamps();

                $table->index(['workspace_id', 'created_at']);
                $table->index(['session_id', 'status']);
            });
        }

        if (!Schema::hasTable('ai_messages')) {
            Schema::create('ai_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
                $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
                $table->string('role', 20); // user, assistant, system
                $table->longText('content');
                $table->string('mode', 20)->default('text'); // text, voice
                $table->unsignedInteger('audio_duration_seconds')->nullable();
                $table->boolean('interrupted')->default(false);
                $table->json('tool_calls')->nullable();
                $table->json('tool_results')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('ai_voice_sessions')) {
            Schema::create('ai_voice_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('conversation_id')->nullable();
                $table->string('token', 64)->unique();
                $table->string('status', 20)->default('initiated'); // initiated, active, ended, interrupted, error
                $table->string('provider', 50)->default('browser_native_duplex');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['workspace_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_voice_sessions');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
