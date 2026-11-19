<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('workspaces', 'business_category')) {
            Schema::table('workspaces', function (Blueprint $table) {
                $table->string('business_category', 50)->default('general')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('workspaces', 'business_category')) {
            Schema::table('workspaces', function (Blueprint $table) {
                $table->dropColumn('business_category');
            });
        }
    }
};
