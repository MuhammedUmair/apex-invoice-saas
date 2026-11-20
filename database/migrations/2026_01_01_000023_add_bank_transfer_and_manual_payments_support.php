<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            if (!Schema::hasColumn('workspaces', 'bank_transfer_instructions')) {
                $table->text('bank_transfer_instructions')->nullable()->after('bank_details');
            }
            if (!Schema::hasColumn('workspaces', 'enable_paypal')) {
                $table->boolean('enable_paypal')->default(true)->after('bank_transfer_instructions');
            }
            if (!Schema::hasColumn('workspaces', 'enable_bank_transfer')) {
                $table->boolean('enable_bank_transfer')->default(true)->after('enable_paypal');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'fee_cents')) {
                $table->unsignedBigInteger('fee_cents')->default(0)->after('amount_cents');
            }
            if (!Schema::hasColumn('payments', 'net_amount_cents')) {
                $table->unsignedBigInteger('net_amount_cents')->default(0)->after('fee_cents');
            }
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('gateway');
            }
            if (!Schema::hasColumn('payments', 'reference_number')) {
                $table->string('reference_number', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('recorded_by_user_id');
            }
            if (!Schema::hasColumn('payments', 'void_reason')) {
                $table->text('void_reason')->nullable()->after('voided_at');
            }
            if (!Schema::hasColumn('payments', 'voided_by_user_id')) {
                $table->foreignId('voided_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('void_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'voided_by_user_id')) {
                $table->dropForeign(['voided_by_user_id']);
                $table->dropColumn('voided_by_user_id');
            }
            $columns = ['fee_cents', 'net_amount_cents', 'payment_method', 'reference_number', 'voided_at', 'void_reason'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('workspaces', function (Blueprint $table) {
            $cols = ['bank_transfer_instructions', 'enable_paypal', 'enable_bank_transfer'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('workspaces', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
