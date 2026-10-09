<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique(); // starter, pro, agency
            $table->unsignedBigInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_interval', ['month', 'year'])->default('month');
            $table->integer('invoice_limit')->default(25); // -1 for unlimited
            $table->integer('client_limit')->default(15);   // -1 for unlimited
            $table->boolean('has_whitelabel')->default(false);
            $table->boolean('has_client_portal')->default(false);
            $table->boolean('has_recurring')->default(false);
            $table->integer('team_seats')->default(1);
            $table->string('stripe_price_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans');
            $table->enum('gateway', ['stripe', 'paypal', 'trial'])->default('trial');
            $table->string('gateway_subscription_id')->nullable()->index();
            $table->string('gateway_customer_id')->nullable()->index();
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled', 'expired'])->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
