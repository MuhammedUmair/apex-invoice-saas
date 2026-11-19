<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Unverified Draft Document') }} - Invoice #{{ $invoice->invoice_number }} | Invoicy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto text-3xl">
            <i class="ph ph-shield-warning"></i>
        </div>
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 mb-2">
                {{ __('Draft • Not Validated') }}
            </span>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Cryptographic Verification Locked') }}</h1>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                {{ __('Invoice') }} <span class="font-bold text-slate-800">#{{ $invoice->invoice_number }}</span> {{ __('is currently saved as an internal draft. Official cryptographic verification seals and QR authenticity validation are only generated after the invoice has been published.') }}
            </p>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs text-slate-600 space-y-1.5">
            <div class="flex justify-between">
                <span class="text-slate-400">{{ __('Issuing Business') }}:</span>
                <span class="font-bold text-slate-800">{{ $invoice->workspace->name ?? 'Invoicy Agency' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">{{ __('Current Status') }}:</span>
                <span class="font-bold text-amber-700 uppercase">{{ __('Draft (Unpublished)') }}</span>
            </div>
        </div>
        <p class="text-[11px] text-slate-400">
            {{ __('Invoicy Trust Shield authenticity verification is reserved strictly for published commercial documents.') }}
        </p>
    </div>
</body>
</html>
