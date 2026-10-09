<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('prefix', 20)->default('INV');
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['workspace_id', 'prefix', 'year'], 'unique_workspace_prefix_year');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('invoice_number', 50);
            $table->enum('status', [
                'draft',
                'issued',
                'sent',
                'partially_paid',
                'paid',
                'overdue',
                'void',
                'credited'
            ])->default('draft');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('subtotal_cents')->default(0);
            $table->string('tax_name', 50)->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('discount_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);
            $table->unsignedBigInteger('paid_cents')->default(0);
            $table->unsignedBigInteger('due_cents')->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            
            // PDF security & Customer Access
            $table->boolean('is_pdf_password_protected')->default(false);
            $table->string('pdf_password_hash')->nullable();
            $table->string('public_secure_token', 64)->unique();
            
            // Financial Immutability Snapshots
            $table->json('client_snapshot')->nullable();
            $table->json('workspace_snapshot')->nullable();
            
            $table->string('language', 5)->default('en');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['workspace_id', 'invoice_number'], 'unique_workspace_invoice_number');
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'client_id']);
            $table->index(['workspace_id', 'due_date']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit', 50)->default('unit');
            $table->unsignedBigInteger('rate_cents')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('amount_cents')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invoice_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_sequences');
    }
};
