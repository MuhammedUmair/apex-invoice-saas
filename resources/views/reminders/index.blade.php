@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-red-50 text-red-600 border border-red-200">
                    <i class="ph ph-bell-ringing text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Invoice Reminders & Scheduler</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Automated multi-tier customer payment reminders (45, 30, 15, 7, 3, 2 days before due), vendor alerts & future scheduled invoice automation.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('reminders.run') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow transition">
                    <i class="ph ph-arrows-clockwise text-base"></i>
                    Run Scheduler Cycle Now
                </button>
            </form>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 transition">
                <i class="ph ph-plus-circle text-base"></i>
                Schedule New Invoice
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
            <i class="ph ph-check-circle text-lg text-emerald-600"></i>
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                <i class="ph ph-calendar-blank text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-slate-500">Planned Future Invoices</p>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $scheduledInvoices->count() }}</h3>
                <p class="text-xs text-slate-400 mt-1">Waiting for planned dispatch</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center shrink-0">
                <i class="ph ph-hourglass-medium text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-slate-500">Monitored Invoices</p>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $unpaidInvoices->count() }}</h3>
                <p class="text-xs text-slate-400 mt-1">Tracking 45, 30, 15, 7, 3, 2 day milestones</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                <i class="ph ph-paper-plane-tilt text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-slate-500">Total Reminders Sent</p>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $logs->total() }}</h3>
                <p class="text-xs text-slate-400 mt-1">Customer & vendor alerts logged</p>
            </div>
        </div>
    </div>

    <!-- Section 1: Planned Future Invoices -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-slate-900 text-base">Planned Future Invoices</h3>
                <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full ml-1">
                    {{ $scheduledInvoices->count() }} Queued
                </span>
            </div>
            <p class="text-xs text-slate-500">Auto-publishes and emails customer when scheduled date is reached</p>
        </div>

        @if($scheduledInvoices->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                <i class="ph ph-calendar-plus text-3xl mb-2 text-slate-300"></i>
                <p>No invoices currently scheduled for future publishing.</p>
                <p class="text-xs text-slate-400 mt-1">To plan an invoice for next week or month, check "Plan Future Invoice" in the Invoice Studio.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Invoice #</th>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Scheduled Date</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Auto Email Client</th>
                            <th class="px-6 py-3">Alert Vendor</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($scheduledInvoices as $inv)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="text-red-600 hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800">{{ $inv->client->name ?? 'Walk-in' }}</span>
                                    <span class="block text-xs text-slate-400">{{ $inv->client->email ?? 'No email' }}</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-amber-700">
                                    <i class="ph ph-clock mr-1"></i>
                                    {{ $inv->scheduled_publish_date ? $inv->scheduled_publish_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-black text-slate-900">
                                    {{ $inv->formatted_total }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($inv->scheduled_send_email)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            <i class="ph ph-check mr-1"></i> Yes
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($inv->scheduled_alert_vendor)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="ph ph-check mr-1"></i> Yes
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold">
                                        <i class="ph ph-pencil-simple"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section 2: Active Milestone Monitoring (45, 30, 15, 7, 3, 2 Days) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-600"></span>
                <h3 class="font-bold text-slate-900 text-base">Unpaid Invoices & Milestone Timeline</h3>
            </div>
            <p class="text-xs text-slate-500">Automated triggers: 45, 30, 15, 7, 3, and 2 days before payment due</p>
        </div>

        @if($unpaidInvoices->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                <i class="ph ph-check-circle text-3xl mb-2 text-emerald-500"></i>
                <p>All invoices are paid in full! No active reminder milestones pending.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Invoice #</th>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Due Date</th>
                            <th class="px-6 py-3">Balance Due</th>
                            <th class="px-6 py-3">Milestone Progress (Days Before Due)</th>
                            <th class="px-6 py-3 text-right">Manual Trigger</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unpaidInvoices as $inv)
                            @php
                                $daysRemaining = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($inv->due_date)->startOfDay(), false);
                                $sentMilestones = $inv->reminders->pluck('days_before')->toArray();
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="text-red-600 hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800">{{ $inv->client->name ?? 'Customer' }}</span>
                                    <span class="block text-xs text-slate-400">{{ $inv->client->email }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold {{ $daysRemaining < 0 ? 'text-rose-600' : ($daysRemaining <= 7 ? 'text-amber-600' : 'text-slate-800') }}">
                                        {{ $inv->due_date ? $inv->due_date->format('M d, Y') : 'N/A' }}
                                    </span>
                                    <span class="block text-xs text-slate-400">
                                        @if($daysRemaining > 0)
                                            Due in {{ $daysRemaining }} day(s)
                                        @elseif($daysRemaining === 0)
                                            Due Today!
                                        @else
                                            {{ abs($daysRemaining) }} days overdue
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-black text-red-600">
                                    {{ $inv->formatted_due }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach([45, 30, 15, 7, 3, 2] as $m)
                                            @php
                                                $isSent = in_array($m, $sentMilestones, true);
                                                $isPast = $daysRemaining < $m;
                                            @endphp
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $isSent ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($isPast ? 'bg-slate-100 text-slate-400 border-slate-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}" title="{{ $isSent ? 'Sent to customer & vendor' : ($isPast ? 'Passed' : 'Upcoming trigger') }}">
                                                {{ $m }}d {{ $isSent ? '✓' : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('invoices.reminder', $inv->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 font-bold border border-red-200 transition">
                                            <i class="ph ph-paper-plane-tilt"></i> Send Now
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section 3: Dispatched Reminders Audit Trail -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Reminder Delivery Audit Trail</h3>
            <p class="text-xs text-slate-500">Historical proof of customer reminder & vendor notification emails</p>
        </div>

        @if($logs->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                <p>No reminders logged in audit ledger yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Dispatched At</th>
                            <th class="px-6 py-3">Invoice #</th>
                            <th class="px-6 py-3">Customer Email</th>
                            <th class="px-6 py-3">Vendor Alert Email</th>
                            <th class="px-6 py-3">Milestone</th>
                            <th class="px-6 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-xs text-slate-500">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-3 font-bold text-slate-900">
                                    <a href="{{ route('invoices.show', $log->invoice_id) }}" class="text-red-600 hover:underline">
                                        {{ $log->invoice->invoice_number ?? 'Invoice' }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-slate-700 font-medium">
                                    {{ $log->recipient_customer_email }}
                                </td>
                                <td class="px-6 py-3 text-slate-500 text-xs">
                                    {{ $log->recipient_vendor_email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-800">
                                        {{ $log->days_before }} Days Before Due
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $log->status === 'sent' || $log->status === 'manual' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
