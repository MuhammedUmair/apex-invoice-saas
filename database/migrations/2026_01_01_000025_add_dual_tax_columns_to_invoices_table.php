<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Dual Tax & Canadian Place-of-Supply columns on invoices
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'federal_tax_name')) {
                $table->string('federal_tax_name', 50)->nullable()->after('tax_name');
            }
            if (!Schema::hasColumn('invoices', 'federal_tax_rate')) {
                $table->decimal('federal_tax_rate', 6, 3)->nullable()->after('tax_rate');
            }
            if (!Schema::hasColumn('invoices', 'federal_tax_cents')) {
                $table->unsignedBigInteger('federal_tax_cents')->default(0)->after('tax_cents');
            }
            if (!Schema::hasColumn('invoices', 'provincial_tax_name')) {
                $table->string('provincial_tax_name', 50)->nullable()->after('federal_tax_name');
            }
            if (!Schema::hasColumn('invoices', 'provincial_tax_rate')) {
                $table->decimal('provincial_tax_rate', 6, 3)->nullable()->after('federal_tax_rate');
            }
            if (!Schema::hasColumn('invoices', 'provincial_tax_cents')) {
                $table->unsignedBigInteger('provincial_tax_cents')->default(0)->after('federal_tax_cents');
            }
            if (!Schema::hasColumn('invoices', 'is_dual_tax')) {
                $table->boolean('is_dual_tax')->default(false)->after('provincial_tax_cents');
            }
        });

        // 2. Structured province and optional email for clients
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'province')) {
                $table->string('province', 50)->nullable()->after('state');
            }
            // Allow email to be nullable for walk-in retail counter clients
            if (Schema::hasColumn('clients', 'email')) {
                $table->string('email', 255)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $cols = [
                'federal_tax_name',
                'federal_tax_rate',
                'federal_tax_cents',
                'provincial_tax_name',
                'provincial_tax_rate',
                'provincial_tax_cents',
                'is_dual_tax',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('invoices', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'province')) {
                $table->dropColumn('province');
            }
        });
    }
};
