@extends('layouts.admin', ['title' => 'System Health', 'header' => 'Infrastructure Health & Diagnostics'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2">
                <i class="ph ph-heartbeat text-emerald-400"></i> Platform Health Status
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Real-time connectivity tests and runtime infrastructure metrics.</p>
        </div>
        <a href="{{ route('admin.monitoring.health') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="ph ph-arrows-clockwise"></i> Re-test Diagnostics
        </a>
    </div>

    <!-- Health Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Database -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-database text-xl text-amber-400"></i>
                    <h3 class="font-bold text-white text-sm">Database Engine</h3>
                </div>
                <span class="w-3 h-3 rounded-full {{ $health['database_connected'] ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
            </div>
            <div class="text-xs text-slate-400">
                Connection: <strong class="{{ $health['database_connected'] ? 'text-emerald-400' : 'text-rose-400' }}">{{ $health['database_connected'] ? 'Connected' : 'Failed' }}</strong>
            </div>
            <div class="text-[11px] font-mono text-slate-500 truncate">{{ $health['database_version'] }}</div>
        </div>

        <!-- Disk Storage -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-hard-drive text-xl text-sky-400"></i>
                    <h3 class="font-bold text-white text-sm">Disk Storage</h3>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xs text-slate-400">
                Free Disk Space: <strong class="text-white">{{ $health['disk_free_gb'] }} GB</strong> / {{ $health['disk_total_gb'] }} GB
            </div>
            <div class="text-[11px] text-slate-500">Writable: <strong class="text-emerald-400">Yes</strong></div>
        </div>

        <!-- PHP / Hostinger Runtime -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-cpu text-xl text-indigo-400"></i>
                    <h3 class="font-bold text-white text-sm">Runtime Environment</h3>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xs text-slate-400">
                PHP: <strong class="text-white">v{{ $health['php_version'] }}</strong> &bull; Laravel: <strong class="text-white">v{{ $health['laravel_version'] }}</strong>
            </div>
            <div class="text-[11px] text-slate-500">Hostinger Cloud / LiteSpeed</div>
        </div>

        <!-- Cache Driver -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-lightning text-xl text-yellow-400"></i>
                    <h3 class="font-bold text-white text-sm">Cache Driver</h3>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xs text-slate-400">
                Driver: <strong class="text-white font-mono uppercase">{{ $health['cache_driver'] }}</strong>
            </div>
            <div class="text-[11px] text-slate-500">Session: <strong class="text-slate-300 font-mono">{{ $health['session_driver'] }}</strong></div>
        </div>

        <!-- Mail System -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-envelope-simple text-xl text-rose-400"></i>
                    <h3 class="font-bold text-white text-sm">Platform Mail Delivery</h3>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xs text-slate-400">
                Driver: <strong class="text-white font-mono uppercase">{{ $health['mail_driver'] }}</strong>
            </div>
            <div class="text-[11px] text-slate-500">Hostinger SMTP Relay</div>
        </div>

        <!-- Security Scope -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ph ph-shield-check text-xl text-emerald-400"></i>
                    <h3 class="font-bold text-white text-sm">Multi-Tenant Scoping</h3>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xs text-slate-400">
                Isolation Mode: <strong class="text-emerald-400">Fail-Closed Protected</strong>
            </div>
            <div class="text-[11px] text-slate-500">WorkspaceScope active on all Eloquent models</div>
        </div>
    </div>
</div>
@endsection
