<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('support_tickets', 'sla_due_at')) {
                    $table->timestamp('sla_due_at')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (Schema::hasColumn('support_tickets', 'sla_due_at')) {
                    $table->dropColumn('sla_due_at');
                }
            });
        }
    }
};
