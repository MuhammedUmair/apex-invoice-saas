@extends('layouts.admin', ['title' => 'Email Templates', 'header' => 'Platform Email Templates'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Platform Email Notification Templates</h1>
            <p class="text-xs text-slate-400 mt-0.5">Core transactional notifications dispatched across the platform lifecycle.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($templates as $tpl)
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-white text-sm">{{ $tpl['name'] }}</h3>
                    <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-800 text-amber-400 border border-slate-700">
                        {{ $tpl['slug'] }}
                    </span>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                    <span class="text-slate-500">Subject:</span>
                    <strong class="text-slate-200">{{ $tpl['subject'] }}</strong>
                </div>
                <div class="flex justify-end pt-2">
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="ph ph-check-circle"></i> Active in Mailer
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
