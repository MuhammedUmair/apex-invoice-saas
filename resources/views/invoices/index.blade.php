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

    <!-- Bulk Actions Form & Table -->
    <form id="bulk-invoices-form" method="POST" action="{{ route('invoices.bulk') }}"
          x-data="{
              selected: [],
              allIds: {{ json_encode($invoices->pluck('id')) }},
              toggleAll() {
                  if (this.selected.length === this.allIds.length) {
                      this.selected = [];
                  } else {
                      this.selected = [...this.allIds];
                  }
              },
              submitBulk(action, confirmMsg) {
                  if (this.selected.length === 0) return;
                  if (confirmMsg && !confirm(confirmMsg)) return;
                  $refs.bulkActionInput.value = action;
                  $el.submit();
              }
          }">
        @csrf
        <input type="hidden" name="action" x-ref="bulkActionInput" value="">

        <!-- Bulk Action Floating Toolbar (Shows when 1+ selected) -->
        <div x-show="selected.length > 0" x-cloak class="mb-4 p-3.5 bg-slate-900 text-white rounded-2xl shadow-lg flex flex-wrap items-center justify-between gap-3 border border-slate-800 transition">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 text-white text-xs font-bold" x-text="selected.length"></span>
                <span class="text-xs font-semibold">Invoice(s) Selected</span>
                <button type="button" @click="selected = []" class="text-[11px] text-slate-400 hover:text-white underline ml-1">
                    Clear selection
                </button>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="submitBulk('mark_paid', 'Mark ' + selected.length + ' selected invoice(s) as Paid?')" 
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-check-circle text-sm"></i> Mark Paid
                </button>
                <button type="button" @click="submitBulk('mark_issued', 'Mark ' + selected.length + ' selected invoice(s) as Issued?')" 
                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-file-text text-sm"></i> Mark Issued
                </button>
                <button type="button" @click="submitBulk('send_emails', 'Dispatch email to clients for ' + selected.length + ' selected invoice(s)?')" 
                        class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-paper-plane-tilt text-sm"></i> Send Emails
                </button>
                <button type="button" @click="submitBulk('delete', 'Permanently delete ' + selected.length + ' selected invoice(s)? This action cannot be undone.')" 
                        class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="ph ph-trash text-sm"></i> Delete
                </button>
            </div>
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
                            <th class="px-4 py-3.5 w-10 text-center">
                                <input type="checkbox" @click="toggleAll()" :checked="selected.length === allIds.length && allIds.length > 0" class="rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer">
                            </th>
                            <th class="px-4 py-3.5 font-bold">Invoice #</th>
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
                            <tr class="hover:bg-slate-50/60 transition" :class="selected.includes('{{ $inv->id }}') ? 'bg-red-50/30' : ''">
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" name="invoice_ids[]" value="{{ $inv->id }}" x-model="selected" class="rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4">
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
                                    @elseif($inv->status === 'sent') bg-sky-100 text-sky-800
                                    @elseif($inv->status === 'credited') bg-purple-100 text-purple-800
                                    @elseif($inv->status === 'overdue') bg-rose-100 text-rose-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $inv->status }}
                                </span>

                                <div class="mt-1 space-y-0.5">
                                    @if($inv->sent_at)
                                        <span class="block text-[10px] text-sky-700 font-medium" title="Dispatched on {{ $inv->sent_at->format('M d, Y h:i A') }}">
                                            <i class="ph ph-paper-plane-tilt"></i> Emailed {{ $inv->sent_at->format('M d') }}
                                        </span>
                                    @elseif($inv->status === 'issued')
                                        <span class="block text-[10px] text-amber-700 font-semibold" title="Invoice finalized, but not yet emailed to client">
                                            <i class="ph ph-envelope-simple"></i> Not Emailed
                                        </span>
                                    @endif

                                    @if($inv->email_opened_at)
                                        <span class="block text-[10px] text-emerald-700 font-semibold" title="Email opened on {{ $inv->email_opened_at->format('M d, Y h:i A') }}">
                                            <i class="ph ph-envelope-open"></i> Opened ({{ $inv->email_opens_count }}x)
                                        </span>
                                    @endif

                                    @if($inv->viewed_at)
                                        <span class="block text-[10px] text-indigo-700 font-semibold" title="Portal or PDF viewed on {{ $inv->viewed_at->format('M d, Y h:i A') }}">
                                            <i class="ph ph-eye"></i> Viewed ({{ $inv->view_count }}x)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($inv->client && !empty($inv->client->email))
                                        <form action="{{ route('invoices.send', $inv->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('{{ $inv->sent_at ? "This invoice was already emailed to {$inv->client->email} on " . $inv->sent_at->format('M d, Y h:i A') . ". Do you want to send it again?" : "Send invoice #{$inv->invoice_number} to {$inv->client->email}?" }}');">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 {{ $inv->sent_at ? 'text-sky-600' : 'text-emerald-600' }}" title="{{ $inv->sent_at ? 'Resend Invoice Email (Already sent ' . $inv->sent_at->format('M d') . ')' : 'Send Invoice to Customer Email' }}">
                                                <i class="ph {{ $inv->sent_at ? 'ph-arrows-clockwise' : 'ph-paper-plane-tilt' }} text-base"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-700" title="Revise / Edit Invoice">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </a>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-600" title="View Details">
                                        <i class="ph ph-eye text-base"></i>
                                    </a>
                                    <a href="{{ route('invoices.portal', $inv->public_secure_token) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600" title="Customer Portal Link">
                                        <i class="ph ph-arrow-square-out text-base"></i>
                                    </a>
                                    <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Permanently delete invoice #{{ $inv->invoice_number }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-600" title="Delete Invoice">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
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
    </form>
</div>
@endsection
