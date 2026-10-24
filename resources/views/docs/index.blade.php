<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ in_array($currentLang, ['ur', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation & Multi-Language Guide - Invoicy by RedMaple</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/maple-leaf.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <style>
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .ltr-text { direction: ltr; text-align: left; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center p-1.5 shrink-0 shadow-sm">
                        <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-black text-slate-900 text-sm tracking-tight flex items-center gap-1">
                            Red<span class="text-red-600">Maple</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block -mt-1">User Manual & API</span>
                    </div>
                </a>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                    v1.0 Production
                </span>
            </div>

            <!-- Language Switcher & App Links -->
            <div class="flex items-center gap-3">
                <!-- Language Selector Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold text-slate-700 transition">
                        <span>{{ $languages[$currentLang]['flag'] }}</span>
                        <span>{{ $languages[$currentLang]['name'] }} ({{ $languages[$currentLang]['native'] }})</span>
                        <i class="ph ph-caret-down text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50">
                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Select Documentation Language</div>
                        @foreach($languages as $code => $meta)
                            <a href="{{ route('docs.index', ['lang' => $code]) }}" class="flex items-center justify-between px-3 py-2 text-xs hover:bg-red-50 hover:text-red-700 transition {{ $currentLang === $code ? 'bg-red-50 text-red-700 font-bold' : 'text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <span>{{ $meta['flag'] }}</span>
                                    <span>{{ $meta['name'] }}</span>
                                </span>
                                <span class="text-slate-400 text-[11px] font-medium">{{ $meta['native'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <i class="ph ph-arrow-left"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        Sign In <i class="ph ph-arrow-right"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container with Sidebar + Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 flex flex-col md:flex-row gap-8 w-full">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm sticky top-24">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Table of Contents</h4>
                <nav class="space-y-1 text-sm">
                    <a href="#overview" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-house text-base text-slate-400"></i> Platform Overview
                    </a>
                    <a href="#client-crm" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-users text-base text-slate-400"></i> Client CRM & Languages
                    </a>
                    <a href="#invoicing-engine" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-receipt text-base text-slate-400"></i> Invoicing & Sequential Numbers
                    </a>
                    <a href="#tax-and-security" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-shield-check text-base text-slate-400"></i> Taxation & PDF Passwords
                    </a>
                    <a href="#credit-notes" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-arrow-u-up-left text-base text-slate-400"></i> Credit Notes & Refunds
                    </a>
                    <a href="#payments-webhooks" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-credit-card text-base text-slate-400"></i> Stripe, PayPal & Webhooks
                    </a>
                    <a href="#membership-coupons" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                        <i class="ph ph-tag text-base text-slate-400"></i> Membership & Coupons
                    </a>
                </nav>

                <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                    <p class="font-semibold text-slate-700">Need Live Support?</p>
                    <p class="text-[11px] mt-0.5">Contact host administrator or view live API logs.</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area with Localized Content -->
        <main class="flex-1 space-y-10 min-w-0">

            <!-- Language Notice Banner -->
            <div class="bg-gradient-to-r from-red-600 to-rose-700 rounded-2xl p-6 text-white shadow-md flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <span>{{ $languages[$currentLang]['flag'] }}</span>
                        @if($currentLang === 'ur')
                            <span>ریڈ میپل انوائس جنریٹر - مکمل اردو رہنمائی</span>
                        @elseif($currentLang === 'ar')
                            <span>مولد فواتير ريد مابل - الدليل الشامل باللغة العربية</span>
                        @elseif($currentLang === 'hi')
                            <span>रेडमेपल इनवॉइस जेनरेटर - सम्पूर्ण हिंदी गाइड</span>
                        @elseif($currentLang === 'tr')
                            <span>RedMaple Fatura Oluşturucu - Türkçe Kullanım Kılavuzu</span>
                        @else
                            <span>RedMaple Invoice Generator - Multilingual User Manual</span>
                        @endif
                    </h2>
                    <p class="text-red-100 text-sm mt-1">
                        @if($currentLang === 'ur')
                            یہ دستاویز آپ کو ملٹی ٹیننٹ انوائسنگ، کلائنٹ ڈائرکٹری، ادائیگیوں، ٹیکس، اور پی ڈی ایف سیکیورٹی کے طریقہ کار کی مکمل رہنمائی فراہم کرتی ہے۔
                        @elseif($currentLang === 'ar')
                            يوفر هذا الدليل شرحاً تفصيلياً لإدارة الفواتير متعددة المستأجرين، دليل العملاء، بوابات الدفع، والضرائب الإقليمية.
                        @elseif($currentLang === 'hi')
                            यह दस्तावेज़ आपको बहु-किरायेदार इनवॉइसिंग, क्लाइंट डायरेक्टरी, भुगतान गेटवे और टैक्स नीतियों का संपूर्ण मार्गदर्शन प्रदान करता है।
                        @elseif($currentLang === 'tr')
                            Bu kılavuz, çok kiracılı faturalama, müşteri rehberi, ödeme entegrasyonları ve vergi yapılandırmasını açıklar.
                        @else
                            Complete guide for multi-tenant SaaS invoicing, client CRM, Stripe/PayPal checkout, localized taxes, and secure PDF delivery.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Section 1: Overview & Tenant Isolation -->
            <section id="overview" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">1. Platform Architecture & Multi-Tenancy</h3>
                        <p class="text-xs text-slate-500">Cryptographically isolated agency workspaces and tenant scopes</p>
                    </div>
                </div>

                @if($currentLang === 'ur')
                    <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                        <p>ریڈ میپل کا بنیادی ڈھانچہ مکمل طور پر ملٹی ٹیننٹ (Multi-Tenant) پر مبنی ہے۔ ہر ایجنسی کا الگ اور محفوظ ورک اسپیس ہوتا ہے۔</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600">
                            <li><strong>ورک اسپیس آئسولیشن:</strong> کوئی بھی ایجنسی کسی دوسری ایجنسی کے کلائنٹس، انوائسز یا مالیاتی ڈیٹا تک رسائی حاصل نہیں کر سکتی۔</li>
                            <li><strong>سیکیور تصدیق:</strong> پاس ورڈز Bcrypt ہیشنگ کے ساتھ محفوظ کیے جاتے ہیں۔</li>
                            <li><strong>کردار (RBAC):</strong> ایجنسی اونر (Owner)، ایڈمن (Admin)، اور اسٹاف (Staff) کے لیے اجازت نامے متعین ہیں۔</li>
                        </ul>
                    </div>
                @elseif($currentLang === 'ar')
                    <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                        <p>يعتمد نظام ريد مابل على بنية العزل التام للمستأجرين (Multi-Tenancy)، حيث تمتلك كل وكالة مساحة عمل خاصة ومستقلة بالكامل.</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600">
                            <li><strong>عزل البيانات:</strong> لا يمكن لأي وكالة الاطلاع على فواتير أو عملاء أي وكالة أخرى.</li>
                            <li><strong>التوثيق الآمن:</strong> تشفير كلمات المرور باستخدام خوارزمية Bcrypt مع حماية ضد هجمات CSRF.</li>
                            <li><strong>أدوار المستخدمين:</strong> صلاحيات مقسمة بين المالك، المدير، والموظف.</li>
                        </ul>
                    </div>
                @elseif($currentLang === 'hi')
                    <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                        <p>रेडमेपल पूरी तरह से मल्टी-टेनेंट आर्किटेक्चर पर निर्मित है। प्रत्येक एजेंसी का डेटा पूरी तरह से अलग और सुरक्षित रहता है।</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600">
                            <li><strong>डेटा अलगाव:</strong> कोई भी एजेंसी किसी अन्य एजेंसी के क्लाइंट्स या इनवॉइस नहीं देख सकती।</li>
                            <li><strong>सुरक्षित प्रमाणीकरण:</strong> Bcrypt एन्क्रिप्शन के साथ पासवर्ड सुरक्षित रखे जाते हैं।</li>
                            <li><strong>भूमिकाएँ (RBAC):</strong> ओनर, एडमिन, और स्टाफ के लिए अलग अनुमतियाँ।</li>
                        </ul>
                    </div>
                @elseif($currentLang === 'tr')
                    <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                        <p>RedMaple platformu, her ajansın kendi yalıtılmış çalışma alanına sahip olduğu çok kiracılı (multi-tenant) bir mimari ile çalışır.</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600">
                            <li><strong>Ajans İzolasyonu:</strong> Hiçbir ajans başka bir ajansın faturalarını veya müşteri verilerini göremez.</li>
                            <li><strong>Güvenli Kimlik Doğrulama:</strong> Bcrypt parola şifrelemesi ve CSRF koruması mevcuttur.</li>
                            <li><strong>Roller:</strong> Sahip (Owner), Yönetici (Admin) ve Personel (Staff) rolleri desteklenir.</li>
                        </ul>
                    </div>
                @else
                    <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                        <p>RedMaple is built on a strict multi-tenant schema where every agency operates in an isolated workspace container:</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600">
                            <li><strong>Tenant Scoping:</strong> All database queries are automatically scoped using <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">WorkspaceScope</code>, guaranteeing zero data leakage between agencies.</li>
                            <li><strong>Enterprise Authentication:</strong> Strong Bcrypt credential hashing and CSRF token verification.</li>
                            <li><strong>Role-Based Access Control (RBAC):</strong> Permissions segregated by Owner, Admin, and Staff tiers.</li>
                        </ul>
                    </div>
                @endif
            </section>

            <!-- Section 2: Client CRM & Languages -->
            <section id="client-crm" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-users"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">2. Client Directory & Multilingual Support (Urdu, Hindi, Turkish)</h3>
                        <p class="text-xs text-slate-500">Multi-currency billing and localized customer preferences</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>
                        @if($currentLang === 'ur')
                            کلائنٹ ڈائرکٹری میں کلائنٹ شامل کرتے وقت آپ ان کی پسندیدہ زبان، کرنسی اور ادائیگی کی شرائط منتخب کر سکتے ہیں:
                        @elseif($currentLang === 'ar')
                            عند إضافة عميل جديد، يمكنك تعيين لغة الفاتورة المفضلة، العملة الإقليمية، وشروط الاستحقاق:
                        @elseif($currentLang === 'hi')
                            क्लाइंट जोड़ते समय आप उसकी पसंदीदा भाषा (हिंदी, उर्दू, तुर्की आदि), मुद्रा और भुगतान की शर्तें चुन सकते हैं:
                        @elseif($currentLang === 'tr')
                            Müşteri eklerken müşterinin faturada tercih ettiği dili (Türkçe, Urduca, Hintçe vb.), para birimini ve ödeme vadelerini belirleyebilirsiniz:
                        @else
                            When registering a client in the CRM, you can configure their specific language and billing rules:
                        @endif
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Supported Languages</span>
                            <span class="text-xs text-slate-600 leading-normal block">English, Urdu (اردو), Hindi (हिन्दी), Turkish (Türkçe), Arabic (العربية), Spanish, French, German, Chinese.</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Supported Currencies</span>
                            <span class="text-xs text-slate-600 leading-normal block">USD ($), EUR (€), GBP (£), AED (د.إ), SAR (﷼), CAD (C$), AUD (A$), INR (₹), TRY (₺), PKR (Rs).</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block mb-1">Payment Terms</span>
                            <span class="text-xs text-slate-600 leading-normal block">Due on Receipt, Net 7 Days, Net 14 Days, Net 30 Days, Net 60 Days.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Invoicing Engine -->
            <section id="invoicing-engine" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-receipt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">3. Invoicing Engine & Sequential Numbering Lock</h3>
                        <p class="text-xs text-slate-500">Atomic gap-less sequence locking per workspace</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>
                        @if($currentLang === 'ur')
                            ہر انوائس کو ایک منفرد اور مسلسل نمبر دیا جاتا ہے جیسے کہ <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code>۔ ڈیٹا بیس میں اٹامک لاکنگ (Atomic Locking) اس بات کو یقینی بناتی ہے کہ ایک ہی وقت میں دو انوائسز پر ایک جیسا نمبر جاری نہ ہو۔
                        @elseif($currentLang === 'ar')
                            يتم ترقيم الفواتير تسلسلياً برمز فريد مثل <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code> مع قفل تسلسلي ذري يمنع تكرار الأرقام أو وجود فجوات حسابية.
                        @elseif($currentLang === 'hi')
                            प्रत्येक इनवॉइस को एक क्रमिक संख्या दी जाती है (जैसे <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code>)। डेटाबेस लॉकिंग सुनिश्चित करती है कि कोई डुप्लीकेट नंबर उत्पन्न न हो।
                        @elseif($currentLang === 'tr')
                            Her fatura <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code> gibi sıralı ve benzersiz bir numara alır. Veritabanı kilidi, eşzamanlı işlemlerde mükerrer fatura numarasını engeller.
                        @else
                            Every issued invoice is assigned a concurrency-safe sequential code (e.g., <code class="bg-slate-100 px-1 py-0.5 rounded text-red-600">INV-2026-0001</code>). An atomic database sequence counter prevents duplicate numbering even under simultaneous checkout attempts.
                        @endif
                    </p>
                </div>
            </section>

            <!-- Section 4: Taxation & PDF Passwords -->
            <section id="tax-and-security" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-lock-key"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">4. Country Taxation Rules & PDF Security</h3>
                        <p class="text-xs text-slate-500">GAAP/IFRS pre-tax discounts and optional client PIN protection</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><strong>Pre-Tax Discount Calculation:</strong> Taxes are computed strictly after deducting discounts: <code class="bg-slate-100 px-1 py-0.5 rounded">Taxable Amount = Subtotal - Discount</code>.</li>
                        <li><strong>Regional Tax Presets:</strong> UK VAT (20%), DE MwSt (19%), UAE VAT (5%), Saudi Arabia VAT (15%), India GST (18%), Turkey KDV (20%), Pakistan Sales Tax (16%).</li>
                        <li><strong>PDF Security PIN:</strong> When creating sensitive commercial invoices, enable "Password Protect PDF". The client must enter the assigned PIN before downloading the document.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 5: Credit Notes & Refunds -->
            <section id="credit-notes" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-arrow-u-up-left"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">5. Credit Notes & Ledger Refunds</h3>
                        <p class="text-xs text-slate-500">Accounting integrity and immutable reversal trail</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>
                        In commercial accounting, issued invoices must never be deleted. To correct errors, billing mistakes, or cancelled projects, an agency issues a **Credit Note** (<code class="bg-slate-100 px-1 py-0.5 rounded">CN-YYYY-XXXX</code>).
                    </p>
                    <p class="text-xs text-slate-600">
                        Credit notes automatically adjust outstanding balances, update the financial audit trail, and maintain strict accounting compliance.
                    </p>
                </div>
            </section>

            <!-- Section 6: Payments & Webhooks -->
            <section id="payments-webhooks" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">6. Stripe, PayPal & Webhook Processing</h3>
                        <p class="text-xs text-slate-500">Idempotent automated reconciliation without duplicate charges</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Configure your webhook endpoints in your gateway developer dashboard:</p>
                    <div class="bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-xs space-y-1 overflow-x-auto ltr-text">
                        <div><span class="text-emerald-400">Stripe Webhook URL:</span> https://redmapleagency.com/webhooks/stripe</div>
                        <div><span class="text-sky-400">PayPal Webhook URL:</span> https://redmapleagency.com/webhooks/paypal</div>
                    </div>
                    <p class="text-xs text-slate-600">Every webhook payload is verified and indexed using its gateway event ID, preventing double-crediting if a webhook is retried.</p>
                </div>
            </section>

            <!-- Section 7: Membership & Coupons -->
            <section id="membership-coupons" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-tag"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">7. SaaS Membership & Atomic Coupons</h3>
                        <p class="text-xs text-slate-500">Promo codes for subscription signups and renewals</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-slate-700 space-y-3">
                    <p>Agencies can subscribe to or renew SaaS tiers using discount coupons:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="font-mono font-bold text-red-600 text-xs">LAUNCH2026</span>
                            <span class="text-xs text-slate-600 block mt-0.5">20% off all plans</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="font-mono font-bold text-red-600 text-xs">HOSTINGER50</span>
                            <span class="text-xs text-slate-600 block mt-0.5">50% promotional discount</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="font-mono font-bold text-red-600 text-xs">SAVE10FIXED</span>
                            <span class="text-xs text-slate-600 block mt-0.5">$10.00 flat discount</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        Coupons feature atomic concurrency checks preventing race conditions during checkout spikes.
                    </p>
                </div>
            </section>

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">Invoicy <span class="text-xs font-normal text-slate-400">by</span> <span class="text-red-600">RedMaple</span></span>
                <span>&bull;</span>
                <span>Multilingual SaaS Documentation</span>
            </div>
            <div>
                &copy; {{ date('Y') }} RedMaple. Production Ready on Hostinger Cloud.
            </div>
        </div>
    </footer>

</body>
</html>
