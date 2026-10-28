@php
    $placement = $placement ?? 'invoice_sidebar';
    $ad = null;
    try {
        $ad = \App\Models\Advertisement::getActive($placement);
        if ($ad) {
            $ad->increment('impressions_count');
        }
    } catch (\Throwable $e) {
        $ad = null;
    }
@endphp

@if($ad)
    @php
        $target = $ad->open_in_new_tab ? '_blank' : '_self';
        $clickUrl = route('ads.click', $ad->id);
    @endphp

    @if($ad->type === 'image' && !empty($ad->image_path))
        <!-- Image Banner Advertisement -->
        <div class="relative overflow-hidden rounded-2xl shadow-md border border-slate-200/80 group">
            <a href="{{ $clickUrl }}" target="{{ $target }}" class="block">
                <img src="{{ $ad->image_path }}" alt="{{ $ad->alt_text ?? $ad->title }}" 
                     class="w-full h-auto max-h-56 object-cover group-hover:scale-102 transition duration-300">
            </a>
            <div class="absolute bottom-1 right-2 bg-slate-900/70 text-slate-300 text-[8px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider backdrop-blur-2xs">
                Ad
            </div>
        </div>

    @elseif($ad->type === 'html' && !empty($ad->html_code))
        <!-- Raw HTML / Script Embed Advertisement -->
        <div class="overflow-hidden rounded-2xl shadow-sm border border-slate-200 p-3 bg-white">
            <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1 flex items-center justify-between">
                <span>Sponsored</span>
                <i class="ph ph-info text-slate-400"></i>
            </div>
            <div class="ad-html-container overflow-hidden">
                {!! $ad->html_code !!}
            </div>
        </div>

    @else
        <!-- Text-Based Sponsored Promo Advertisement -->
        @php
            $styles = $ad->getThemeStyles();
        @endphp
        <div class="relative overflow-hidden rounded-2xl {{ $styles['card_bg'] }} border p-5 text-white shadow-md">
            <div class="absolute -right-6 -top-6 w-24 h-24 {{ $styles['glow'] }} rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $styles['badge'] }} font-bold text-[10px] uppercase tracking-wider border">
                        <i class="ph ph-sparkle text-amber-300"></i> {{ $ad->badge_text ?? 'SPONSORED PROMO' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">RedMaple Studio</span>
                </div>

                <div>
                    <h4 class="font-extrabold text-white text-sm leading-snug">
                        {{ $ad->headline }}
                    </h4>
                    @if($ad->body_text)
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            {{ $ad->body_text }}
                        </p>
                    @endif
                </div>

                <div class="pt-1 flex items-center gap-2">
                    <a href="{{ $clickUrl }}" target="{{ $target }}" 
                       class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 {{ $styles['btn'] }} font-bold text-xs rounded-xl shadow-sm transition">
                        <span>{{ $ad->cta_text ?? 'Learn More' }}</span>
                        <i class="ph ph-arrow-up-right font-bold text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

@else
    <!-- Fallback Default Advertisement -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-red-950 border border-red-500/30 p-5 text-white shadow-md">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-600/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 space-y-3">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/20 border border-red-400/30 text-red-300 font-bold text-[10px] uppercase tracking-wider">
                    <i class="ph ph-sparkle text-amber-300"></i> SPONSORED PROMO
                </span>
                <span class="text-[10px] text-slate-400 font-medium">RedMaple Studio</span>
            </div>

            <div>
                <h4 class="font-extrabold text-white text-sm leading-snug">
                    Scale Your Business with Custom Web & Mobile Apps
                </h4>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Dedicated full-stack developers, custom CRM integrations, and turnkey SaaS development with RedMaple.
                </p>
            </div>

            <div class="pt-1 flex items-center gap-2">
                <a href="https://redmapleagency.com" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <span>Get Agency Quote</span>
                    <i class="ph ph-arrow-up-right font-bold text-xs"></i>
                </a>
                <a href="{{ route('membership.index') }}" class="inline-flex items-center justify-center py-2 px-3 bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs rounded-xl border border-slate-700 transition">
                    <span>Upgrade</span>
                </a>
            </div>
        </div>
    </div>
@endif
