@extends('layouts.app', ['title' => 'Recurring Invoices - Invoicy', 'header' => 'Monthly Recurring Invoices & Retainers'])

@section('content')
<div class="space-y-8" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-red-50 text-red-600 border border-red-200">
                    <i class="ph ph-repeat text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Recurring Invoices & Retainers</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Automate monthly website maintenance, retainer agreements, and recurring subscriptions with pre-send vendor review alerts.
            </p>
        </div>

        <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 transition">
            <i class="ph ph-plus-circle text-base"></i>
            Create Recurring Profile
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Profiles Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base">Active Recurring Billing Profiles</h3>
            <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full">
                {{ $recurring->total() }} Profiles Active
            </span>
        </div>

        @if($recurring->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                <i class="ph ph-repeat text-3xl mb-2 text-slate-300"></i>
                <p>No recurring invoice profiles created yet.</p>
                <p class="text-xs text-slate-400 mt-1">Set up monthly retainers (e.g. Website Maintenance on 1st of each month) using the button above.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Retainer Title</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Recurring Amount</th>
                            <th class="px-6 py-3">Schedule</th>
                            <th class="px-6 py-3">Pre-Send Vendor Check</th>
                            <th class="px-6 py-3">Next Run Date</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recurring as $r)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $r->title }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800">{{ $r->client->name }}</span>
                                    <span class="block text-xs text-slate-400">{{ $r->client->company_name }}</span>
                                </td>
                                <td class="px-6 py-4 font-black text-slate-900">
                                    {{ $r->formatted_amount }}
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                    {{ ucfirst($r->frequency) }} on the {{ $r->day_of_month }}th
                                </td>
                                <td class="px-6 py-4">
                                    @if($r->require_vendor_approval)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800" title="Vendor gets alert before sending to customer">
                                            <i class="ph ph-bell mr-1"></i> Review Required
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Direct Send</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-bold text-red-600 text-xs">
                                    {{ $r->next_run_date ? $r->next_run_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <form action="{{ route('recurring.approve', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 transition" title="Trigger and dispatch monthly invoice immediately">
                                            <i class="ph ph-paper-plane-tilt"></i> Approve & Send Now
                                        </button>
                                    </form>

                                    <form action="{{ route('recurring.toggle', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold">
                                            {{ $r->status === 'active' ? 'Pause' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $recurring->links() }}
            </div>
        @endif
    </div>

    <!-- Create Recurring Profile Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 text-left">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-red-50 text-red-600"><i class="ph ph-repeat text-lg"></i></span>
                    <h3 class="font-bold text-slate-900 text-base">New Recurring Retainer Profile</h3>
                </div>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('recurring.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Client *</label>
                    <select name="client_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="">-- Choose Client --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name ? "{$c->company_name} ({$c->name})" : $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Retainer Title / Service Description *</label>
                    <input type="text" name="title" required placeholder="e.g. Monthly Website Maintenance & Server Retainer"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Amount *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="500.00"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Currency *</label>
                        <select name="currency" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="AED">AED (د.إ)</option>
                            <option value="SAR">SAR (﷼)</option>
                            <option value="CAD">CAD (C$)</option>
                            <option value="PKR">PKR (Rs)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Billing Frequency</label>
                        <select name="frequency" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dispatch Day of Month</label>
                        <select name="day_of_month" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="1" selected>1st of Month</option>
                            <option value="5">5th of Month</option>
                            <option value="10">10th of Month</option>
                            <option value="15">15th of Month</option>
                            <option value="25">25th of Month</option>
                            <option value="28">28th of Month</option>
                        </select>
                    </div>
                </div>

                <!-- Vendor Review Checkbox (Item 18 Requirement) -->
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 space-y-2">
                    <label class="flex items-start gap-2 cursor-pointer text-xs text-slate-800 font-bold">
                        <input type="checkbox" name="require_vendor_approval" value="1" checked class="w-4 h-4 text-amber-600 rounded mt-0.5">
                        <div>
                            <span>Ask vendor to verify if additional charges are needed before sending</span>
                            <p class="text-[11px] font-normal text-slate-600 mt-0.5">
                                On the 1st of each month, an alert prompts the vendor to check for extra work before the invoice is finalized and emailed to the customer.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow">Create Recurring Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
