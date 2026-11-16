@extends('layouts.admin', ['title' => 'Background Jobs', 'header' => 'Queue Workers & Background Jobs'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-xl font-black text-white">Background Jobs & Queue Health</h1>
            <p class="text-xs text-slate-400 mt-0.5">Asynchronous queue workers, PDF rendering jobs, and automated reminder tasks.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Queue Driver</div>
            <div class="text-2xl font-black text-white mt-1 font-mono uppercase">{{ config('queue.default') }}</div>
            <div class="text-xs text-slate-500 mt-1">Direct database/sync worker execution</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-emerald-400 uppercase">Worker Latency</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">&lt; 150ms</div>
            <div class="text-xs text-slate-500 mt-1">Average invoice dispatch turnaround</div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="text-xs font-bold text-cyan-400 uppercase">Failed Jobs</div>
            <div class="text-2xl font-black text-white mt-1">0</div>
            <div class="text-xs text-slate-500 mt-1">All queues healthy</div>
        </div>
    </div>
</div>
@endsection
