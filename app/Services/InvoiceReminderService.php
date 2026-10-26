<?php

namespace App\Services;

use App\Mail\InvoiceMailable;
use App\Mail\InvoiceReminderMailable;
use App\Mail\VendorReminderAlertMailable;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderService
{
    /**
     * Standard milestone intervals: 45, 30, 15, 7, 3, 2 days before due date
     */
    public const MILESTONES = [45, 30, 15, 7, 3, 2];

    /**
     * Process milestone reminders for unpaid invoices.
     */
    public function processMilestoneReminders(): array
    {
        $today = Carbon::today();
        $results = [
            'checked' => 0,
            'reminders_sent' => 0,
            'vendor_alerts_sent' => 0,
            'errors' => [],
        ];

        $invoices = Invoice::where('due_cents', '>', 0)
            ->whereIn('status', ['issued', 'sent', 'partially_paid'])
            ->whereNotNull('due_date')
            ->with(['client', 'workspace.users'])
            ->get();

        $results['checked'] = $invoices->count();

        foreach ($invoices as $invoice) {
            if (!$invoice->client || empty($invoice->client->email)) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            // Positive if due in future, 0 if due today, negative if overdue
            $daysRemaining = (int) $today->diffInDays($dueDate, false);

            if (in_array($daysRemaining, self::MILESTONES, true)) {
                // Check if already sent for this invoice and milestone
                $alreadySent = InvoiceReminder::where('invoice_id', $invoice->id)
                    ->where('days_before', $daysRemaining)
                    ->exists();

                if (!$alreadySent) {
                    try {
                        // 1. Send Customer Reminder
                        Mail::to($invoice->client->email)->send(new InvoiceReminderMailable($invoice, $daysRemaining));
                        $results['reminders_sent']++;

                        // 2. Alert Vendor
                        $vendorEmail = $invoice->workspace->email 
                            ?? ($invoice->workspace->users->first()->email ?? null);

                        $vendorAlertSent = false;
                        if ($vendorEmail) {
                            Mail::to($vendorEmail)->send(new VendorReminderAlertMailable($invoice, $daysRemaining));
                            $results['vendor_alerts_sent']++;
                            $vendorAlertSent = true;
                        }

                        // 3. Record in audit ledger
                        InvoiceReminder::create([
                            'invoice_id' => $invoice->id,
                            'days_before' => $daysRemaining,
                            'recipient_customer_email' => $invoice->client->email,
                            'recipient_vendor_email' => $vendorEmail,
                            'customer_notified_at' => now(),
                            'vendor_notified_at' => $vendorAlertSent ? now() : null,
                            'status' => 'sent',
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to process reminder for invoice {$invoice->id}: " . $e->getMessage());
                        $results['errors'][] = "Invoice {$invoice->invoice_number}: " . $e->getMessage();

                        InvoiceReminder::create([
                            'invoice_id' => $invoice->id,
                            'days_before' => $daysRemaining,
                            'recipient_customer_email' => $invoice->client->email,
                            'recipient_vendor_email' => $vendorEmail ?? null,
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Publish scheduled invoices whose scheduled date has arrived.
     */
    public function processScheduledInvoices(): array
    {
        $today = Carbon::today();
        $results = [
            'published' => 0,
            'emails_sent' => 0,
            'errors' => [],
        ];

        $scheduled = Invoice::where('is_scheduled', true)
            ->whereDate('scheduled_publish_date', '<=', $today)
            ->where('status', 'draft')
            ->with(['client', 'workspace'])
            ->get();

        foreach ($scheduled as $invoice) {
            try {
                $invoice->is_scheduled = false;
                $invoice->status = 'issued';

                if ($invoice->scheduled_send_email && $invoice->client && $invoice->client->email) {
                    Mail::to($invoice->client->email)->send(new InvoiceMailable($invoice));
                    $invoice->status = 'sent';
                    $invoice->sent_at = now();
                    $results['emails_sent']++;
                }

                $invoice->save();
                $results['published']++;

                if ($invoice->scheduled_alert_vendor) {
                    $vendorEmail = $invoice->workspace->email ?? ($invoice->creator->email ?? null);
                    if ($vendorEmail) {
                        Mail::to($vendorEmail)->send(new VendorReminderAlertMailable($invoice, 0));
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to publish scheduled invoice {$invoice->id}: " . $e->getMessage());
                $results['errors'][] = "Invoice {$invoice->invoice_number}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Apply dynamic tenant SMTP settings if configured.
     */
    public function applyTenantSmtp(?\App\Models\Workspace $workspace): void
    {
        if ($workspace && !empty($workspace->smtp_settings_encrypted)) {
            $smtp = $workspace->smtp_settings_encrypted;
            if (!empty($smtp['host']) && !empty($smtp['username'])) {
                config([
                    'mail.mailers.smtp.host' => $smtp['host'],
                    'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                    'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                    'mail.mailers.smtp.username' => $smtp['username'],
                    'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                    'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                    'mail.from.name' => $smtp['from_name'] ?? ($workspace->name ?: 'Invoicy by RedMaple'),
                ]);
            }
        }
    }

    /**
     * Send immediate manual reminder on-demand.
     */
    public function sendManualReminder(Invoice $invoice): array
    {
        $invoice->load(['client', 'workspace']);
        if (!$invoice->client || empty($invoice->client->email)) {
            return ['success' => false, 'error' => 'Client has no registered email address in CRM.'];
        }

        $this->applyTenantSmtp($invoice->workspace);

        $today = Carbon::today();
        $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
        $daysRemaining = (int) $today->diffInDays($dueDate, false);
        $vendorEmail = $invoice->workspace->email ?? null;

        try {
            Mail::to($invoice->client->email)->send(new InvoiceReminderMailable($invoice, $daysRemaining));

            if ($vendorEmail) {
                try {
                    Mail::to($vendorEmail)->send(new VendorReminderAlertMailable($invoice, $daysRemaining));
                } catch (\Throwable $ve) {
                    Log::warning("Vendor alert delivery failed: " . $ve->getMessage());
                }
            }

            InvoiceReminder::create([
                'invoice_id' => $invoice->id,
                'days_before' => $daysRemaining,
                'recipient_customer_email' => $invoice->client->email,
                'recipient_vendor_email' => $vendorEmail,
                'customer_notified_at' => now(),
                'vendor_notified_at' => $vendorEmail ? now() : null,
                'status' => 'manual',
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error("Failed to send manual reminder for invoice {$invoice->id}: " . $e->getMessage());

            InvoiceReminder::create([
                'invoice_id' => $invoice->id,
                'days_before' => $daysRemaining,
                'recipient_customer_email' => $invoice->client->email,
                'recipient_vendor_email' => $vendorEmail,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "SMTP delivery failed: " . $e->getMessage() . ". Please configure valid SMTP Mailbox credentials in Agency Settings."
            ];
        }
    }
}
