<?php

namespace App\Console\Commands;

use App\Services\InvoiceReminderService;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send automatic invoice payment reminders (45, 30, 15, 7, 3, 2 days before due) and publish scheduled invoices.';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceReminderService $reminderService): int
    {
        $this->info('Starting Invoicy payment reminder and scheduler processor...');

        // 1. Process scheduled future invoices
        $this->comment('Checking for scheduled invoices ready to publish...');
        $scheduledResult = $reminderService->processScheduledInvoices();
        $this->info("Published: {$scheduledResult['published']} scheduled invoice(s). Emails dispatched: {$scheduledResult['emails_sent']}.");

        // 2. Process milestone reminders (45, 30, 15, 7, 3, 2 days)
        $this->comment('Scanning unpaid invoices for 45, 30, 15, 7, 3, 2 day milestone triggers...');
        $reminderResult = $reminderService->processMilestoneReminders();
        $this->info("Checked {$reminderResult['checked']} unpaid invoice(s).");
        $this->info("Dispatched {$reminderResult['reminders_sent']} customer reminder(s) and {$reminderResult['vendor_alerts_sent']} vendor alert(s).");

        if (!empty($reminderResult['errors'])) {
            $this->warn('Encountered non-fatal warnings:');
            foreach ($reminderResult['errors'] as $err) {
                $this->warn(" - {$err}");
            }
        }

        $this->info('Reminder run finished successfully.');
        return Command::SUCCESS;
    }
}
