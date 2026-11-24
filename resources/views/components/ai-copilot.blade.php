@php
    $user = auth()->user() ?? auth()->guard('superadmin')->user();
    $tenantAiConfig = \App\Models\CmsContent::getVal('feature_tenant_ai_agent', '');
    if ($tenantAiConfig === '') {
        $tenantAiConfig = \App\Models\CmsContent::getVal('ai_agent_enabled', '1');
    }
    $isTenantAiActive = ($tenantAiConfig !== '0' && $tenantAiConfig !== false && $tenantAiConfig !== 0);
    $isSuperAdminUser = $user && $user->isSuperAdmin();

    $activeLocale = app()->getLocale();
    if (!in_array($activeLocale, ['en', 'ur', 'ar', 'hi', 'tr', 'es', 'fr', 'de', 'zh'])) {
        $activeLocale = session('locale', 'en');
    }
    $isPortalRtl = in_array($activeLocale, ['ur', 'ar']);
@endphp

@if(!$user || (!$isTenantAiActive && !$isSuperAdminUser))
    {{-- AI Assistant only available after login to customer / authorized user --}}
@else
<script>
(function() {
    function initAiAgent() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('aiAgentComponent', () => ({
                isOpen: false,
                isLoading: false,
                pageLocale: '{{ $activeLocale }}',
                isPageRtl: {{ $isPortalRtl ? 'true' : 'false' }},
                detectedLang: '{{ $activeLocale }}',
                selectedLang: '{{ $activeLocale }}',
                isRtl: {{ $isPortalRtl ? 'true' : 'false' }},
                inputMessage: '',
                conversationId: null,
                ratedMessages: {},

                languages: {
                    auto: { 
                        code: 'auto', 
                        placeholder: 'Ask in any language...', 
                        greeting: 'Hello! I am your Invoicy AI Support Assistant. Ask me anything about creating invoices, managing clients, tracking payments, or system settings!', 
                        suggestions: ['Create new invoice', 'How do drafts work?', 'Open Support Desk', 'My Workspace Stats'] 
                    },
                    ur: { 
                        code: 'ur', 
                        placeholder: 'اردو میں سوال پوچھیں...', 
                        greeting: 'السلام علیکم! میں آپ کا انوائسی AI سپورٹ اسسٹنٹ ہوں۔ انوائس بنانے، نجی ڈرافٹس، کسٹمرز یا فنانشل اعدادوشمار کے بارے میں کوئی بھی سوال پوچھیں!', 
                        suggestions: ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'سپورٹ ڈیسک کھولیں', 'میرے فنانشل اعدادوشمار'] 
                    },
                    'ur-roman': { 
                        code: 'ur-roman', 
                        placeholder: 'Roman Urdu mein sawaal poochein...', 
                        greeting: 'Hello! Main aap ka Invoicy AI Support Assistant hoon. Naya invoice banane, 45+ presets, drafts, balance & stats ya support desk ke bare mein poochein!', 
                        suggestions: ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Open Support Desk', 'Mera Balance & Stats'] 
                    },
                    ar: { 
                        code: 'ar', 
                        placeholder: 'اسأل الوكيل الذكي...', 
                        greeting: 'مرحباً بك! أنا وكيل الدعم الذكي لمنصة Invoicy. اسألني عن فواتيرك، المسودات الآمنة، إدارة العملاء، أو الإعدادات!', 
                        suggestions: ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'فتح مكتب الدعم', 'إحصائيات الحساب'] 
                    },
                    en: { 
                        code: 'en', 
                        placeholder: 'Ask AI Support Assistant...', 
                        greeting: 'Hello! I am your Invoicy AI Support Assistant. Ask me anything about your invoices, draft safeguards, client CRM, payments, or support tickets!', 
                        suggestions: ['Create new invoice', 'How do drafts work?', 'Open Support Desk', 'My Workspace Stats'] 
                    },
                    tr: { 
                        code: 'tr', 
                        placeholder: 'AI Asistanına sor...', 
                        greeting: 'Merhaba! Ben Invoicy AI Destek Asistanınızım. Faturalarınız, taslaklar, ayarlar veya destek masası hakkında soru sorabilirsiniz!', 
                        suggestions: ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Destek Masası Aç', 'İstatistiklerim'] 
                    },
                    hi: { 
                        code: 'hi', 
                        placeholder: 'AI सहायक से पूछें...', 
                        greeting: 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉइस बनाने, ड्राफ्ट सुरक्षा, ग्राहक प्रबंधन या सपोर्ट के बारे में पूछें!', 
                        suggestions: ['नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'सपोर्ट डेस्क खोलें', 'मेरे आंकड़े'] 
                    },
                    es: { 
                        code: 'es', 
                        placeholder: 'Pregunta a la IA...', 
                        greeting: '¡Hola! Soy tu Asistente IA de Invoicy. Pregúntame sobre facturas, borradores confidenciales, clientes o soporte técnico.', 
                        suggestions: ['Crear nueva factura', '¿Cómo funcionan los borradores?', 'Abrir soporte', 'Estadísticas'] 
                    },
                    fr: { 
                        code: 'fr', 
                        placeholder: 'Demandez à l\'Assistant IA...', 
                        greeting: 'Bonjour ! Je suis votre Assistant IA Invoicy. Posez-moi vos questions sur la facturation, les brouillons, les clients ou le support.', 
                        suggestions: ['Créer une facture', 'Fonctionnement des brouillons', 'Ouvrir le support', 'Statistiques'] 
                    },
                    de: { 
                        code: 'de', 
                        placeholder: 'Frage an KI-Assistenten...', 
                        greeting: 'Hallo! Ich bin Ihr Invoicy KI-Support-Assistent. Fragen Sie mich nach Rechnungen, Entwürfen, Kunden oder Support-Tickets.', 
                        suggestions: ['Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Support öffnen', 'Statistiken'] 
                    },
                    zh: { 
                        code: 'zh', 
                        placeholder: '向 AI 助手提问...', 
                        greeting: '您好！我是 Invoicy AI 支持助手。向我询问有关发票、安全草稿、客户管理或支持的问题！', 
                        suggestions: ['创建新发票', '草稿如何运作？', '打开支持', '工作区统计'] 
                    }
                },

                messages: [],

                init() {
                    const activeLoc = '{{ $activeLocale }}';
                    const initialLang = this.languages[activeLoc] || this.languages.auto;
                    const initialId = 'msg-init-' + Date.now();
                    this.messages = [{
                        id: initialId,
                        sender: 'ai',
                        text: initialLang.greeting,
                        suggestions: initialLang.suggestions
                    }];
                    this.detectedLang = activeLoc;
                    this.isRtl = ['ur', 'ar'].includes(activeLoc);

                    window.addEventListener('open-ai-copilot', () => {
                        this.isOpen = true;
                        this.scrollToBottom();
                    });

                    window.addEventListener('ai-agent-reset', () => {
                        this.clearChat();
                    });
                },

                get isRtlLayout() {
                    const docDir = (document.documentElement.getAttribute('dir') || '').toLowerCase();
                    const docLang = (document.documentElement.getAttribute('lang') || '').toLowerCase();
                    if (docDir === 'rtl' || ['ur', 'ar'].includes(docLang)) {
                        return true;
                    }
                    if (docDir === 'ltr' || ['en', 'hi', 'tr', 'es', 'fr', 'de', 'zh'].includes(docLang)) {
                        return false;
                    }
                    return this.isPageRtl;
                },

                get currentLangObj() {
                    const langKey = this.pageLocale || '{{ $activeLocale }}';
                    if (this.languages[langKey]) {
                        return this.languages[langKey];
                    }
                    return this.languages.en || this.languages.auto;
                },

                /**
                 * Collect Real-Time Screen & Entity Context
                 */
                collectScreenContext() {
                    const path = window.location.pathname;
                    let entityType = null;
                    let entityId = null;

                    const invMatch = path.match(/\/invoices\/([a-zA-Z0-9\-]+)/);
                    if (invMatch && invMatch[1] !== 'create') {
                        entityType = 'invoice';
                        entityId = invMatch[1];
                    } else if (path.includes('/invoices/create')) {
                        entityType = 'invoice_builder';
                    } else if (path.includes('/clients')) {
                        entityType = 'client_crm';
                    } else if (path.includes('/support')) {
                        entityType = 'support_ticket';
                    } else if (path.includes('/settings')) {
                        entityType = 'settings';
                    } else if (path.includes('/help')) {
                        entityType = 'help_center';
                    }

                    return {
                        page_url: window.location.href,
                        page_title: document.title,
                        route_name: path,
                        entity_type: entityType,
                        entity_id: entityId,
                        timestamp: new Date().toISOString()
                    };
                },

                async clearChat() {
                    this.conversationId = null;
                    this.isLoading = false;

                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const token = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                    try {
                        await fetch('{{ route('ai.copilot.clear') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            }
                        });
                    } catch (e) {}

                    const activeLoc = '{{ $activeLocale }}';
                    const langObj = this.languages[activeLoc] || this.currentLangObj;
                    this.detectedLang = activeLoc;
                    this.isRtl = ['ur', 'ar'].includes(activeLoc);

                    this.messages = [{
                        id: 'msg-clear-' + Date.now(),
                        sender: 'ai',
                        text: langObj.greeting,
                        suggestions: langObj.suggestions
                    }];
                    this.scrollToBottom();
                },

                async sendMessage(customText = null) {
                    const text = customText || this.inputMessage.trim();
                    if (!text || this.isLoading) return;

                    const userMsgId = 'msg-user-' + Date.now();
                    this.messages.push({ id: userMsgId, sender: 'user', text: text });
                    this.inputMessage = '';
                    this.isLoading = true;
                    this.scrollToBottom();

                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const token = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                        const res = await fetch('{{ route('ai.copilot.chat') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                message: text,
                                lang: this.pageLocale,
                                context: {
                                    ...this.collectScreenContext(),
                                    page_locale: this.pageLocale,
                                    mode: 'text'
                                }
                            })
                        });

                        if (res.status === 401) {
                            this.messages.push({
                                id: 'err-unauth-' + Date.now(),
                                sender: 'ai',
                                text: 'The AI Support Assistant is exclusively available to logged-in customers. Please log in to your account.',
                                action: { type: 'navigate', url: '{{ route('login') }}', label: 'Log In ➔' }
                            });
                            this.scrollToBottom();
                            this.isLoading = false;
                            return;
                        }

                        if (!res.ok) throw new Error('HTTP ' + res.status);

                        const data = await res.json();
                        if (data.conversation_id) {
                            this.conversationId = data.conversation_id;
                        }
                        if (data.lang_code) {
                            this.detectedLang = (this.pageLocale === 'en' && data.lang_code === 'ur-roman') ? 'en' : data.lang_code;
                        }
                        this.isRtl = this.isPageRtl;

                        const aiMsgId = 'msg-ai-' + Date.now();
                        this.messages.push({
                            id: aiMsgId,
                            sender: 'ai',
                            text: data.reply,
                            action: data.action || null,
                            suggestions: data.suggestions || []
                        });
                        this.scrollToBottom();
                    } catch (e) {
                        console.error('AI Agent error:', e);
                        const fallbackLang = this.currentLangObj;
                        this.messages.push({ 
                            id: 'msg-err-' + Date.now(),
                            sender: 'ai', 
                            text: fallbackLang.greeting, 
                            suggestions: fallbackLang.suggestions 
                        });
                        this.scrollToBottom();
                    } finally {
                        this.isLoading = false;
                        this.scrollToBottom();
                    }
                },

                /**
                 * Helpful / Unhelpful Feedback Submission
                 */
                async rateFeedback(msg, rating) {
                    if (this.ratedMessages[msg.id]) return;
                    this.ratedMessages[msg.id] = rating;

                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const token = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                        await fetch('{{ route('ai.copilot.feedback') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                conversation_id: this.conversationId,
                                rating: rating
                            })
                        });
                    } catch (e) {
                        console.warn('Feedback submit error:', e);
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('ai-chat-body');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                }
            }));
        } else {
            document.addEventListener('alpine:init', initAiAgent, { once: true });
        }
    }
    initAiAgent();
})();
</script>

<!-- DYNAMIC RTL / LTR ROOT CONTAINER -->
<!-- When RTL (Urdu, Arabic), widget moves to LEFT side. When LTR (English), stays on RIGHT side. -->
<div x-data="aiAgentComponent" 
     :dir="isRtlLayout ? 'rtl' : 'ltr'"
     class="ai-agent-root-wrapper"
     :style="isRtlLayout 
        ? 'position: fixed !important; bottom: 24px !important; left: 24px !important; right: auto !important; z-index: 9999 !important; direction: rtl !important;' 
        : 'position: fixed !important; bottom: 24px !important; right: 24px !important; left: auto !important; z-index: 9999 !important; direction: ltr !important;'">

    <!-- Floating Trigger Button -->
    <div x-show="!isOpen" x-cloak>
        <button @click="isOpen = true; scrollToBottom()" type="button"
                style="font-weight: 700;"
                class="group flex items-center gap-2.5 px-5 py-3.5 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white rounded-full shadow-2xl transition duration-300 transform hover:scale-105 border-2 border-white/20">
            <span class="text-xs font-bold tracking-wider flex items-center gap-2">
                <i class="ph ph-chat-circle-dots text-base text-white"></i>
                <span class="font-bold" x-text="isRtlLayout ? 'AI سپورٹ اسسٹنٹ' : 'AI Support Assistant'"></span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-ping"></span>
            </span>
        </button>
    </div>

    <!-- Active Chat Modal (Flipped layout for RTL/LTR) -->
    <div x-show="isOpen" x-cloak
         :dir="isRtlLayout ? 'rtl' : 'ltr'"
         class="w-[360px] sm:w-[460px] h-[600px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200">
        
        <!-- Header (Clean, Zero Clutter) -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white p-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-red-600/30 border border-red-500/40 flex items-center justify-center text-red-400">
                    <i class="ph ph-robot text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5" style="font-weight: 700;">
                        <span x-text="isRtlLayout ? 'AI سپورٹ اسسٹنٹ' : 'AI Support Assistant'"></span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                    </h3>
                    <p class="text-[10px] text-slate-300 font-bold flex items-center gap-1.5" style="font-weight: 700;">
                        <span x-text="isRtlLayout ? 'ذہین کسٹمر سپورٹ' : 'Intelligent Support Agent'"></span>
                        <span class="text-emerald-400">• Online</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <!-- Reset Chat Session -->
                <button @click="clearChat()" type="button" title="New Conversation / Clear Context"
                        class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </button>

                <!-- Close Modal -->
                <button @click="isOpen = false" type="button" class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
        </div>

        <!-- Chat Conversation Messages (With dynamic RTL / LTR formatting) -->
        <div id="ai-chat-body" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/60 text-xs">
            <template x-for="(msg, idx) in messages" :key="msg.id || idx">
                <div :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
                    <div :class="msg.sender === 'user' ? 'bg-red-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs shadow-2xs'"
                         :dir="isRtlLayout ? 'rtl' : 'ltr'"
                         class="max-w-[88%] rounded-2xl p-3.5 space-y-2 text-left rtl:text-right">

                        <div x-text="msg.text" 
                             :class="isRtlLayout ? 'text-right' : 'text-left'"
                             class="leading-relaxed font-bold whitespace-pre-line" style="font-weight: 700;"></div>
                        
                        <!-- Interactive Action Button (Step-by-step navigation) -->
                        <template x-if="msg.action && msg.action.url">
                            <div class="pt-2">
                                <a :href="msg.action.url" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl font-bold text-xs shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5"
                                   style="font-weight: 700;">
                                    <i class="ph ph-arrow-square-out text-sm"></i>
                                    <span x-text="msg.action.label || (isRtlLayout ? 'کھولیں اور آگے بڑھیں ➔' : 'Open & Proceed ➔')"></span>
                                </a>
                            </div>
                        </template>

                        <!-- Helpful / Unhelpful Feedback for AI Messages -->
                        <template x-if="msg.sender === 'ai'">
                            <div class="pt-2 flex items-center justify-end border-t border-slate-100 text-[10px]">
                                <!-- Feedback Rating Buttons (Helpful / Unhelpful) -->
                                <div class="flex items-center gap-1">
                                    <template x-if="!ratedMessages[msg.id]">
                                        <div class="flex items-center gap-1">
                                            <button @click="rateFeedback(msg, 1)" type="button" 
                                                    class="p-1 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Helpful">
                                                <i class="ph ph-thumbs-up text-xs"></i>
                                            </button>
                                            <button @click="rateFeedback(msg, -1)" type="button" 
                                                    class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Unhelpful">
                                                <i class="ph ph-thumbs-down text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="ratedMessages[msg.id] === 1">
                                        <span class="text-emerald-600 font-bold text-[9px] flex items-center gap-0.5">
                                            <i class="ph ph-check-circle"></i> <span x-text="isRtlLayout ? 'مفید' : 'Helpful'"></span>
                                        </span>
                                    </template>
                                    <template x-if="ratedMessages[msg.id] === -1">
                                        <span class="text-rose-500 font-bold text-[9px] flex items-center gap-0.5">
                                            <i class="ph ph-x-circle"></i> <span x-text="isRtlLayout ? 'شکریہ' : 'Thanks for feedback'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Suggestion Pills -->
                        <template x-if="msg.suggestions && msg.suggestions.length > 0">
                            <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5" :dir="isRtlLayout ? 'rtl' : 'ltr'">
                                <template x-for="sug in msg.suggestions" :key="sug">
                                    <button @click="sendMessage(sug)" type="button"
                                            style="font-weight: 700;"
                                            class="px-2.5 py-1 rounded-full bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-700 text-[10px] font-bold border border-slate-200 transition">
                                        <span x-text="sug"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Loading Typing Indicator -->
            <div x-show="isLoading" x-cloak class="flex justify-start">
                <div class="bg-white border border-slate-200 text-slate-500 rounded-2xl rounded-tl-xs p-3 shadow-2xs flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-bounce"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-bounce [animation-delay:0.4s]"></span>
                    <span class="text-[10px] font-bold text-slate-400 ml-1" x-text="isRtlLayout ? 'سوچ رہا ہے...' : 'Thinking...'"></span>
                </div>
            </div>
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-white border-t border-slate-200">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input type="text" x-model="inputMessage" 
                       :disabled="isLoading"
                       :dir="isRtlLayout ? 'rtl' : 'ltr'"
                       :placeholder="currentLangObj ? currentLangObj.placeholder : 'Ask AI Support Assistant...'"
                       style="font-weight: 700;"
                       class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 transition">

                <button type="submit" 
                        :disabled="isLoading || !inputMessage.trim()"
                        class="w-10 h-10 rounded-2xl bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:hover:bg-red-600 text-white flex items-center justify-center shrink-0 transition shadow-sm">
                    <i class="ph ph-paper-plane-tilt text-base" :class="isRtlLayout ? 'transform rotate-180' : ''"></i>
                </button>
            </form>
        </div>

    </div>
</div>
@endif
