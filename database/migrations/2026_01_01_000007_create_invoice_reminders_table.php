<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add Future Scheduling columns to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('scheduled_publish_date')->nullable()->after('due_date');
            $table->boolean('is_scheduled')->default(false)->after('scheduled_publish_date');
            $table->boolean('scheduled_send_email')->default(true)->after('is_scheduled');
            $table->boolean('scheduled_alert_vendor')->default(true)->after('scheduled_send_email');
        });

        // 2. Create invoice_reminders table for 45, 30, 15, 7, 3, 2 day milestones & vendor alerts
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->integer('days_before'); // 45, 30, 15, 7, 3, 2, 0, or custom
            $table->string('recipient_customer_email')->nullable();
            $table->string('recipient_vendor_email')->nullable();
            $table->timestamp('customer_notified_at')->nullable();
            $table->timestamp('vendor_notified_at')->nullable();
            $table->string('status')->default('sent'); // sent, failed, manual
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_publish_date',
                'is_scheduled',
                'scheduled_send_email',
                'scheduled_alert_vendor',
            ]);
        });
    }
};
