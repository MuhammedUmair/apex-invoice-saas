<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CMS Website Content Table
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('section')->default('general');
            $table->text('content')->nullable();
            $table->string('locale', 10)->default('en');
            $table->timestamps();
        });

        // 2. Multilingual Documentation Articles Table
        Schema::create('doc_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category')->default('Getting Started');
            $table->string('title_en');
            $table->string('title_ur')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_hi')->nullable();
            $table->string('title_tr')->nullable();
            $table->longText('content_en');
            $table->longText('content_ur')->nullable();
            $table->longText('content_ar')->nullable();
            $table->longText('content_hi')->nullable();
            $table->longText('content_tr')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Monthly Recurring Invoices Table
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('amount_cents')->default(0);
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->unsignedSmallInteger('day_of_month')->default(1);
            $table->boolean('require_vendor_approval')->default(true);
            $table->boolean('auto_send_email')->default(true);
            $table->json('items')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_run_date')->nullable();
            $table->date('last_run_date')->nullable();
            $table->enum('status', ['active', 'paused', 'pending_approval'])->default('active');
            $table->timestamps();
        });

        // 4. Add Late Surcharge columns to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('has_late_surcharge')->default(false)->after('scheduled_alert_vendor');
            $table->string('late_surcharge_type', 10)->default('percent')->after('has_late_surcharge');
            $table->decimal('late_surcharge_value', 10, 2)->default(5.00)->after('late_surcharge_type');
            $table->integer('late_surcharge_days')->default(7)->after('late_surcharge_value');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'has_late_surcharge',
                'late_surcharge_type',
                'late_surcharge_value',
                'late_surcharge_days',
            ]);
        });

        Schema::dropIfExists('recurring_invoices');
        Schema::dropIfExists('doc_articles');
        Schema::dropIfExists('cms_contents');
    }
};
