<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            if (!Schema::hasColumn('workspaces', 'province')) {
                $table->string('province', 50)->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('workspaces', 'default_tax_rate')) {
                $table->decimal('default_tax_rate', 6, 3)->default(0.000)->after('tax_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $cols = ['province', 'default_tax_rate'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('workspaces', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
