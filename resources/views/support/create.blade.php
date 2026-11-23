@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('support.index') }}" class="hover:text-red-600 transition">{{ __('Support Tickets') }}</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">{{ __('New Ticket') }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Open Support Ticket') }}</h1>
        </div>
        <a href="{{ route('support.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
            {{ __('Back to Desk') }}
        </a>
    </div>

    <!-- 24-Hour SLA Guarantee Notice -->
    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-950 flex items-start gap-3 shadow-xs">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shrink-0 mt-0.5">
            <i class="ph ph-shield-check"></i>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-indigo-900">{{ __('24-Hour Turnaround SLA Commitment') }}</p>
            <p class="text-indigo-800 leading-relaxed">
                {{ __('When you submit this ticket, our engineering response timer starts immediately. We guarantee an initial investigation, status update, or resolution within 24 hours. Your data and tenant ledger integrity are protected under our zero-data-loss architecture.') }}
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
            <p class="font-bold">{{ __('Please correct the following errors:') }}</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Ticket Form -->
    <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
        @csrf

        <!-- Subject -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                {{ __('Subject / Summary') }} <span class="text-red-600">*</span>
            </label>
            <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="e.g. Issue connecting custom SMTP or tax calculation query..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
        </div>

        <!-- Category & Priority -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    {{ __('Category') }} <span class="text-red-600">*</span>
                </label>
                <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                    <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>{{ __('Technical / Bug / Error') }}</option>
                    <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>{{ __('Billing / Plan / Subscription') }}</option>
                    <option value="feature_request" {{ old('category') === 'feature_request' ? 'selected' : '' }}>{{ __('Feature Request / Suggestion') }}</option>
                    <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>{{ __('General Invoicing Question') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    {{ __('Priority') }} <span class="text-red-600">*</span>
                </label>
                <select name="priority" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('Medium (Standard 24h Turnaround)') }}</option>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('Low (General Question)') }}</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('High (Impacts Workflow)') }}</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('Urgent (Critical Block)') }}</option>
                </select>
            </div>
        </div>

        <!-- Message -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                {{ __('Detailed Description') }} <span class="text-red-600">*</span>
            </label>
            <textarea name="message" rows="6" required placeholder="Please describe the issue in detail, including steps to reproduce, invoice numbers, or error messages encountered..."
                      class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">{{ old('message') }}</textarea>
            <p class="text-[11px] text-slate-400 mt-1">Minimum 10 characters.</p>
        </div>

        <!-- Attachment -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                {{ __('Attachment (Screenshot, Log, or Document)') }}
            </label>
            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx"
                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 transition cursor-pointer">
            <p class="text-[11px] text-slate-400 mt-1">Supported: JPG, PNG, PDF, ZIP, DOCX (Max: 10MB).</p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('support.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center gap-2">
                <i class="ph ph-paper-plane-tilt text-base"></i>
                <span>{{ __('Submit Ticket & Start 24h SLA') }}</span>
            </button>
        </div>
    </form>

</div>
@endsection
