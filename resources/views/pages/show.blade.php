@php
    $websiteLogo = \App\Models\CmsContent::getWebsiteLogo();
    $faviconUrl = \App\Models\CmsContent::getFaviconUrl();
    $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
    $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
    $navItems = \App\Models\CmsContent::getNavItems();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} — {{ $brandName }} by {{ $brandSub }}</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <!-- Dynamic Favicon -->
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 900 900'><path fill='%23dc2626' d='M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z'/></svg>">
    @endif

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                @if($websiteLogo)
                    <div class="h-10 flex items-center">
                        <img src="{{ $websiteLogo }}" alt="{{ $brandName }}" class="max-h-10 max-w-[200px] object-contain">
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center p-2 shadow-xs group-hover:scale-105 transition">
                        <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black text-slate-900 tracking-tight">{{ $brandName }} <span class="text-xs font-semibold text-slate-500">by</span> <span class="text-red-600">{{ $brandSub }}</span></span>
                        <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-400">Enterprise SaaS Suite</span>
                    </div>
                @endif
            </a>

            <!-- Dynamic Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                @foreach($navItems as $nav)
                    <a href="{{ $nav['url'] }}" target="{{ $nav['target'] ?? '_self' }}" class="hover:text-red-600 transition">
                        {{ $nav['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 transition">
                        <i class="ph ph-squares-four text-base"></i> Open Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-red-600 px-3 py-2 transition">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md transition">
                        Get Started Free
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1 w-full">
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-6">
                <a href="/" class="text-xs font-bold text-red-600 hover:underline flex items-center gap-1 mb-3">
                    <i class="ph ph-arrow-left"></i> Back to Home
                </a>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $page->title }}
                </h1>
                <p class="text-xs text-slate-400 mt-2">Last updated: {{ $page->updated_at->format('M d, Y') }}</p>
            </div>

            <!-- Page Body Content (HTML Supported) -->
            <div class="prose max-w-none text-slate-700 text-sm leading-relaxed space-y-4">
                {!! $page->content !!}
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">{{ $brandName }} <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-600">{{ $brandSub }}</span></span>
            </div>
            <p>{{ \App\Models\CmsContent::getVal('footer_copyright', '© ' . date('Y') . " $brandName by $brandSub. All rights reserved.") }}</p>
        </div>
    </footer>

</body>
</html>
