@extends('layouts.guest')

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center mx-auto mb-3 font-black text-2xl shadow-md">
                A
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Apex Invoice Studio</h2>
            <p class="text-sm text-slate-500 mt-1">Sign in to your agency portal</p>
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
                <input id="email" type="email" name="email" value="{{ old('email', 'admin@apexstudios.com') }}" required autofocus
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                </div>
                <input id="password" type="password" name="password" required value="Password123!"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <span class="text-slate-600 text-xs">Keep me signed in</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-lock-key text-base"></i>
                Sign In to Studio
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Don't have an agency account yet? 
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Start 14-day free trial</a>
        </div>
    </div>
</div>
@endsection
