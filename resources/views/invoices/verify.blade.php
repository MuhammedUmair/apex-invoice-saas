@php
    $sysLogo = \App\Models\CmsContent::getSystemLogo();
    $workspace = $invoice->workspace;
    $client = $invoice->client;
    $isPaid = $invoice->isPaid();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Authenticity Verification - Invoice #{{ $invoice->invoice_number }} | Invoicy by RedMaple</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900 flex flex-col justify-between antialiased">

    <!-- Top Header -->
    <header class="bg-white border-b border-slate-200 py-3 sm:py-4 px-4 sm:px-6 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 sm:gap-4">
            <div class="flex items-center gap-2.5 sm:gap-3">
                @if($sysLogo)
                    <img src="{{ $sysLogo }}" alt="Logo" class="h-7 sm:h-8 max-w-[120px] sm:max-w-[140px] object-contain">
                @else
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-red-600 flex items-center justify-center text-white font-black text-sm shadow-sm shrink-0">
                        <i class="ph ph-shield-check text-lg sm:text-xl"></i>
                    </div>
                @endif
                <div>
                    <div class="text-sm font-black tracking-tight text-slate-900 flex items-center gap-1.5">
                        <span>Invoicy</span>
                        <span class="text-[10px] uppercase tracking-wider font-extrabold text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded">Trust Shield</span>
                    </div>
                    <div class="text-[10px] sm:text-[11px] text-slate-500 font-medium">Digital Document Authenticity Verification</div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Cryptographically Validated
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto w-full px-4 py-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">

            <!-- Hero Banner -->
            @if($isPaid)
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-8 text-center relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md mb-4 text-white shadow-inner">
                        <i class="ph ph-seal-check text-4xl"></i>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight mb-1">Authentic Invoice — Paid in Full</h1>
                    <p class="text-emerald-100 text-sm max-w-md mx-auto">This commercial invoice was officially issued and fully settled through Invoicy by RedMaple secure billing infrastructure.</p>
                </div>
            @elseif($invoice->status === 'issued')
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white p-8 text-center relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-40 h-40 bg-red-500/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-600/30 border border-red-400/30 backdrop-blur-md mb-4 text-white shadow-inner">
                        <i class="ph ph-shield-check text-4xl text-red-400"></i>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight mb-1">Verified Authentic Commercial Invoice</h1>
                    <p class="text-slate-300 text-sm max-w-md mx-auto">This document is verified as an official, uncompromised commercial invoice published by the verified agency below.</p>
                </div>
            @else
                <div class="bg-gradient-to-r from-amber-600 to-orange-700 text-white p-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md mb-4 text-white shadow-inner">
                        <i class="ph ph-warning-circle text-4xl"></i>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight mb-1">Invoice Status: {{ ucfirst($invoice->status) }}</h1>
                    <p class="text-amber-100 text-sm max-w-md mx-auto">This invoice is registered in Invoicy with status '{{ ucfirst($invoice->status) }}'.</p>
                </div>
            @endif

            <!-- Body Details -->
            <div class="p-6 sm:p-8 space-y-6">

                <!-- Financial Highlight Box -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider font-extrabold text-slate-500 mb-1">Total Verified Amount</div>
                        <div class="text-3xl font-black text-slate-900 flex items-baseline gap-2">
                            <span>{{ number_format($invoice->total_cents / 100, 2) }}</span>
                            <span class="text-sm font-bold text-slate-600">{{ $invoice->currency }}</span>
                        </div>
                        @if(!$isPaid && $invoice->due_cents > 0)
                            <div class="text-xs text-red-600 font-bold mt-1">Outstanding Balance Due: {{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="text-right sm:border-r sm:border-slate-200 sm:pr-4">
                            <div class="text-[11px] font-bold text-slate-400 uppercase">Invoice Number</div>
                            <div class="text-base font-black text-slate-900 font-mono">#{{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="sm:pl-2">
                            <span class="inline-block px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider {{ $isPaid ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300' }}">
                                {{ $invoice->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Issuer & Client Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-white">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="ph ph-buildings text-sm text-slate-500"></i>
                            Official Issuer (Agency)
                        </div>
                        <div class="font-bold text-slate-900 text-sm">{{ $invoice->workspace_snapshot['name'] ?? $workspace?->name ?? 'Verified Agency' }}</div>
                        @if(!empty($invoice->workspace_snapshot['tax_id']) || !empty($workspace?->tax_id))
                            <div class="text-xs text-slate-500 mt-0.5">Tax / VAT ID: {{ $invoice->workspace_snapshot['tax_id'] ?? $workspace?->tax_id }}</div>
                        @endif
                        @if(!empty($invoice->workspace_snapshot['email']) || !empty($workspace?->email))
                            <div class="text-xs text-slate-500">{{ $invoice->workspace_snapshot['email'] ?? $workspace?->email }}</div>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200 bg-white">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="ph ph-user text-sm text-slate-500"></i>
                            Billed Recipient (Client)
                        </div>
                        <div class="font-bold text-slate-900 text-sm">{{ $invoice->client_snapshot['company_name'] ?? $client?->company_name ?? $invoice->client_snapshot['name'] ?? $client?->name ?? 'Commercial Client' }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Attn: {{ $invoice->client_snapshot['name'] ?? $client?->name ?? 'Authorized Contact' }}</div>
                        @if(!empty($invoice->client_snapshot['email']) || !empty($client?->email))
                            <div class="text-xs text-slate-500">{{ $invoice->client_snapshot['email'] ?? $client?->email }}</div>
                        @endif
                    </div>
                </div>

                <!-- Date Breakdown -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block mb-0.5 font-medium">Issue Date</span>
                        <span class="font-bold text-slate-900">{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block mb-0.5 font-medium">Due Date</span>
                        <span class="font-bold text-slate-900">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 col-span-2 sm:col-span-1">
                        <span class="text-slate-500 block mb-0.5 font-medium">Verification Timestamp</span>
                        <span class="font-bold text-slate-900 font-mono">{{ now()->format('M d, Y H:i:s') }}</span>
                    </div>
                </div>

                <!-- Cryptographic Fingerprint Verification Box -->
                <div class="p-4 rounded-xl border border-red-200 bg-red-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="ph ph-fingerprint text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-red-950">Cryptographic Seal Fingerprint</div>
                            <div class="text-xs font-mono font-bold text-red-700 tracking-wider select-all">{{ $invoice->cryptographic_fingerprint }}</div>
                            <div class="text-[11px] text-slate-500">Tamper-evident verification hash computed over invoice record.</div>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-lg">
                            <i class="ph ph-check-circle"></i> Uncompromised
                        </span>
                    </div>
                </div>

                <!-- Action CTAs -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center gap-3">
                    @if($invoice->status !== 'draft')
                        <a href="{{ route('invoices.portal', $invoice->public_secure_token) }}" class="w-full sm:w-1/2 py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs text-center shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-arrow-square-out text-base"></i>
                            View Full Online Invoice
                        </a>
                        <a href="{{ route('invoices.pdf.download', $invoice->public_secure_token) }}" class="w-full sm:w-1/2 py-3 px-4 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs text-center shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-file-pdf text-base text-red-400"></i>
                            Download Official PDF
                        </a>
                    @else
                        <div class="w-full text-center text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3">
                            <i class="ph ph-lock-key"></i> Customer portal & PDF downloads are locked while this invoice is in draft mode.
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-500 border-t border-slate-200 bg-white">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 900 900" fill="#dc2626">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
                <span>Powered by <strong>Invoicy by RedMaple</strong> &bull; Commercial Enterprise Trust</span>
            </div>
            <div>
                &copy; {{ date('Y') }} Red Maple Agency Inc. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
