<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable(); // VAT, TRN, EIN, GSTIN
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code', 2)->default('US');
            $table->string('preferred_currency', 3)->default('USD');
            $table->string('preferred_language', 5)->default('en'); // en, es, fr, de, ar, ur, hi, tr, pt
            $table->string('default_payment_terms')->default('net_14'); // due_on_receipt, net_7, net_14, net_30
            $table->boolean('is_tax_exempt')->default(false);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['workspace_id', 'company_name']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('General');
            $table->text('description')->nullable();
            $table->string('unit', 50)->default('unit'); // project, month, hour, pack, design, etc.
            $table->unsignedBigInteger('rate_cents')->default(0); // integer cents for precision
            $table->decimal('default_tax_rate', 5, 2)->default(0.00);
            $table->string('sku', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['workspace_id', 'category']);
        });

        Schema::create('industry_presets', function (Blueprint $table) {
            $table->id();
            $table->string('industry_key', 50)->unique();
            $table->string('industry_name');
            $table->string('icon', 50)->default('briefcase');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('preset_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_preset_id')->constrained('industry_presets')->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->text('description');
            $table->string('default_unit', 50);
            $table->unsignedBigInteger('suggested_rate_cents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preset_items');
        Schema::dropIfExists('industry_presets');
        Schema::dropIfExists('services');
        Schema::dropIfExists('clients');
    }
};
