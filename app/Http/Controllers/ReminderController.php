<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Services\InvoiceReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        // 1. Future scheduled invoices waiting to be published
        $scheduledInvoices = Invoice::where('is_scheduled', true)
            ->with('client')
            ->orderBy('scheduled_publish_date', 'asc')
            ->get();

        // 2. Unpaid invoices subject to milestone reminders (45, 30, 15, 7, 3, 2 days)
        $unpaidInvoices = Invoice::where('due_cents', '>', 0)
            ->whereIn('status', ['issued', 'sent', 'partially_paid'])
            ->with(['client', 'reminders'])
            ->orderBy('due_date', 'asc')
            ->get();

        // 3. Complete audit log of sent reminders
        $logs = InvoiceReminder::with(['invoice.client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('reminders.index', compact('scheduledInvoices', 'unpaidInvoices', 'logs'));
    }

    /**
     * Trigger immediate cycle of reminder check and scheduled publishing.
     */
    public function runCycle(InvoiceReminderService $reminderService)
    {
        $sched = $reminderService->processScheduledInvoices();
        $rem = $reminderService->processMilestoneReminders();

        $msg = "Scheduler executed! Published {$sched['published']} scheduled invoice(s). Sent {$rem['reminders_sent']} milestone reminder(s) and {$rem['vendor_alerts_sent']} vendor alert(s).";

        return back()->with('message', $msg);
    }
}
