@php
    $faviconUrl = \App\Models\CmsContent::getFaviconUrl();
    $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
    $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? "$brandName by $brandSub" }}</title>
    <!-- Dynamic Favicon -->
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/maple-leaf.svg') }}">
    @endif
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Phosphor Icons CDN -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
    <div>
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    <footer class="py-6 text-center text-xs text-slate-400">
        @php
            $sysLogo = \App\Models\CmsContent::getSystemLogo();
            $brandName = \App\Models\CmsContent::getVal('website_brand_name', 'Invoicy');
            $brandSub = \App\Models\CmsContent::getVal('website_brand_sub', 'RedMaple');
            $footerCopyright = \App\Models\CmsContent::getVal('footer_copyright');
        @endphp
        <div class="flex items-center justify-center gap-2 mb-1.5 text-slate-500">
            @if($sysLogo)
                <img src="{{ $sysLogo }}" alt="{{ $brandName }}" class="h-4 max-w-[120px] object-contain inline-block">
            @else
                <svg class="w-3.5 h-3.5 text-red-600 inline-block" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            @endif
            <span class="font-bold text-slate-700">{{ $brandName }} <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-600">{{ $brandSub }}</span></span>
        </div>
        @if($footerCopyright)
            <p>{{ $footerCopyright }}</p>
        @else
            <p>&copy; {{ date('Y') }} {{ $brandSub }} &bull; Multi-Tenant SaaS Billing Suite</p>
        @endif
    </footer>

    @livewireScripts
</body>
</html>
