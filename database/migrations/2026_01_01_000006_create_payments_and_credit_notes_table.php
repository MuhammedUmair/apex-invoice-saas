<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('payment_number', 50);
            $table->enum('gateway', ['stripe', 'paypal', 'bank_transfer', 'cash', 'manual'])->default('manual');
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3)->default('USD');
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('idempotency_key', 100)->unique();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->json('payment_method_details')->nullable();
            $table->timestamp('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['workspace_id', 'invoice_id']);
            $table->index(['workspace_id', 'payment_date']);
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('credit_note_number', 50);
            $table->string('reason');
            $table->unsignedBigInteger('amount_cents');
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['issued', 'applied', 'refunded', 'void'])->default('issued');
            $table->date('credit_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['workspace_id', 'credit_note_number'], 'unique_workspace_credit_note');
            $table->index(['workspace_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payments');
    }
};
