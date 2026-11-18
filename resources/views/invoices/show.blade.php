@extends('layouts.app')

@php
    $currentLocale = app()->getLocale();
    $isRtl = in_array($currentLocale, ['ur', 'ar']);
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showCreditModal: false, showRecallModal: false, openActions: false }">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-black text-slate-900">{{ $invoice->invoice_number }}</h1>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800
                    @elseif($invoice->status === 'partially_paid') bg-amber-100 text-amber-800
                    @elseif($invoice->status === 'issued') bg-blue-100 text-blue-800
                    @elseif($invoice->status === 'sent') bg-sky-100 text-sky-800
                    @elseif($invoice->status === 'cancelled') bg-rose-100 text-rose-800
                    @else bg-slate-100 text-slate-800 @endif">
                    {{ __($invoice->status) }}
                </span>

                @if($invoice->sent_at)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200" title="Dispatched on {{ $invoice->sent_at->format('M d, Y h:i A') }}">
                        <i class="ph ph-envelope-simple-check text-xs text-sky-600"></i> {{ __('Emailed') }} {{ $invoice->sent_at->format('M d') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200" title="This invoice has not yet been emailed to the client.">
                        <i class="ph ph-envelope-simple text-xs"></i> {{ __('Not Emailed') }}
                    </span>
                @endif

                @if($invoice->email_opened_at)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200" title="First opened on {{ $invoice->email_opened_at->format('M d, Y h:i A') }}">
                        <i class="ph ph-envelope-open text-xs text-emerald-600"></i> {{ __('Email Opened') }} ({{ $invoice->email_opens_count }}x)
                    </span>
                @endif

                @if($invoice->viewed_at)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200" title="Client visited portal / downloaded PDF on {{ $invoice->viewed_at->format('M d, Y h:i A') }}">
                        <i class="ph ph-eye text-xs text-indigo-600"></i> {{ __('Viewed by Client') }} ({{ $invoice->view_count }}x)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ __('Issued') }} {{ $invoice->issue_date->format('F d, Y') }} &bull; {{ __('Due') }} {{ $invoice->due_date->format('F d, Y') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap" x-data="{ openActions: false }">
            <!-- Language Switcher Form for Invoice -->
            <form action="{{ route('invoices.language', $invoice->id) }}" method="POST" class="inline-flex items-center">
                @csrf
                <div class="flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 transition" title="{{ __('Change Invoice Document Language') }}">
                    <i class="ph ph-translate text-sm text-red-600"></i>
                    <select name="language" onchange="this.form.submit()" class="bg-transparent border-0 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer pr-3 py-0">
                        <option value="en" {{ ($invoice->language ?: $currentLocale) === 'en' ? 'selected' : '' }}>🇺🇸 EN</option>
                        <option value="ur" {{ ($invoice->language ?: $currentLocale) === 'ur' ? 'selected' : '' }}>🇵🇰 اردو</option>
                        <option value="ar" {{ ($invoice->language ?: $currentLocale) === 'ar' ? 'selected' : '' }}>🇦🇪 العربية</option>
                        <option value="tr" {{ ($invoice->language ?: $currentLocale) === 'tr' ? 'selected' : '' }}>🇹🇷 TR</option>
                        <option value="hi" {{ ($invoice->language ?: $currentLocale) === 'hi' ? 'selected' : '' }}>🇮🇳 HI</option>
                        <option value="es" {{ ($invoice->language ?: $currentLocale) === 'es' ? 'selected' : '' }}>🇪🇸 ES</option>
                        <option value="fr" {{ ($invoice->language ?: $currentLocale) === 'fr' ? 'selected' : '' }}>🇫🇷 FR</option>
                        <option value="de" {{ ($invoice->language ?: $currentLocale) === 'de' ? 'selected' : '' }}>🇩🇪 DE</option>
                    </select>
                </div>
            </form>

            <a href="{{ route('invoices.edit', $invoice->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-300 shadow-xs transition">
                <i class="ph ph-pencil-simple text-base text-slate-500"></i> {{ __('Revise') }}
            </a>

            <a href="{{ route('invoices.pdf.download', ['token' => $invoice->public_secure_token, 'lang' => $invoice->language ?: $currentLocale]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                <i class="ph ph-file-pdf text-base text-slate-300"></i> {{ __('PDF') }}
            </a>

            <a href="{{ route('invoices.portal', ['token' => $invoice->public_secure_token, 'lang' => $invoice->language ?: $currentLocale]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-arrow-square-out text-base"></i> {{ __('Pay Portal') }}
            </a>

            @if($invoice->status !== 'draft' && $invoice->status !== 'paid')
                <button @click="showRecallModal = true" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold rounded-xl transition shadow-xs"
                        title="{{ __('Recall invoice and revert to draft for corrections') }}">
                    <i class="ph ph-arrow-counter-clockwise text-base text-amber-600"></i> {{ __('Recall / Pull Back') }}
                </button>
            @endif

            <!-- More Actions Dropdown Menu -->
            <div class="relative">
                <button @click="openActions = !openActions" @click.outside="openActions = false" type="button"
                        class="inline-flex items-center gap-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 transition">
                    <span>{{ __('More') }}</span>
                    <i class="ph ph-caret-down text-xs"></i>
                </button>

                <div x-show="openActions" x-cloak 
                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                    
                    @if($invoice->client && !empty($invoice->client->email))
                        <form action="{{ route('invoices.send', $invoice->id) }}" method="POST" class="w-full"
                              onsubmit="return confirm('{{ $invoice->sent_at ? "This invoice was already emailed to {$invoice->client->email} on " . $invoice->sent_at->format('M d, Y h:i A') . ". Do you want to send it again?" : "Send invoice #{$invoice->invoice_number} to {$invoice->client->email}?" }}');">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center justify-between text-slate-700 font-semibold">
                                <span class="flex items-center gap-2">
                                    <i class="ph ph-paper-plane-tilt {{ $invoice->sent_at ? 'text-blue-600' : 'text-emerald-600' }} text-sm"></i>
                                    <span>{{ $invoice->sent_at ? __('Resend to Client Email') : __('Send to Client Email') }}</span>
                                </span>
                                @if($invoice->sent_at)
                                    <span class="text-[10px] text-slate-400 font-normal">{{ __('Sent') }}</span>
                                @endif
                            </button>
                        </form>
                    @endif

                    @if($invoice->due_cents > 0)
                        <form action="{{ route('invoices.reminder', $invoice->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-700 font-semibold">
                                <i class="ph ph-bell-ringing text-amber-600 text-sm"></i>
                                {{ __('Send Payment Reminder') }}
                            </button>
                        </form>
                    @endif

                    <button @click="showCreditModal = true; openActions = false" type="button"
                            class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-rose-700 font-semibold border-t border-slate-100">
                        <i class="ph ph-arrow-u-up-left text-rose-600 text-sm"></i>
                        {{ __('Issue Credit Note') }}
                    </button>

                    @if($invoice->status !== 'draft' && $invoice->status !== 'paid')
                        <button @click="showRecallModal = true; openActions = false" type="button"
                                class="w-full text-left px-4 py-2 hover:bg-amber-50 flex items-center gap-2 text-amber-700 font-semibold border-t border-slate-100">
                            <i class="ph ph-arrow-counter-clockwise text-amber-600 text-sm"></i>
                            {{ __('Recall / Pull Back Invoice') }}
                        </button>
                    @endif

                    <button onclick="navigator.clipboard.writeText('{{ route('invoices.portal', $invoice->public_secure_token) }}'); alert('Customer portal link copied!');"
                            @click="openActions = false" type="button"
                            class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-600 font-medium">
                        <i class="ph ph-copy text-slate-500 text-sm"></i>
                        {{ __('Copy Portal Link') }}
                    </button>

                    <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="w-full"
                          onsubmit="return confirm('Permanently delete invoice #{{ $invoice->invoice_number }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-rose-50 flex items-center gap-2 text-rose-600 font-semibold border-t border-slate-100">
                            <i class="ph ph-trash text-rose-600 text-sm"></i>
                            {{ __('Delete Invoice') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->status !== 'draft' && $invoice->items->isNotEmpty())
    <!-- Customer Link Share Banner -->
    <div class="bg-red-50/70 border border-red-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-xs text-red-900">
            <span class="font-bold flex items-center gap-1.5 mb-0.5">
                <svg class="w-3.5 h-3.5 text-red-600" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
                {{ __('Public Secure Customer URL') }}:
            </span>
            <span class="font-mono text-red-700 block truncate max-w-lg">{{ route('invoices.portal', $invoice->public_secure_token) }}</span>
        </div>
        <button onclick="navigator.clipboard.writeText('{{ route('invoices.portal', $invoice->public_secure_token) }}'); alert('Customer portal link copied to clipboard!');"
                class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg shadow-sm whitespace-nowrap">
            {{ __('Copy Link') }}
        </button>
    </div>
    @else
    <!-- Draft Notice Banner (Item 2) -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3 text-xs text-amber-900">
        <i class="ph ph-lock-key text-xl text-amber-600 shrink-0"></i>
        <div class="flex-1">
            <span class="font-bold block">{{ __('Public Portal & PDF Download Disabled') }}</span>
            <span>{{ __('This invoice is saved as a Draft. Public customer URLs and PDF downloads are locked until you issue and publish the invoice.') }}</span>
        </div>
        <a href="{{ route('invoices.edit', $invoice->id) }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shrink-0 transition">
            {{ __('Edit & Publish') }}
        </a>
    </div>
    @endif

    <!-- Authentic Verification & QR Code Card (Item 6 / Authentic Verification) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <div class="p-2 bg-slate-50 border border-slate-200 rounded-xl shrink-0 shadow-inner">
                <img src="{{ $invoice->qr_code_data_uri }}" alt="Verification QR Code" class="w-20 h-20 rounded-lg">
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-50 text-red-700 border border-red-200">
                        <i class="ph ph-shield-check text-xs"></i> {{ __('Official Verification QR Code') }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono font-bold">{{ $invoice->cryptographic_fingerprint }}</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900 mt-1">{{ __('Authentic Commercial Document Seal') }}</h4>
                <p class="text-xs text-slate-500 max-w-md">{{ __('Scan this QR code with any smartphone camera to verify this invoice\'s cryptographic authenticity and current payment status.') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
            <a href="{{ route('invoices.verify', $invoice->public_secure_token) }}" target="_blank"
               class="w-full sm:w-auto px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                <i class="ph ph-shield-check text-sm text-red-400"></i> {{ __('Open Verification Screen') }}
            </a>
        </div>
    </div>

    <!-- Automated Milestone Reminders Timeline Card -->
    @php
        $daysRemaining = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($invoice->due_date)->startOfDay(), false);
        $sentMilestones = $invoice->reminders->pluck('days_before')->toArray();
    @endphp
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <i class="ph ph-bell-ringing text-red-600 text-lg"></i>
                <h4 class="font-bold text-sm text-slate-900">{{ __('Payment Reminder Automation') }}</h4>
                <span class="text-xs text-slate-500">({{ __('Milestones') }}: 45, 30, 15, 7, 3, 2 {{ __('days before due') }})</span>
            </div>
            @if($invoice->due_cents > 0)
                <form action="{{ route('invoices.reminder', $invoice->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg border border-red-200 transition">
                        <i class="ph ph-paper-plane-tilt"></i> {{ __('Send Manual Reminder Now') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap text-xs pt-1">
            <span class="text-slate-500 font-medium">{{ __('Schedule Progress') }}:</span>
            @foreach([45, 30, 15, 7, 3, 2] as $m)
                @php
                    $isSent = in_array($m, $sentMilestones, true);
                    $isPast = $daysRemaining < $m;
                @endphp
                <span class="px-2.5 py-1 rounded-lg font-bold border {{ $isSent ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : ($isPast ? 'bg-slate-100 text-slate-400 border-slate-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                    {{ $m }} {{ __('Days Before') }} {{ $isSent ? '✓ ' . __('Sent') : ($isPast ? '(' . __('Passed') . ')' : '') }}
                </span>
            @endforeach
        </div>

        @if($invoice->reminders->isNotEmpty())
            <div class="text-xs text-slate-500 pt-1">
                {{ __('Latest dispatch') }}: {{ $invoice->reminders->first()->created_at->format('M d, Y H:i') }} {{ __('to') }} {{ $invoice->reminders->first()->recipient_customer_email }}
            </div>
        @endif
    </div>

    <!-- Invoice Content Paper -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-8 relative overflow-hidden" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        @if($invoice->isTrialInvoice())
            <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden z-20">
                <div class="transform -rotate-25 border-4 border-dashed border-red-500/30 px-10 py-4 text-center font-black tracking-widest text-red-500/25 text-2xl sm:text-4xl uppercase whitespace-nowrap">
                    {{ __('Trial Version • Not For Official Accounting') }}
                </div>
            </div>
        @endif
        <!-- Invoice Parties -->
        <div class="grid grid-cols-2 gap-8 border-b border-slate-100 pb-8">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('From') }}</p>
                <h3 class="font-bold text-slate-900 text-base">{{ $invoice->workspace_snapshot['name'] ?? auth()->user()->workspace->name }}</h3>
                <p class="text-xs text-slate-500 whitespace-pre-line mt-1">{{ $invoice->workspace_snapshot['address'] ?? auth()->user()->workspace->address }}</p>
                @if(!empty($invoice->workspace_snapshot['tax_id']))
                    <p class="text-xs text-slate-500 mt-1">{{ __('Tax ID') }}: <span class="font-mono">{{ $invoice->workspace_snapshot['tax_id'] }}</span></p>
                @endif
                @if(!empty($invoice->workspace_snapshot['email']))
                    <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->workspace_snapshot['email'] }}</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Billed To') }}</p>
                <h3 class="font-bold text-slate-900 text-base">{{ $invoice->client_snapshot['company_name'] ?? $invoice->client_snapshot['name'] ?? __('Walk-in / General Customer') }}</h3>
                @if(!empty($invoice->client_snapshot['company_name']))
                    <p class="text-xs text-slate-600 font-medium">{{ $invoice->client_snapshot['name'] }}</p>
                @endif
                <p class="text-xs text-slate-500 whitespace-pre-line mt-1">{{ $invoice->client_snapshot['address'] ?? __('Address on file') }}</p>
                @if(!empty($invoice->client_snapshot['tax_id']))
                    <p class="text-xs text-slate-500 mt-1">{{ __('VAT/Tax ID') }}: <span class="font-mono">{{ $invoice->client_snapshot['tax_id'] }}</span></p>
                @endif
                <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->client_snapshot['email'] ?? '' }}</p>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm {{ $isRtl ? 'text-right' : 'text-left' }}">
                <thead class="border-b border-slate-200 text-slate-400 text-xs uppercase font-bold">
                    <tr>
                        <th class="py-3">{{ __('Description') }}</th>
                        <th class="py-3 text-center">{{ __('Qty') }}</th>
                        <th class="py-3 {{ $isRtl ? 'text-left' : 'text-right' }}">{{ __('Unit Rate') }}</th>
                        <th class="py-3 {{ $isRtl ? 'text-left' : 'text-right' }}">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-4">
                                <div class="font-bold text-slate-800">{{ $item->title ?? $item->description }}</div>
                                @if($item->description && $item->title && $item->description !== $item->title)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-4 text-center font-medium text-slate-600">
                                {{ (float)$item->quantity == (int)$item->quantity ? (int)$item->quantity : $item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="py-4 {{ $isRtl ? 'text-left' : 'text-right' }} font-medium text-slate-600">
                                {{ number_format($item->rate_cents / 100, 2) }} {{ $invoice->currency }}
                            </td>
                            <td class="py-4 {{ $isRtl ? 'text-left' : 'text-right' }} font-bold text-slate-900">
                                {{ number_format($item->amount_cents / 100, 2) }} {{ $invoice->currency }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Totals Summary -->
        <div class="flex {{ $isRtl ? 'justify-start' : 'justify-end' }} pt-4 border-t border-slate-100">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>{{ __('Subtotal') }}:</span>
                    <span class="font-semibold text-slate-900">{{ number_format($invoice->subtotal_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                @if($invoice->discount_cents > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>{{ __('Discount') }}:</span>
                        <span class="font-semibold">-{{ number_format($invoice->discount_cents / 100, 2) }} {{ $invoice->currency }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>{{ $invoice->tax_name }} ({{ number_format($invoice->tax_rate, 2) }}%):</span>
                    <span class="font-semibold text-slate-900">+{{ number_format($invoice->tax_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-black text-slate-900">
                    <span>{{ __('Total Amount') }}:</span>
                    <span class="text-red-600">{{ number_format($invoice->total_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500 pt-1">
                    <span>{{ __('Amount Paid') }}:</span>
                    <span>{{ number_format($invoice->paid_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-rose-600">
                    <span>{{ __('Balance Due') }}:</span>
                    <span>{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                @if($invoice->getLateSurchargeCents() > 0)
                    <div class="flex justify-between text-xs font-bold text-rose-700 bg-rose-50 px-2 py-1 rounded">
                        <span>{{ __('Late Surcharge (Overdue)') }}:</span>
                        <span>+{{ number_format($invoice->getLateSurchargeCents() / 100, 2) }} {{ $invoice->currency }}</span>
                    </div>
                    <div class="flex justify-between border-t border-rose-200 pt-1 text-sm font-black text-rose-700">
                        <span>{{ __('Adjusted Total Due') }}:</span>
                        <span>{{ number_format(($invoice->due_cents + $invoice->getLateSurchargeCents()) / 100, 2) }} {{ $invoice->currency }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($invoice->notes)
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-600">
                <p class="font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Notes') }}</p>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif

        @if($invoice->terms)
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-600">
                <p class="font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Terms & Conditions') }}</p>
                <p class="whitespace-pre-line">{{ $invoice->terms }}</p>
            </div>
        @endif
    </div>

    <!-- Credit Notes & Ledger Adjustments Card -->
    @if($invoice->creditNotes->isNotEmpty())
        <div class="bg-rose-50/50 rounded-2xl border border-rose-200 p-6 shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
                <i class="ph ph-arrow-u-up-left text-lg text-rose-600"></i>
                {{ __('Issued Credit Notes & Reversals') }}
            </div>
            <div class="divide-y divide-rose-200/60 text-xs">
                @foreach($invoice->creditNotes as $cn)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-rose-700">{{ $cn->credit_note_number }}</span>
                            <span class="text-slate-500 ml-2">&bull; {{ $cn->reason }}</span>
                            <span class="text-slate-400 ml-2">({{ $cn->created_at->format('M d, Y') }})</span>
                        </div>
                        <div class="font-mono font-bold text-rose-700">
                            -{{ $cn->formatted_amount }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Issue Credit Note Modal -->
    <div x-show="showCreditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">{{ __('Issue Credit Note') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('For invoice') }} {{ $invoice->invoice_number }}</p>
                </div>
                <button @click="showCreditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('credit-notes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Credited Amount') }} ({{ $invoice->currency }}) *</label>
                    <input type="number" step="0.01" max="{{ $invoice->total_cents / 100 }}" name="amount"
                           value="{{ number_format($invoice->due_cents > 0 ? $invoice->due_cents / 100 : $invoice->total_cents / 100, 2, '.', '') }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                    <p class="text-[10px] text-slate-400 mt-1">{{ __('Maximum creditable') }}: {{ $invoice->formatted_total }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Reason for Credit Note') }} *</label>
                    <select name="reason" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="Billing error or incorrect amount">{{ __('Billing error or incorrect amount') }}</option>
                        <option value="Customer refund requested">{{ __('Customer refund requested') }}</option>
                        <option value="Project cancelled / unfulfilled service">{{ __('Project cancelled / unfulfilled service') }}</option>
                        <option value="Dispute resolution">{{ __('Dispute resolution') }}</option>
                        <option value="Discount applied after issuance">{{ __('Discount applied after issuance') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Audit Notes / Comments') }}</label>
                    <textarea name="notes" rows="2" placeholder="{{ __('Explanation for accounting audit log...') }}"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreditModal = false"
                            class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-sm">
                        {{ __('Confirm & Issue Credit Note') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recall Invoice Modal (Item 4) -->
    <div x-show="showRecallModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="ph ph-arrow-counter-clockwise text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">{{ __('Recall / Pull Back Invoice') }}</h3>
                        <p class="text-xs text-slate-500">Invoice #{{ $invoice->invoice_number }}</p>
                    </div>
                </div>
                <button @click="showRecallModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.recall', $invoice->id) }}" class="space-y-4">
                @csrf
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 leading-relaxed">
                    <strong>{{ __('What happens when you recall:') }}</strong><br>
                    &bull; {{ __('The current public online link and PDF download token are immediately revoked.') }}<br>
                    &bull; {{ __('The invoice status is reverted back to') }} <strong>{{ __('Draft') }}</strong> {{ __('mode so you can correct items and pricing.') }}<br>
                    &bull; {{ __('If selected below, an automated correction notice will be emailed to your client.') }}
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Reason for Recall') }} *</label>
                    <select name="recall_reason" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="Correction needed on line items or pricing">{{ __('Correction needed on line items or pricing') }}</option>
                        <option value="Invoice dispatched with incorrect customer details">{{ __('Invoice dispatched with incorrect customer details') }}</option>
                        <option value="Invoice issued in error or duplicated">{{ __('Invoice issued in error or duplicated') }}</option>
                        <option value="Client requested changes prior to payment">{{ __('Client requested changes prior to payment') }}</option>
                    </select>
                </div>

                @if($invoice->client && !empty($invoice->client->email))
                <div class="pt-2">
                    <label class="flex items-start gap-2 cursor-pointer text-xs text-slate-700">
                        <input type="checkbox" name="notify_client" value="1" checked class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                        <span>{{ __('Send correction notice email to client') }} (<strong>{{ $invoice->client->email }}</strong>) {{ __('informing them the invoice has been retracted for updates.') }}</span>
                    </label>
                </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showRecallModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-lg shadow-sm">
                        {{ __('Confirm Recall & Revert to Draft') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
