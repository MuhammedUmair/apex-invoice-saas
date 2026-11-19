<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Draft Invoice Access Restricted') }} - Invoicy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto text-3xl">
            <i class="ph ph-lock-key"></i>
        </div>
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 mb-2">
                {{ __('Draft Document') }}
            </span>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Document Not Available Yet') }}</h1>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                {{ __('Invoice') }} <span class="font-bold text-slate-800">#{{ $invoice->invoice_number }}</span> {{ __('is currently an unissued draft and is not published for public customer viewing or online settlement. Once the vendor officially publishes this invoice, you will be able to access the secure payment portal.') }}
            </p>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs text-slate-600 space-y-1.5">
            <div class="flex justify-between">
                <span class="text-slate-400">{{ __('Issuing Agency') }}:</span>
                <span class="font-bold text-slate-800">{{ $invoice->workspace->name ?? 'Invoicy Agency' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">{{ __('Invoice Total') }}:</span>
                <span class="font-bold text-slate-800">{{ number_format($invoice->total_cents / 100, 2) }} {{ $invoice->currency }}</span>
            </div>
        </div>
        <p class="text-[11px] text-slate-400">
            {{ __('If you believe this is an error, please contact the issuing business to release the published invoice.') }}
        </p>
    </div>
</body>
</html>
