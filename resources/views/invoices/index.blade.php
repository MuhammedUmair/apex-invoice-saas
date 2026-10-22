@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Invoices</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage billing documents, view payment statuses, and track receivables</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-plus-circle text-base"></i> New Invoice
            </a>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs font-semibold overflow-x-auto">
        <a href="{{ route('invoices.index') }}" class="px-3 py-1.5 rounded-lg transition {{ empty($currentStatus) ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-200' }}">All</a>
        <a href="{{ route('invoices.index', ['status' => 'issued']) }}" class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'issued' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-200' }}">Issued</a>
        <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'paid' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-200' }}">Paid</a>
        <a href="{{ route('invoices.index', ['status' => 'partially_paid']) }}" class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'partially_paid' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-200' }}">Partially Paid</a>
        <a href="{{ route('invoices.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'draft' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-200' }}">Drafts</a>
    </div>

    <!-- Invoice Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($invoices->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="ph ph-receipt text-4xl mb-2 inline-block"></i>
                <p class="text-sm font-medium">No invoices found for this criteria.</p>
                <div class="mt-4">
                    <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg">Create First Invoice</a>
                </div>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">Invoice #</th>
                        <th class="px-4 py-3.5 font-bold">Client / Recipient</th>
                        <th class="px-4 py-3.5 font-bold">Dates</th>
                        <th class="px-4 py-3.5 font-bold text-right">Total Amount</th>
                        <th class="px-4 py-3.5 font-bold text-center">Security</th>
                        <th class="px-4 py-3.5 font-bold text-center">Status</th>
                        <th class="px-6 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="font-bold text-red-600 hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-800">{{ $inv->client->company_name ?? $inv->client->name }}</div>
                                <div class="text-xs text-slate-400">{{ $inv->client->email }}</div>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                <div>Issued: {{ $inv->issue_date->format('M d, Y') }}</div>
                                <div class="{{ $inv->isOverdue() ? 'text-rose-600 font-bold' : '' }}">Due: {{ $inv->due_date->format('M d, Y') }}</div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="font-bold text-slate-900">{{ $inv->formatted_total }}</div>
                                @if($inv->due_cents > 0 && $inv->paid_cents > 0)
                                    <div class="text-[11px] text-amber-600 font-medium">Due: {{ $inv->formatted_due }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($inv->is_pdf_password_protected)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200" title="Password Protected PDF">
                                        <i class="ph ph-lock-key"></i> Encrypted
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($inv->status === 'paid') bg-emerald-100 text-emerald-800
                                    @elseif($inv->status === 'partially_paid') bg-amber-100 text-amber-800
                                    @elseif($inv->status === 'issued') bg-blue-100 text-blue-800
                                    @elseif($inv->status === 'overdue') bg-rose-100 text-rose-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-600" title="View Details">
                                        <i class="ph ph-eye text-base"></i>
                                    </a>
                                    <a href="{{ route('invoices.portal', $inv->public_secure_token) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600" title="Customer Portal Link">
                                        <i class="ph ph-arrow-square-out text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
