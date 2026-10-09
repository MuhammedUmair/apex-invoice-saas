<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('brand_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#1e293b');
            $table->string('currency', 3)->default('USD');
            $table->string('language', 5)->default('en');
            $table->string('country_code', 2)->default('US');
            $table->string('tax_id')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('bank_details')->nullable();
            $table->text('stripe_keys_encrypted')->nullable();
            $table->text('paypal_keys_encrypted')->nullable();
            $table->text('smtp_settings_encrypted')->nullable();
            $table->boolean('is_whitelabel_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['owner', 'admin', 'staff'])->default('owner');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('workspaces');
    }
};
