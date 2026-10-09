<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percent', 'fixed', 'trial_days']);
            $table->decimal('discount_value', 10, 2);
            $table->string('currency_restriction', 3)->nullable(); // e.g. USD only for fixed discounts
            $table->unsignedBigInteger('min_purchase_cents')->default(0);
            $table->enum('duration', ['once', 'repeating', 'forever', 'trial_extension'])->default('once');
            $table->unsignedInteger('duration_months')->nullable();
            $table->unsignedInteger('max_redemptions')->nullable(); // Global limit
            $table->unsignedInteger('times_redeemed')->default(0);  // Atomic incremented
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('stripe_coupon_id')->nullable();
            $table->string('paypal_discount_id')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->unsignedBigInteger('discount_applied_cents');
            $table->string('currency', 3)->default('USD');
            $table->string('subscription_id')->nullable();
            $table->string('gateway', 20)->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            // Guard against duplicate redemptions by the same agency workspace
            $table->unique(['coupon_id', 'workspace_id'], 'unique_coupon_per_workspace');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
    }
};
