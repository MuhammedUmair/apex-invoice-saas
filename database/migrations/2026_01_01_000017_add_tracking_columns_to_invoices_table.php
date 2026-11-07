<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'viewed_at')) {
                $table->timestamp('viewed_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('invoices', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('viewed_at');
            }
            if (!Schema::hasColumn('invoices', 'email_opened_at')) {
                $table->timestamp('email_opened_at')->nullable()->after('view_count');
            }
            if (!Schema::hasColumn('invoices', 'email_opens_count')) {
                $table->unsignedInteger('email_opens_count')->default(0)->after('email_opened_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['viewed_at', 'view_count', 'email_opened_at', 'email_opens_count']);
        });
    }
};
