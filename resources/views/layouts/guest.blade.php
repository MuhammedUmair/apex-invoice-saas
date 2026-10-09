<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Apex Invoice Studio' }}</title>
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
        <p>&copy; {{ date('Y') }} Apex Invoice Studio &bull; Enterprise Multi-Tenant Billing Platform</p>
    </footer>

    @livewireScripts
</body>
</html>
