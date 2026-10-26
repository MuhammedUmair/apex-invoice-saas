@extends('layouts.app', ['title' => 'Website & Docs CMS', 'header' => 'Website & Documentation CMS'])

@section('content')
<div class="space-y-8" x-data="{ currentTab: 'landing', editingArticleId: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-red-50 text-red-600 border border-red-200">
                    <i class="ph ph-note-pencil text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Content Management System (CMS)</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Update marketing website copy, manage documentation in English, and review/edit auto-translated versions in Urdu, Arabic, Hindi, and Turkish.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold">
            <button @click="currentTab = 'landing'" 
                    :class="currentTab === 'landing' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="ph ph-browsers text-base"></i> Landing Website Copy
            </button>
            <button @click="currentTab = 'docs'" 
                    :class="currentTab === 'docs' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="ph ph-book-open text-base text-red-600"></i> Multilingual Docs Hub
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tab 1: Landing Page Content Editor -->
    <div x-show="currentTab === 'landing'" class="space-y-6">
        <form method="POST" action="{{ route('cms.update') }}" class="space-y-6">
            @csrf

            <!-- Hero Section Copy -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-megaphone-simple"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Hero Section Headline & Subtitle</h3>
                        <p class="text-xs text-slate-500">The primary value proposition displayed above the fold on redmapleagency.com</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Top Announcement Badge</label>
                        <input type="text" name="hero_badge" value="{{ $cms['hero_badge'] ?? 'Introducing Invoicy by RedMaple • Automated Payment Reminders & Multi-Currency GAAP Accounting' }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hero Main Title (Headline)</label>
                        <input type="text" name="hero_title" value="{{ $cms['hero_title'] ?? 'Get Paid Faster. Automate Reminders. Invoice Worldwide.' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hero Subtitle Paragraph</label>
                        <textarea name="hero_subtitle" rows="3"
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">{{ $cms['hero_subtitle'] ?? 'Invoicy by RedMaple gives modern agencies, freelancers, and businesses real-time VAT calculations, automated milestone payment reminders (45, 30, 15, 7, 3, 2 days before due), password-protected client PDFs, and seamless one-click billing.' }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Primary CTA Button Text</label>
                            <input type="text" name="hero_cta_primary" value="{{ $cms['hero_cta_primary'] ?? 'Start Free with Invoicy' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Secondary CTA Button Text</label>
                            <input type="text" name="hero_cta_secondary" value="{{ $cms['hero_cta_secondary'] ?? 'Launch App / Login' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future SaaS Products Copy -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">RedMaple SaaS Suite Roadmap Copy</h3>
                        <p class="text-xs text-slate-500">Showcase your upcoming software products (CRM, HRMS, Project Desk, POS)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">RedMaple CRM Pro Description</label>
                        <textarea name="crm_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['crm_desc'] ?? 'Visual Kanban pipelines, deal stages, client activity tracking, email sync, and automated proposal-to-invoice conversion.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">RedMaple HRMS & Payroll Description</label>
                        <textarea name="hrms_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['hrms_desc'] ?? 'Employee directories, attendance, vacation requests, digital payslips, and compliance withholding calculation.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">RedMaple Project Desk Description</label>
                        <textarea name="project_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['project_desc'] ?? 'Project milestones, billable hours tracking, client collaborative boards, and instant time-log conversion into Invoicy line items.' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">RedMaple POS Description</label>
                        <textarea name="pos_desc" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ $cms['pos_desc'] ?? 'Barcode scanning, receipt printers, cash drawers, multi-location stock replenishment, and instant receipt dispatch.' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-8">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                    <i class="ph ph-floppy-disk text-base"></i> Save Website Content
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 2: Multilingual Documentation CMS with Auto-Translate & Review -->
    <div x-show="currentTab === 'docs'" class="space-y-8">
        <!-- New Doc Article Creator -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-plus-circle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Publish New Documentation Article</h3>
                        <p class="text-xs text-slate-500">Write in English; the built-in AI engine will automatically translate to Urdu, Arabic, Hindi, and Turkish</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('cms.docs.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Article Title (English) *</label>
                        <input type="text" name="title_en" required placeholder="e.g. How to Automate Payment Reminders"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category *</label>
                        <select name="category" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white">
                            <option value="Getting Started">Getting Started</option>
                            <option value="Invoice Studio">Invoice Studio</option>
                            <option value="Payment Reminders">Payment Reminders</option>
                            <option value="Taxes & Multi-Currency">Taxes & Multi-Currency</option>
                            <option value="Credit Notes">Credit Notes</option>
                            <option value="Security & Compliance">Security & Compliance</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Article Content (English) *</label>
                    <textarea name="content_en" rows="5" required placeholder="Detailed guide, instructions, or documentation body..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow transition">
                        <i class="ph ph-magic-wand text-base"></i> Create & Auto-Translate to All Languages
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Doc Articles with Multi-Language Review & Edit -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Published Documentation Articles & Translations</h3>
                    <p class="text-xs text-slate-500">Click on any article to review or modify translated versions in Urdu, Arabic, Hindi, or Turkish</p>
                </div>
                <span class="text-xs bg-slate-100 text-slate-700 font-bold px-3 py-1 rounded-full">
                    {{ $docArticles->count() }} Articles
                </span>
            </div>

            @if($docArticles->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">
                    No custom documentation articles published yet. Create your first article above!
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($docArticles as $art)
                        <div x-data="{ openReview: false, activeLang: 'ur' }" class="p-6 hover:bg-slate-50/50 transition">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-red-600 bg-red-50 px-2 py-0.5 rounded">
                                        {{ $art->category }}
                                    </span>
                                    <h4 class="text-base font-bold text-slate-900 mt-1">{{ $art->title_en }}</h4>
                                    <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ Str::limit($art->content_en, 100) }}</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('cms.docs.translate', $art->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 flex items-center gap-1" title="Re-run machine translation">
                                            <i class="ph ph-arrows-clockwise text-sm text-blue-600"></i> Auto-Translate
                                        </button>
                                    </form>
                                    <button @click="openReview = !openReview" 
                                            class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold border border-red-200 flex items-center gap-1">
                                        <i class="ph ph-translate text-sm"></i> Review & Edit Translations ▾
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable Review & Edit Modal Box -->
                            <div x-show="openReview" x-cloak class="mt-6 pt-6 border-t border-slate-200 bg-slate-50/70 p-5 rounded-xl space-y-4">
                                <form method="POST" action="{{ route('cms.docs.update', $art->id) }}" class="space-y-4">
                                    @csrf

                                    <!-- Language Selector Tabs -->
                                    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto text-xs font-bold">
                                        <button type="button" @click="activeLang = 'en'" :class="activeLang === 'en' ? 'text-red-600 border-b-2 border-red-600 pb-1' : 'text-slate-500'">
                                            🇺🇸 English (Source)
                                        </button>
                                        <button type="button" @click="activeLang = 'ur'" :class="activeLang === 'ur' ? 'text-red-600 border-b-2 border-red-600 pb-1' : 'text-slate-500'">
                                            🇵🇰 Urdu (اردو)
                                        </button>
                                        <button type="button" @click="activeLang = 'ar'" :class="activeLang === 'ar' ? 'text-red-600 border-b-2 border-red-600 pb-1' : 'text-slate-500'">
                                            🇦🇪 Arabic (العربية)
                                        </button>
                                        <button type="button" @click="activeLang = 'hi'" :class="activeLang === 'hi' ? 'text-red-600 border-b-2 border-red-600 pb-1' : 'text-slate-500'">
                                            🇮🇳 Hindi (हिन्दी)
                                        </button>
                                        <button type="button" @click="activeLang = 'tr'" :class="activeLang === 'tr' ? 'text-red-600 border-b-2 border-red-600 pb-1' : 'text-slate-500'">
                                            🇹🇷 Turkish (Türkçe)
                                        </button>
                                    </div>

                                    <input type="hidden" name="category" value="{{ $art->category }}">

                                    <!-- English Tab -->
                                    <div x-show="activeLang === 'en'" class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Title (English)</label>
                                            <input type="text" name="title_en" value="{{ $art->title_en }}" class="w-full px-3 py-2 rounded-lg border text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Content (English)</label>
                                            <textarea name="content_en" rows="4" class="w-full px-3 py-2 rounded-lg border text-xs">{{ $art->content_en }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Urdu Tab -->
                                    <div x-show="activeLang === 'ur'" class="space-y-3" dir="rtl">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 text-right">عنوان (Urdu)</label>
                                            <input type="text" name="title_ur" value="{{ $art->title_ur }}" class="w-full px-3 py-2 rounded-lg border text-sm text-right">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 text-right">مواد (Urdu Content)</label>
                                            <textarea name="content_ur" rows="4" class="w-full px-3 py-2 rounded-lg border text-xs text-right">{{ $art->content_ur }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Arabic Tab -->
                                    <div x-show="activeLang === 'ar'" class="space-y-3" dir="rtl">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 text-right">العنوان (Arabic)</label>
                                            <input type="text" name="title_ar" value="{{ $art->title_ar }}" class="w-full px-3 py-2 rounded-lg border text-sm text-right">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 text-right">المحتوى (Arabic Content)</label>
                                            <textarea name="content_ar" rows="4" class="w-full px-3 py-2 rounded-lg border text-xs text-right">{{ $art->content_ar }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Hindi Tab -->
                                    <div x-show="activeLang === 'hi'" class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">शीर्षक (Hindi)</label>
                                            <input type="text" name="title_hi" value="{{ $art->title_hi }}" class="w-full px-3 py-2 rounded-lg border text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">विवरण (Hindi Content)</label>
                                            <textarea name="content_hi" rows="4" class="w-full px-3 py-2 rounded-lg border text-xs">{{ $art->content_hi }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Turkish Tab -->
                                    <div x-show="activeLang === 'tr'" class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Başlık (Turkish)</label>
                                            <input type="text" name="title_tr" value="{{ $art->title_tr }}" class="w-full px-3 py-2 rounded-lg border text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">İçerik (Turkish Content)</label>
                                            <textarea name="content_tr" rows="4" class="w-full px-3 py-2 rounded-lg border text-xs">{{ $art->content_tr }}</textarea>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                            <input type="checkbox" name="is_published" value="1" {{ $art->is_published ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded">
                                            Publish to Public Docs Hub
                                        </label>

                                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs shadow transition">
                                            Save Reviewed Translations
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
