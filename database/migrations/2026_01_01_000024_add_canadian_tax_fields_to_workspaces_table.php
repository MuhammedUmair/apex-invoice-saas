<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            if (!Schema::hasColumn('workspaces', 'cra_bn_number')) {
                $table->string('cra_bn_number', 30)->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('workspaces', 'qst_number')) {
                $table->string('qst_number', 30)->nullable()->after('cra_bn_number');
            }
            if (!Schema::hasColumn('workspaces', 'small_supplier_exempt')) {
                $table->boolean('small_supplier_exempt')->default(false)->after('qst_number');
            }
            if (!Schema::hasColumn('workspaces', 'collect_qst')) {
                $table->boolean('collect_qst')->default(true)->after('small_supplier_exempt');
            }
            if (!Schema::hasColumn('workspaces', 'collect_pst')) {
                $table->boolean('collect_pst')->default(true)->after('collect_qst');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $cols = ['cra_bn_number', 'qst_number', 'small_supplier_exempt', 'collect_qst', 'collect_pst'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('workspaces', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
