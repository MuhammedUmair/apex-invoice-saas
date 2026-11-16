@extends('layouts.admin', ['title' => 'Error Logs', 'header' => 'Application Error Logs'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-warning-circle text-rose-400"></i> Application Error Logs
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Sanitized application trace. Passwords, secrets, and auth tokens are automatically redacted.</p>
        </div>
        <a href="{{ route('admin.monitoring.errors') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrows-clockwise"></i> Refresh Logs
        </a>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4 font-mono text-xs overflow-x-auto shadow-sm max-h-[600px] overflow-y-auto">
        @forelse($logLines as $line)
            <div class="py-0.5 text-slate-300 hover:bg-slate-800/40 px-2 rounded {{ str_contains($line, 'ERROR') || str_contains($line, 'Exception') ? 'text-rose-400 font-bold' : '' }}">
                {{ $line }}
            </div>
        @empty
            <div class="text-center py-12 text-slate-500">No recent errors recorded. Log file is clean.</div>
        @endforelse
    </div>
</div>
@endsection
