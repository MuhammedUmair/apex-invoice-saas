@extends('layouts.guest')

@section('content')
@php
    $loginLogo = \App\Models\CmsContent::getLoginLogo();
    $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
    $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
    $loginTitle = \App\Models\CmsContent::getVal('login_title');
    $loginSubtitle = \App\Models\CmsContent::getVal('login_subtitle', 'Sign in to your commercial invoicing studio');
@endphp
<div class="min-h-[85vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            @if($loginLogo)
                <div class="h-16 flex items-center justify-center mx-auto mb-3">
                    <img src="{{ $loginLogo }}" alt="{{ $brandName }}" class="max-h-16 max-w-[220px] object-contain">
                </div>
            @else
                <div class="w-16 h-16 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm p-3">
                    <!-- Red Maple Leaf Vector Logo -->
                    <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                        <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                    </svg>
                </div>
            @endif

            @if($loginTitle)
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ $loginTitle }}
                </h2>
            @else
                <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-1">
                    {{ $brandName }} <span class="text-sm font-normal text-slate-400">by</span> <span class="text-red-600">{{ $brandSub }}</span>
                </h2>
            @endif
            <p class="text-xs text-slate-500 mt-1">{{ $loginSubtitle }}</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="name@company.com"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-red-600 hover:underline font-semibold">
                        Forgot Password?
                    </a>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                    <span class="text-slate-600 text-xs">Keep me signed in</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-lock-key text-base"></i>
                Sign In to RedMaple
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2 text-center text-xs text-slate-500">
            <div>
                Don't have an agency account yet? 
                <a href="{{ route('register') }}" class="text-red-600 font-bold hover:underline">Start free trial</a>
            </div>
            <div class="pt-2">
                <a href="{{ route('docs.index') }}" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-red-600 font-medium">
                    <i class="ph ph-book-open text-sky-600"></i>
                    <span>Multilingual User Manual & Guide (اردو, हिन्दी, Türkçe, English)</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
