<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | Invoicy by RedMaple</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/maple-leaf.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between antialiased selection:bg-red-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200/80 bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5 shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-full h-full text-red-600 drop-shadow-xs" viewBox="0 0 900 900" fill="currentColor">
                        <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-slate-900 text-sm tracking-tight flex items-center gap-1">
                        Invoicy <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-600 font-bold">RedMaple</span>
                    </span>
                    <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400 -mt-0.5">SaaS Suite</span>
                </div>
            </a>
            <div>
                <a href="mailto:support@redmapleagency.com" class="text-xs font-bold text-slate-600 hover:text-red-600 transition flex items-center gap-1">
                    <i class="ph ph-envelope-simple text-sm"></i>
                    <span>Contact Support</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-12 sm:py-16 relative overflow-hidden">
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-xl w-full text-center relative z-10">
            <div class="relative inline-block mb-4">
                <div class="text-[100px] sm:text-[140px] font-black tracking-tighter leading-none bg-gradient-to-br from-rose-600 via-red-600 to-amber-600 bg-clip-text text-transparent select-none drop-shadow-sm">
                    500
                </div>
                <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-slate-900 text-white text-[11px] font-extrabold uppercase tracking-widest whitespace-nowrap shadow-md border border-slate-800 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span>Internal Server Error</span>
                </div>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-6 mb-3">
                Something Went Wrong on Our End
            </h1>
            <p class="text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
                An unexpected condition was encountered. Our automated monitoring system has logged this incident and alerted platform administrators.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button onclick="window.location.reload()"
                        type="button"
                        class="w-full sm:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="ph ph-arrows-clockwise text-base"></i>
                    <span>Reload Page</span>
                </button>

                <a href="{{ url('/') }}"
                   class="w-full sm:w-auto px-6 py-3 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition flex items-center justify-center gap-2">
                    <i class="ph ph-house text-base"></i>
                    <span>Return to Home</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200 py-6 bg-white text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} <strong>Invoicy by RedMaple</strong> &bull; All Rights Reserved.
    </footer>

</body>
</html>
