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
    $speechLangTag = match($activeLocale) {
        'ur' => 'ur-PK',
        'ar' => 'ar-SA',
        'hi' => 'hi-IN',
        'tr' => 'tr-TR',
        'es' => 'es-ES',
        'fr' => 'fr-FR',
        'de' => 'de-DE',
        'zh' => 'zh-CN',
        default => 'en-US'
    };
    $isPortalRtl = in_array($activeLocale, ['ur', 'ar']);
@endphp

@if(!$user || (!$isTenantAiActive && !$isSuperAdminUser))
    {{-- AI Assistant only available after login to customer / authorized user --}}
@else
<style>
@keyframes ai-wave-bar {
    0%, 100% { height: 6px; }
    50% { height: 22px; }
}
.ai-wave-1 { animation: ai-wave-bar 0.7s ease-in-out infinite; }
.ai-wave-2 { animation: ai-wave-bar 0.7s ease-in-out 0.15s infinite; }
.ai-wave-3 { animation: ai-wave-bar 0.7s ease-in-out 0.3s infinite; }
.ai-wave-4 { animation: ai-wave-bar 0.7s ease-in-out 0.45s infinite; }
.ai-wave-5 { animation: ai-wave-bar 0.7s ease-in-out 0.2s infinite; }

@keyframes ai-pulse-ring {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 0.3; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
.ai-ring-pulse { animation: ai-pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>

<script>
(function() {
    function initAiAgent() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('aiAgentComponent', () => ({
                isOpen: false,
                activeMode: 'text', // 'text' | 'voice'
                voiceState: 'idle', // 'idle' | 'connecting' | 'listening' | 'processing' | 'speaking' | 'interrupted' | 'muted' | 'error'
                isListening: false,
                isSpeaking: false,
                isMuted: false,
                soundEnabled: true,
                pageLocale: '{{ $activeLocale }}',
                isPageRtl: {{ $isPortalRtl ? 'true' : 'false' }},
                detectedLang: '{{ $activeLocale }}',
                selectedLang: '{{ $activeLocale }}',
                isRtl: {{ $isPortalRtl ? 'true' : 'false' }},
                speechLang: '{{ $speechLangTag }}',
                inputMessage: '',
                interimTranscript: '',
                currentlySpeakingMsgId: null,
                currentUtterance: null,
                _watchdogTimer: null,
                _vadSilenceTimer: null,
                _vadSilenceThresholdMs: 1200, // 1.2s of silence commits utterance (Voice Activity Detection)
                _accumulatedTranscript: '',
                _voiceInterval: null,
                voiceSessionToken: null,
                voiceDurationSeconds: 0,
                conversationId: null,
                availableVoices: [],
                recognition: null,
                synth: window.speechSynthesis || null,
                ratedMessages: {},

                languages: {
                    auto: { 
                        code: 'auto', 
                        tag: 'en-US', 
                        placeholder: 'Ask in any language or click mic to talk...', 
                        greeting: 'Hello! I am your Invoicy AI Assistant. Ask me anything in English, Urdu, Roman Urdu, Arabic, Hindi, Turkish, French, Spanish, or click mic for voice support!', 
                        suggestions: ['Talk to AI (Voice)', 'Create new invoice', 'How do drafts work?', 'Open Support Desk'] 
                    },
                    ur: { 
                        code: 'ur', 
                        tag: 'ur-PK', 
                        placeholder: 'اردو میں سوال پوچھیں یا مائیک دبائیں...', 
                        greeting: 'السلام علیکم! میں آپ کا AI ایجنٹ ہوں۔ انوائس بنانے، نجی ڈرافٹس، ریونیو اعدادوشمار یا سپورٹ کے بارے میں پوچھیں یا وائس میں بات کریں!', 
                        suggestions: ['وائس کال شروع کریں 🎙️', 'نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'سپورٹ ڈیسک کھولیں'] 
                    },
                    'ur-roman': { 
                        code: 'ur-roman', 
                        tag: 'ur-PK', 
                        placeholder: 'Roman Urdu me sawaal poochein ya mic click karein...', 
                        greeting: 'Hello! Main aap ka Invoicy AI Agent hoon. Naya invoice banane, 45+ presets, drafts, balance & stats ya support desk ke bare me poochein!', 
                        suggestions: ['Talk to AI (Voice)', 'Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Mera Balance & Stats'] 
                    },
                    ar: { 
                        code: 'ar', 
                        tag: 'ar-SA', 
                        placeholder: 'اسأل الوكيل الذكي أو اضغط المايك...', 
                        greeting: 'مرحباً! أنا وكيل الذكاء الاصطناعي لمنصة Invoicy. اسألني عن فواتيرك، المسودات، الإعدادات، أو اضغط المايك للأوامر الصوتية!', 
                        suggestions: ['بدء محادثة صوتية 🎙️', 'إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'فتح مكتب الدعم'] 
                    },
                    en: { 
                        code: 'en', 
                        tag: 'en-US', 
                        placeholder: 'Ask AI Agent or click mic to speak...', 
                        greeting: 'Hello! I am your AI Agent. Ask me anything about your invoices, settings, draft safeguards, support tickets, or click the mic for two-way voice support!', 
                        suggestions: ['Talk to AI (Voice)', 'Create new invoice', 'How do drafts work?', 'Open Support Desk'] 
                    },
                    tr: { 
                        code: 'tr', 
                        tag: 'tr-TR', 
                        placeholder: 'AI Asistanına sor veya sesli konuş...', 
                        greeting: 'Merhaba! Ben Invoicy AI Asistanınızım. Faturalarınız, taslaklar, ayarlar veya destek masası hakkında soru sorabilir veya mikrofon ile konuşabilirsiniz!', 
                        suggestions: ['Sesli Konuşma Başlat 🎙️', 'Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Destek Masası Aç'] 
                    },
                    hi: { 
                        code: 'hi', 
                        tag: 'hi-IN', 
                        placeholder: 'AI से पूछें या माइक पर बोलें...', 
                        greeting: 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉइस, ड्राफ्ट, सेटिंग्स या सपोर्ट کے بارے میں پوچھیں یا हिंदी में वॉइस कमांड दें!', 
                        suggestions: ['वॉइस सपोर्ट शुरू करें 🎙️', 'नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'सपोर्ट डेस्क खोलें'] 
                    },
                    es: { 
                        code: 'es', 
                        tag: 'es-ES', 
                        placeholder: 'Pregunta a la IA o usa el micrófono...', 
                        greeting: '¡Hola! Soy tu Agente IA de Invoicy. Pregúntame sobre facturas, borradores, soporte o habla en español usando el micrófono.', 
                        suggestions: ['Iniciar llamada de voz 🎙️', 'Crear nueva factura', '¿Cómo funcionan los borradores?', 'Abrir soporte'] 
                    },
                    fr: { 
                        code: 'fr', 
                        tag: 'fr-FR', 
                        placeholder: 'Demandez à l\'IA ou parlez au micro...', 
                        greeting: 'Bonjour ! Je suis votre Agent IA Invoicy. Posez-moi des questions sur vos factures, brouillons, ou cliquez sur le micro pour parler en français.', 
                        suggestions: ['Démarrer session vocale 🎙️', 'Créer une facture', 'Fonctionnement des brouillons', 'Ouvrir le support'] 
                    },
                    de: { 
                        code: 'de', 
                        tag: 'de-DE', 
                        placeholder: 'Frage an KI oder Mikrofon nutzen...', 
                        greeting: 'Hallo! Ich bin Ihr Invoicy KI-Agent. Fragen Sie mich nach Rechnungen, Entwürfen oder sprechen Sie auf Deutsch mit dem Mikrofon.', 
                        suggestions: ['Sprachmodus starten 🎙️', 'Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Support öffnen'] 
                    },
                    zh: { 
                        code: 'zh', 
                        tag: 'zh-CN', 
                        placeholder: '向 AI 提问或点击麦克风...', 
                        greeting: '您好！我是 Invoicy AI 助手。向我询问有关发票、草稿、设置或支持的问题，或使用双向语音！', 
                        suggestions: ['开启语音对话 🎙️', '创建新发票', '草稿如何运作？', '打开支持'] 
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
                    this.speechLang = initialLang.tag || '{{ $speechLangTag }}';
                    this.isRtl = ['ur', 'ar'].includes(activeLoc);

                    this.loadVoices();
                    if (this.synth && this.synth.onvoiceschanged !== undefined) {
                        this.synth.onvoiceschanged = () => { this.loadVoices(); };
                    }

                    this.initSpeech();

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

                get formattedVoiceDuration() {
                    const mins = Math.floor(this.voiceDurationSeconds / 60);
                    const secs = this.voiceDurationSeconds % 60;
                    return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                },

                loadVoices() {
                    if (this.synth) {
                        this.availableVoices = this.synth.getVoices() || [];
                    }
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

                /**
                 * Native Voice Selector.
                 */
                getVoiceForLang(langTag) {
                    if (!this.synth) return null;
                    let voices = this.availableVoices || [];
                    if (!voices.length) {
                        voices = this.synth.getVoices() || [];
                        this.availableVoices = voices;
                    }
                    if (!voices.length) return null;

                    const target = (langTag || 'en-US').toLowerCase();
                    const prefix = target.split('-')[0];

                    if (prefix === 'hi') {
                        const v = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('hi') || n.includes('hindi') || n.includes('हिन्दी') || n.includes('kalpana') || n.includes('hemant') || n.includes('swara') || n.includes('madhur');
                        });
                        if (v) return v;
                    }

                    if (prefix === 'ur') {
                        const v = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('ur') || n.includes('urdu') || n.includes('اردو') || n.includes('asad') || n.includes('uzma');
                        });
                        if (v) return v;

                        const hindiFallback = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('hi') || n.includes('hindi') || n.includes('kalpana') || n.includes('hemant');
                        });
                        if (hindiFallback) return hindiFallback;
                    }

                    if (prefix === 'ar') {
                        const v = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('ar') || n.includes('arabic') || n.includes('العربية') || n.includes('naayf') || n.includes('salma') || n.includes('shakir') || n.includes('zeina') || n.includes('tarik');
                        });
                        if (v) return v;
                    }

                    if (prefix === 'tr') {
                        const v = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('tr') || n.includes('turkish') || n.includes('türkçe') || n.includes('emre') || n.includes('filiz') || n.includes('ahmet');
                        });
                        if (v) return v;
                    }

                    if (prefix === 'es') {
                        const v = voices.find(v => (v.lang || '').toLowerCase().startsWith('es'));
                        if (v) return v;
                    }

                    if (prefix === 'fr') {
                        const v = voices.find(v => (v.lang || '').toLowerCase().startsWith('fr'));
                        if (v) return v;
                    }

                    if (prefix === 'de') {
                        const v = voices.find(v => (v.lang || '').toLowerCase().startsWith('de'));
                        if (v) return v;
                    }

                    if (prefix === 'zh') {
                        const v = voices.find(v => (v.lang || '').toLowerCase().startsWith('zh'));
                        if (v) return v;
                    }

                    let match = voices.find(v => v.lang && v.lang.toLowerCase() === target);
                    if (match) return match;

                    match = voices.find(v => v.lang && v.lang.toLowerCase().startsWith(prefix));
                    if (match) return match;

                    const enNatural = voices.find(v => {
                        const n = (v.name || '').toLowerCase();
                        return n.includes('natural') || n.includes('neural') || n.includes('google us');
                    });
                    if (enNatural) return enNatural;

                    return voices.find(v => v.default) || voices[0] || null;
                },

                /**
                 * Initialize Speech Recognition with Real-Time VAD (Voice Activity Detection)
                 */
                initSpeech() {
                    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                        try {
                            this.recognition = new SpeechRec();
                            this.recognition.continuous = true;
                            this.recognition.interimResults = true;
                            this.recognition.lang = this.speechLang || 'en-US';

                            this.recognition.onstart = () => {
                                this.isListening = true;
                                if (this.activeMode === 'voice') {
                                    this.voiceState = 'listening';
                                }
                            };

                            // INSTANT BARGE-IN / INTERRUPTION:
                            // The moment user starts speaking, halt AI speech synthesis immediately!
                            this.recognition.onspeechstart = () => {
                                if (this.isSpeaking) {
                                    this.stopSpeech(true); // interrupted = true
                                    this.voiceState = 'interrupted';
                                    setTimeout(() => {
                                        if (this.activeMode === 'voice') {
                                            this.voiceState = 'listening';
                                        }
                                    }, 350);
                                }
                            };

                            // VAD (Voice Activity Detection): Sliding silence detection window
                            this.recognition.onresult = (event) => {
                                if (this.isSpeaking) {
                                    this.stopSpeech(true);
                                }

                                let interim = '';
                                let newFinal = '';

                                for (let i = event.resultIndex; i < event.results.length; ++i) {
                                    if (event.results[i].isFinal) {
                                        newFinal += event.results[i][0].transcript;
                                    } else {
                                        interim += event.results[i][0].transcript;
                                    }
                                }

                                if (newFinal) {
                                    this._accumulatedTranscript = (this._accumulatedTranscript + ' ' + newFinal).trim();
                                }

                                const currentSpeech = (this._accumulatedTranscript + ' ' + interim).trim();
                                this.interimTranscript = currentSpeech;

                                // Reset silence timer on ANY incoming speech chunk
                                if (this._vadSilenceTimer) {
                                    clearTimeout(this._vadSilenceTimer);
                                    this._vadSilenceTimer = null;
                                }

                                // Arm VAD Silence Timer: Fires after 1200ms of silence
                                if (currentSpeech.length > 0) {
                                    this._vadSilenceTimer = setTimeout(() => {
                                        const committedText = (this._accumulatedTranscript + ' ' + interim).trim();
                                        this._accumulatedTranscript = '';
                                        this.interimTranscript = '';
                                        this._vadSilenceTimer = null;

                                        if (committedText.length > 0) {
                                            if (this.activeMode === 'voice') {
                                                this.handleVoiceUtterance(committedText);
                                            } else {
                                                this.inputMessage = committedText;
                                                this.sendMessage();
                                            }
                                        }
                                    }, this._vadSilenceThresholdMs);
                                }
                            };

                            this.recognition.onerror = (event) => {
                                console.warn('Speech recognition notice:', event.error);
                                if (event.error === 'not-allowed') {
                                    this.isListening = false;
                                    this.voiceState = 'error';
                                    this.messages.push({
                                        id: 'err-' + Date.now(),
                                        sender: 'ai',
                                        text: 'Microphone permission was not allowed. Please click the lock icon in your browser address bar to allow microphone access.'
                                    });
                                    this.scrollToBottom();
                                } else if (event.error === 'no-speech' && this.activeMode === 'voice' && !this.isMuted) {
                                    // Normal silence; keep duplex loop open
                                }
                            };

                            this.recognition.onend = () => {
                                this.isListening = false;
                                // Auto-restart in duplex voice mode if not processing/speaking and not muted
                                if (this.activeMode === 'voice' && this.voiceState !== 'processing' && this.voiceState !== 'speaking' && !this.isMuted) {
                                    setTimeout(() => {
                                        try {
                                            if (this.activeMode === 'voice' && !this.isMuted && this.voiceState !== 'speaking' && this.voiceState !== 'processing') {
                                                this.recognition.start();
                                            }
                                        } catch (e) {}
                                    }, 250);
                                }
                            };
                        } catch (e) {
                            console.warn('Speech recognition init error:', e);
                        }
                    }
                },

                /**
                 * Two-Way Real-Time Voice Mode Engine
                 */
                async startVoiceMode() {
                    this.stopSpeech();
                    this.activeMode = 'voice';
                    this.voiceState = 'connecting';
                    this.isMuted = false;
                    this.voiceDurationSeconds = 0;
                    this._accumulatedTranscript = '';
                    this.interimTranscript = '';

                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const token = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                        const res = await fetch('{{ route('ai.copilot.voice.session') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                lang: this.detectedLang,
                                context: this.collectScreenContext()
                            })
                        });

                        if (res.status === 401) {
                            this.voiceState = 'idle';
                            this.activeMode = 'text';
                            this.messages.push({
                                id: 'err-unauth-' + Date.now(),
                                sender: 'ai',
                                text: 'The AI Support Assistant is only available to logged-in customers. Please log in to your account.',
                                action: { type: 'navigate', url: '{{ route('login') }}', label: 'Log In ➔' }
                            });
                            this.scrollToBottom();
                            return;
                        }

                        const data = await res.json();
                        if (data.success) {
                            this.voiceSessionToken = data.session_token;
                            this.conversationId = data.conversation_id;

                            // Start duration timer
                            if (this._voiceInterval) clearInterval(this._voiceInterval);
                            this._voiceInterval = setInterval(() => {
                                this.voiceDurationSeconds++;
                            }, 1000);

                            this.startListeningContinuous();
                        } else {
                            this.voiceState = 'error';
                        }
                    } catch (e) {
                        console.error('Voice session creation error:', e);
                        this.startListeningContinuous(); // Fallback to local duplex
                    }
                },

                startListeningContinuous() {
                    if (this.isMuted) {
                        this.voiceState = 'muted';
                        return;
                    }

                    if (!this.recognition) {
                        this.initSpeech();
                    }

                    if (this.recognition) {
                        try {
                            this.recognition.lang = this.speechLang || 'en-US';
                            this.recognition.start();
                            this.voiceState = 'listening';
                            this.isListening = true;
                        } catch (e) {
                            try {
                                this.recognition.abort();
                                setTimeout(() => {
                                    if (this.activeMode === 'voice' && !this.isMuted && this.voiceState !== 'speaking' && this.voiceState !== 'processing') {
                                        try {
                                            this.recognition.start();
                                            this.voiceState = 'listening';
                                            this.isListening = true;
                                        } catch (e2) {}
                                    }
                                }, 250);
                            } catch (e3) {
                                this.voiceState = 'listening';
                            }
                        }
                    }
                },

                toggleMute() {
                    this.isMuted = !this.isMuted;
                    if (this.isMuted) {
                        this.voiceState = 'muted';
                        if (this.recognition) {
                            try { this.recognition.stop(); } catch (e) {}
                        }
                    } else {
                        this.startListeningContinuous();
                    }
                },

                async endVoiceMode() {
                    this.stopSpeech();
                    if (this._vadSilenceTimer) {
                        clearTimeout(this._vadSilenceTimer);
                        this._vadSilenceTimer = null;
                    }
                    this._accumulatedTranscript = '';
                    this.interimTranscript = '';

                    if (this.recognition) {
                        try { this.recognition.stop(); } catch (e) {}
                    }

                    if (this._voiceInterval) {
                        clearInterval(this._voiceInterval);
                        this._voiceInterval = null;
                    }

                    if (this.voiceSessionToken) {
                        try {
                            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                            const token = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                            await fetch('{{ route('ai.copilot.voice.end') }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token
                                },
                                body: JSON.stringify({
                                    session_token: this.voiceSessionToken,
                                    duration_seconds: this.voiceDurationSeconds
                                })
                            });
                        } catch (e) {}
                    }

                    this.activeMode = 'text';
                    this.voiceState = 'idle';
                    this.isListening = false;
                },

                async handleVoiceUtterance(text) {
                    const cleanText = text.trim();
                    if (!cleanText) return;

                    // Stop recognition while processing to avoid self-echo
                    if (this.recognition) {
                        try { this.recognition.stop(); } catch (e) {}
                    }

                    this.voiceState = 'processing';
                    const userMsgId = 'msg-user-v-' + Date.now();
                    this.messages.push({
                        id: userMsgId,
                        sender: 'user',
                        text: cleanText,
                        mode: 'voice'
                    });
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
                                message: cleanText,
                                lang: this.pageLocale,
                                context: {
                                    ...this.collectScreenContext(),
                                    page_locale: this.pageLocale,
                                    mode: 'voice',
                                    session_token: this.voiceSessionToken
                                }
                            })
                        });

                        if (res.status === 401) {
                            this.voiceState = 'idle';
                            this.activeMode = 'text';
                            this.messages.push({
                                id: 'err-unauth-' + Date.now(),
                                sender: 'ai',
                                text: 'The AI Support Assistant is exclusively available to logged-in customers. Please log in to your account.',
                                action: { type: 'navigate', url: '{{ route('login') }}', label: 'Log In ➔' }
                            });
                            this.scrollToBottom();
                            return;
                        }

                        const data = await res.json();
                        if (data.conversation_id) {
                            this.conversationId = data.conversation_id;
                        }
                        if (this.pageLocale === 'en') {
                            this.speechLang = 'en-US';
                        } else if (data.lang) {
                            this.speechLang = data.lang;
                            if (this.recognition) this.recognition.lang = data.lang;
                        }
                        if (data.lang_code) {
                            this.detectedLang = (this.pageLocale === 'en' && data.lang_code === 'ur-roman') ? 'en' : data.lang_code;
                        }
                        this.isRtl = this.isPageRtl;

                        const aiMsgId = 'msg-ai-v-' + Date.now();
                        this.messages.push({
                            id: aiMsgId,
                            sender: 'ai',
                            text: data.reply,
                            mode: 'voice',
                            action: data.action || null,
                            suggestions: data.suggestions || []
                        });
                        this.scrollToBottom();

                        // Speak response verbally with duplex loop auto-resume on completion
                        this.voiceState = 'speaking';
                        this.speak(data.reply, aiMsgId, () => {
                            // On voice synthesis completion, auto-resume listening hands-free!
                            if (this.activeMode === 'voice' && !this.isMuted) {
                                setTimeout(() => {
                                    this.startListeningContinuous();
                                }, 350);
                            } else {
                                this.voiceState = 'idle';
                            }
                        });
                    } catch (e) {
                        console.error('Voice utterance dispatch error:', e);
                        this.voiceState = 'error';
                        setTimeout(() => {
                            if (this.activeMode === 'voice' && !this.isMuted) this.startListeningContinuous();
                        }, 1200);
                    }
                },

                /**
                 * Toggle Voice dictation (Single utterance or Switch to Full Duplex)
                 */
                async toggleVoice() {
                    if (this.activeMode === 'voice') {
                        this.endVoiceMode();
                        return;
                    }
                    this.startVoiceMode();
                },

                /**
                 * Continuous Speech Synthesis Pipeline with OnComplete Callback
                 */
                speak(text, msgId = null, onComplete = null) {
                    if (!this.soundEnabled || !this.synth) {
                        if (onComplete) onComplete();
                        return;
                    }

                    if (this._watchdogTimer) {
                        clearInterval(this._watchdogTimer);
                        this._watchdogTimer = null;
                    }

                    try {
                        if (this.synth.speaking || this.synth.pending) {
                            this.synth.cancel();
                        }
                    } catch (e) {}

                    const cleanText = text
                        .replace(/\*\*(.*?)\*\*/g, '$1')
                        .replace(/\*(.*?)\*/g, '$1')
                        .replace(/•/g, '')
                        .replace(/\[(.*?)\]\(.*?\)/g, '$1')
                        .replace(/[#_~`]/g, '')
                        .trim();

                    if (!cleanText) {
                        if (onComplete) onComplete();
                        return;
                    }

                    setTimeout(() => {
                        try {
                            if (this.synth.paused) {
                                this.synth.resume();
                            }

                            const utter = new SpeechSynthesisUtterance(cleanText);
                            const targetLang = this.speechLang || 'en-US';
                            utter.lang = targetLang;
                            utter.rate = 1.0;
                            utter.pitch = 1.0;

                            const matchedVoice = this.getVoiceForLang(targetLang);
                            if (matchedVoice) {
                                utter.voice = matchedVoice;
                            }

                            window._activeSpeechUtterance = utter;
                            this.currentUtterance = utter;
                            this.currentlySpeakingMsgId = msgId;

                            utter.onstart = () => {
                                this.isSpeaking = true;
                                this.currentlySpeakingMsgId = msgId;
                                if (this.activeMode === 'voice') {
                                    this.voiceState = 'speaking';
                                }

                                this._watchdogTimer = setInterval(() => {
                                    if (this.synth && this.synth.speaking) {
                                        this.synth.pause();
                                        this.synth.resume();
                                    } else {
                                        clearInterval(this._watchdogTimer);
                                    }
                                }, 8000);
                            };

                            utter.onend = () => {
                                this.isSpeaking = false;
                                this.currentlySpeakingMsgId = null;
                                window._activeSpeechUtterance = null;
                                this.currentUtterance = null;
                                if (this._watchdogTimer) {
                                    clearInterval(this._watchdogTimer);
                                    this._watchdogTimer = null;
                                }
                                if (onComplete) onComplete();
                            };

                            utter.onerror = (err) => {
                                this.isSpeaking = false;
                                this.currentlySpeakingMsgId = null;
                                window._activeSpeechUtterance = null;
                                this.currentUtterance = null;
                                if (this._watchdogTimer) {
                                    clearInterval(this._watchdogTimer);
                                    this._watchdogTimer = null;
                                }
                                if (onComplete) onComplete();
                            };

                            this.synth.speak(utter);
                        } catch (e) {
                            console.error('Speech synthesis failure:', e);
                            this.isSpeaking = false;
                            this.currentlySpeakingMsgId = null;
                            if (onComplete) onComplete();
                        }
                    }, 60);
                },

                speakMessage(msg) {
                    if (!msg || !msg.text) return;
                    if (this.currentlySpeakingMsgId === msg.id && this.isSpeaking) {
                        this.stopSpeech();
                        return;
                    }
                    this.soundEnabled = true;
                    this.speak(msg.text, msg.id);
                },

                stopSpeech(isInterrupted = false) {
                    if (this.synth) {
                        try { this.synth.cancel(); } catch (e) {}
                    }
                    this.isSpeaking = false;
                    this.currentlySpeakingMsgId = null;
                    window._activeSpeechUtterance = null;
                    this.currentUtterance = null;
                    if (this._watchdogTimer) {
                        clearInterval(this._watchdogTimer);
                        this._watchdogTimer = null;
                    }
                    if (isInterrupted && this.activeMode === 'voice') {
                        this.voiceState = 'interrupted';
                    }
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    if (!this.soundEnabled) {
                        this.stopSpeech();
                    }
                },

                async clearChat() {
                    this.stopSpeech();
                    if (this.isListening && this.recognition) {
                        try { this.recognition.stop(); } catch(e) {}
                        this.isListening = false;
                    }
                    this.voiceState = 'idle';
                    this.interimTranscript = '';
                    this._accumulatedTranscript = '';
                    this.conversationId = null;

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
                    this.speechLang = langObj.tag || '{{ $speechLangTag }}';
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
                    if (!text) return;

                    // If user clicks a "Talk to AI" pill
                    if (text.includes('Talk to AI') || text.includes('وائس') || text.includes('صوتية') || text.includes('Sesli')) {
                        this.startVoiceMode();
                        return;
                    }

                    this.stopSpeech();

                    const userMsgId = 'msg-user-' + Date.now();
                    this.messages.push({ id: userMsgId, sender: 'user', text: text, mode: 'text' });
                    this.inputMessage = '';
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
                            return;
                        }

                        if (!res.ok) throw new Error('HTTP ' + res.status);

                        const data = await res.json();
                        if (data.conversation_id) {
                            this.conversationId = data.conversation_id;
                        }
                        if (this.pageLocale === 'en') {
                            this.speechLang = 'en-US';
                        } else if (data.lang) {
                            this.speechLang = data.lang;
                            if (this.recognition) this.recognition.lang = data.lang;
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
                            mode: 'text',
                            action: data.action || null,
                            suggestions: data.suggestions || []
                        });
                        this.scrollToBottom();

                        // Play audio in text mode if sound enabled
                        this.speak(data.reply, aiMsgId);
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
                <i class="ph ph-microphone text-base text-white"></i>
                <span class="font-bold" x-text="isRtlLayout ? 'AI سپورٹ اور وائس' : 'AI Support & Voice'"></span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-ping"></span>
            </span>
        </button>
    </div>

    <!-- Active Chat & Voice Modal (Flipped layout for RTL/LTR) -->
    <div x-show="isOpen" x-cloak
         :dir="isRtlLayout ? 'rtl' : 'ltr'"
         class="w-[360px] sm:w-[460px] h-[620px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200">
        
        <!-- Header (Clean, Zero Clutter with Duplex Mode Switcher) -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white p-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-red-600/30 border border-red-500/40 flex items-center justify-center text-red-400">
                    <i :class="activeMode === 'voice' ? 'ph ph-waveform' : 'ph ph-robot'" class="text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5" style="font-weight: 700;">
                        <span x-text="isRtlLayout ? 'AI سپورٹ اسسٹنٹ' : 'AI Support Assistant'"></span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                    </h3>
                    <p class="text-[10px] text-slate-300 font-bold flex items-center gap-1.5" style="font-weight: 700;">
                        <span x-text="activeMode === 'voice' ? (isRtlLayout ? 'دو طرفہ وائس کال' : 'Two-Way Duplex Voice') : (isRtlLayout ? 'ذہین کسٹمر سپورٹ' : 'Intelligent Support Agent')"></span>
                        <span class="text-emerald-400">• Online</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <!-- Two-Way Voice Mode Launcher Button in Header -->
                <button @click="activeMode === 'voice' ? endVoiceMode() : startVoiceMode()" type="button" 
                        :class="activeMode === 'voice' ? 'bg-emerald-500 text-white ring-2 ring-emerald-300 animate-pulse' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
                        class="px-2.5 py-1 rounded-xl text-[10px] font-bold flex items-center gap-1.5 transition"
                        :title="activeMode === 'voice' ? 'End Voice Mode' : 'Start Two-Way Voice Support'">
                    <i class="ph ph-microphone text-xs"></i>
                    <span x-text="activeMode === 'voice' ? (isRtlLayout ? 'وائس جاری ہے' : 'Voice Active') : (isRtlLayout ? 'بات کریں' : 'Talk to AI')"></span>
                </button>

                <!-- Reset Chat Session -->
                <button @click="clearChat()" type="button" title="New Conversation / Clear Context"
                        class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </button>

                <!-- Mute / Unmute Voice Output -->
                <button @click="toggleSound()" type="button" :title="soundEnabled ? 'Mute AI Audio' : 'Unmute AI Audio'"
                        class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i :class="soundEnabled ? 'ph ph-speaker-high' : 'ph ph-speaker-slash text-rose-400'" class="text-base"></i>
                </button>

                <!-- Close Modal -->
                <button @click="isOpen = false" type="button" class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
        </div>

        <!-- VOICE CONVERSATION BANNER (Visual State Pill + Waveform + Controls) -->
        <div x-show="activeMode === 'voice'" x-cloak class="bg-slate-900 border-b border-slate-800 p-3 text-white">
            <div class="flex items-center justify-between">
                <!-- Visual State Pill -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 h-5 px-2.5 py-1 rounded-full text-[10px] font-bold"
                         :class="{
                             'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': voiceState === 'listening',
                             'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30': voiceState === 'processing',
                             'bg-purple-500/20 text-purple-300 border border-purple-500/30': voiceState === 'speaking',
                             'bg-amber-500/20 text-amber-300 border border-amber-500/30': voiceState === 'interrupted',
                             'bg-slate-800 text-slate-400 border border-slate-700': voiceState === 'muted' || voiceState === 'idle',
                             'bg-rose-500/20 text-rose-300 border border-rose-500/30': voiceState === 'error'
                         }">
                        
                        <!-- Animated Waveform for Listening & Speaking -->
                        <template x-if="voiceState === 'listening' || voiceState === 'speaking'">
                            <div class="flex items-center gap-0.5 h-3 mr-1 ml-1">
                                <span class="w-0.5 bg-current rounded-full ai-wave-1"></span>
                                <span class="w-0.5 bg-current rounded-full ai-wave-2"></span>
                                <span class="w-0.5 bg-current rounded-full ai-wave-3"></span>
                                <span class="w-0.5 bg-current rounded-full ai-wave-4"></span>
                                <span class="w-0.5 bg-current rounded-full ai-wave-5"></span>
                            </div>
                        </template>

                        <span x-text="
                            voiceState === 'connecting' ? (isRtlLayout ? 'رابطہ ہو رہا ہے...' : 'Connecting...') :
                            voiceState === 'listening' ? (isRtlLayout ? 'سن رہا ہوں... بولیں' : 'Listening... Speak naturally') :
                            voiceState === 'processing' ? (isRtlLayout ? 'پروسیسنگ...' : 'Processing...') :
                            voiceState === 'speaking' ? (isRtlLayout ? 'بول رہا ہوں...' : 'Speaking (barge-in ready)') :
                            voiceState === 'interrupted' ? (isRtlLayout ? 'روک دیا گیا' : 'Interrupted') :
                            voiceState === 'muted' ? (isRtlLayout ? 'مائیک میوٹ ہے' : 'Microphone Muted') :
                            voiceState === 'error' ? (isRtlLayout ? 'خرابی' : 'Connection Error') : 'Ready'
                        "></span>
                    </div>

                    <span class="text-[11px] font-mono text-slate-400 font-bold" x-text="formattedVoiceDuration"></span>
                </div>

                <!-- Voice Call Controls -->
                <div class="flex items-center gap-1.5">
                    <!-- Mute Mic Toggle -->
                    <button @click="toggleMute()" type="button" 
                            :class="isMuted ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-300'"
                            class="p-1.5 rounded-lg text-xs transition" 
                            :title="isMuted ? 'Unmute Microphone' : 'Mute Microphone'">
                        <i :class="isMuted ? 'ph ph-microphone-slash' : 'ph ph-microphone'" class="text-sm"></i>
                    </button>

                    <!-- End Call -->
                    <button @click="endVoiceMode()" type="button" 
                            class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] flex items-center gap-1 transition shadow-sm">
                        <i class="ph ph-phone-disconnect text-xs"></i>
                        <span x-text="isRtlLayout ? 'کال ختم' : 'End Voice'"></span>
                    </button>
                </div>
            </div>

            <!-- Live Speech Interruption Notification or Interim Transcript -->
            <div x-show="interimTranscript" x-cloak class="mt-2 text-[11px] text-slate-300 bg-slate-950/50 p-2 rounded-xl border border-slate-800 font-mono truncate">
                <span class="text-emerald-400 font-bold" x-text="isRtlLayout ? 'آپ: ' : 'You: '"></span>
                <span x-text="interimTranscript"></span>
            </div>
        </div>

        <!-- Chat Conversation Messages (With dynamic RTL / LTR formatting) -->
        <div id="ai-chat-body" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/60 text-xs">
            <template x-for="(msg, idx) in messages" :key="msg.id || idx">
                <div :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
                    <div :class="msg.sender === 'user' ? 'bg-red-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs shadow-2xs'"
                         :dir="isRtlLayout ? 'rtl' : 'ltr'"
                         class="max-w-[88%] rounded-2xl p-3.5 space-y-2 text-left rtl:text-right">
                        
                        <!-- Header Pill inside Message (Voice tag) -->
                        <template x-if="msg.mode === 'voice'">
                            <div class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider mb-1"
                                 :class="msg.sender === 'user' ? 'text-red-200' : 'text-emerald-600'">
                                <i class="ph ph-waveform"></i>
                                <span x-text="isRtlLayout ? 'وائس میسج' : 'Voice Utterance'"></span>
                            </div>
                        </template>

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

                        <!-- Audio Controls & Helpful Feedback for AI Messages -->
                        <template x-if="msg.sender === 'ai'">
                            <div class="pt-2 flex items-center justify-between border-t border-slate-100 text-[10px]">
                                <div class="flex items-center gap-2">
                                    <template x-if="currentlySpeakingMsgId === msg.id && isSpeaking">
                                        <div class="flex items-center gap-1.5 text-red-600 font-bold animate-pulse">
                                            <i class="ph ph-waveform text-sm"></i>
                                            <span x-text="isRtlLayout ? 'بول رہا ہوں...' : 'Speaking...'"></span>
                                            <button @click="stopSpeech()" type="button" class="ml-1 text-[9px] uppercase px-1.5 py-0.5 rounded bg-red-100 hover:bg-red-200 text-red-700 transition" x-text="isRtlLayout ? 'روکیں' : 'Stop'"></button>
                                        </div>
                                    </template>
                                    <template x-if="currentlySpeakingMsgId !== msg.id || !isSpeaking">
                                        <button @click="speakMessage(msg)" type="button" 
                                                class="flex items-center gap-1 text-slate-400 hover:text-red-600 transition font-semibold"
                                                title="Replay voice">
                                            <i class="ph ph-speaker-high text-xs"></i>
                                            <span x-text="isRtlLayout ? 'آواز سنیں' : 'Play Voice'"></span>
                                        </button>
                                    </template>
                                </div>

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
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-white border-t border-slate-200">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <!-- Microphone Toggle Button (Switches to hands-free voice mode) -->
                <button @click="toggleVoice()" type="button" 
                        :class="activeMode === 'voice' ? 'bg-emerald-600 text-white ring-2 ring-emerald-400' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 transition" 
                        :title="activeMode === 'voice' ? 'Voice call active (click to end)' : 'Click to start real-time two-way voice call'">
                    <i class="ph ph-microphone text-lg"></i>
                </button>

                <input type="text" x-model="inputMessage" 
                       :dir="isRtlLayout ? 'rtl' : 'ltr'"
                       :placeholder="activeMode === 'voice' ? (isRtlLayout ? 'سن رہا ہوں... یا یہاں ٹائپ کریں...' : 'Listening... Or type here to switch to text...') : (currentLangObj ? currentLangObj.placeholder : 'Ask AI Agent or click mic...')"
                       style="font-weight: 700;"
                       class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-red-500 transition">

                <button type="submit" class="w-10 h-10 rounded-2xl bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shrink-0 transition shadow-sm">
                    <i class="ph ph-paper-plane-tilt text-base" :class="isRtlLayout ? 'transform rotate-180' : ''"></i>
                </button>
            </form>
        </div>

    </div>
</div>
@endif
