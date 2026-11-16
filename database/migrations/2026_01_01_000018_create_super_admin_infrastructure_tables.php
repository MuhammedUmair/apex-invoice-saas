<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add admin_role column to users table if not exists
        if (!Schema::hasColumn('users', 'admin_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('admin_role', 50)->nullable()->after('is_super_admin')
                    ->comment('Platform admin sub-role: super_admin, finance_admin, support_admin, technical_admin');
            });
        }

        // 2. Login Histories table
        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('email')->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->text('user_agent')->nullable();
                $table->string('device', 100)->nullable();
                $table->string('status', 30)->default('successful')->index(); // successful, failed, blocked
                $table->string('failure_reason')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 3. System Announcements table
        if (!Schema::hasTable('system_announcements')) {
            Schema::create('system_announcements', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->text('message');
                $table->string('type', 30)->default('info'); // info, warning, success, danger
                $table->string('target_role', 30)->default('all'); // all, owners, admins, staff
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();

                $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 4. Support Tickets table
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('ticket_number', 50)->unique();
                $table->uuid('workspace_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('subject');
                $table->string('category', 50)->default('general')->index(); // billing, technical, feature_request, general
                $table->string('priority', 30)->default('medium')->index(); // low, medium, high, urgent
                $table->string('status', 30)->default('open')->index(); // open, in_progress, waiting_on_customer, resolved, closed
                $table->unsignedBigInteger('assigned_to_user_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('workspace_id')->references('id')->on('workspaces')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('assigned_to_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 5. Ticket Messages table
        if (!Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('ticket_id')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('is_admin_reply')->default(false);
                $table->text('message');
                $table->json('attachments_json')->nullable();
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('support_tickets')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 6. User Feedback table
        if (!Schema::hasTable('user_feedback')) {
            Schema::create('user_feedback', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('workspace_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedTinyInteger('rating')->default(5); // 1 - 5 stars
                $table->string('category', 50)->default('general'); // ui, feature, performance, billing, general
                $table->text('comment');
                $table->string('status', 30)->default('new')->index(); // new, reviewed, actioned, archived
                $table->timestamps();

                $table->foreign('workspace_id')->references('id')->on('workspaces')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 7. Impersonation / Assisted Access Logs table
        if (!Schema::hasTable('impersonation_logs')) {
            Schema::create('impersonation_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('operator_user_id')->index();
                $table->uuid('target_workspace_id')->index();
                $table->unsignedBigInteger('target_user_id')->nullable()->index();
                $table->text('reason');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('ended_at')->nullable();

                $table->foreign('operator_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('target_workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
                $table->foreign('target_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
        Schema::dropIfExists('user_feedback');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('system_announcements');
        Schema::dropIfExists('login_histories');

        if (Schema::hasColumn('users', 'admin_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('admin_role');
            });
        }
    }
};
