@extends('layouts.guest')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center px-4">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-200 text-center">
        <!-- RedMaple Brand -->
        <div class="flex items-center justify-center gap-1.5 mb-5">
            <div class="w-6 h-6 rounded-md bg-red-50 border border-red-200 flex items-center justify-center p-0.5 shadow-sm">
                <svg class="w-full h-full text-red-600" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            </div>
            <span class="font-black text-slate-800 text-xs tracking-tight">Red<span class="text-red-600">Maple</span> Security Protection</span>
        </div>

        <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm border border-amber-200">
            <i class="ph ph-lock-key"></i>
        </div>

        <h2 class="text-xl font-black text-slate-900 tracking-tight">Protected Invoice Document</h2>
        <p class="text-xs text-slate-500 mt-1 mb-6">
            This invoice (<span class="font-bold text-slate-700">{{ $invoice->invoice_number }}</span>) is password-protected by the issuer for privacy and security.
        </p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('invoices.pdf.download', $invoice->public_secure_token) }}" class="space-y-4">
            <div>
                <input type="password" name="password" required autofocus placeholder="Enter document password..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none text-center">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <i class="ph ph-lock-open text-base"></i>
                Unlock & Download PDF
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100">
            <a href="{{ route('invoices.portal', $invoice->public_secure_token) }}" class="text-xs text-red-600 font-bold hover:underline">
                &larr; Return to Payment Portal
            </a>
        </div>
    </div>
</div>
@endsection
