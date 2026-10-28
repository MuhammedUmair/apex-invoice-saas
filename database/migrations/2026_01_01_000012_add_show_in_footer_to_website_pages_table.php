<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('website_pages', 'show_in_footer')) {
            Schema::table('website_pages', function (Blueprint $table) {
                $table->boolean('show_in_footer')->default(false)->after('show_in_nav');
            });
        }

        // Set default pages to show in footer
        DB::table('website_pages')->whereIn('slug', ['privacy-policy', 'terms-of-service', 'about-us'])
            ->update(['show_in_footer' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('website_pages', 'show_in_footer')) {
            Schema::table('website_pages', function (Blueprint $table) {
                $table->dropColumn('show_in_footer');
            });
        }
    }
};
