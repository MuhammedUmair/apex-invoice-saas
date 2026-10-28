<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'workspace_id')) {
                $table->uuid('workspace_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('coupons', 'name')) {
                $table->string('name')->nullable()->after('code');
            }
            if (!Schema::hasColumn('coupons', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('coupons', 'offer_type')) {
                $table->string('offer_type', 30)->default('discount')->after('discount_type');
            }
            if (!Schema::hasColumn('coupons', 'service_id')) {
                $table->uuid('service_id')->nullable()->after('offer_type');
            }
            if (!Schema::hasColumn('coupons', 'service_name')) {
                $table->string('service_name')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('coupons', 'target')) {
                $table->string('target', 30)->default('all')->after('service_name');
            }
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            if (!Schema::hasColumn('coupon_redemptions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('workspace_id');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'invoice_id')) {
                $table->uuid('invoice_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'client_id')) {
                $table->uuid('client_id')->nullable()->after('invoice_id');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'service_granted')) {
                $table->string('service_granted')->nullable()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'invoice_id', 'client_id', 'service_granted']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['workspace_id', 'name', 'description', 'offer_type', 'service_id', 'service_name', 'target']);
        });
    }
};
