@php
    $tenantAiConfig = \App\Models\CmsContent::getVal('feature_tenant_ai_agent', null);
    if ($tenantAiConfig === null) {
        $tenantAiConfig = \App\Models\CmsContent::getVal('ai_agent_enabled', '1');
    }
    $isTenantAiActive = ($tenantAiConfig === '1' || $tenantAiConfig === true || $tenantAiConfig === 1);
    $isSuperAdminUser = auth()->check() && auth()->user()->isSuperAdmin();
@endphp

@if(!$isTenantAiActive && !$isSuperAdminUser)
    {{-- Tenant AI Agent is hidden by Super Admin in Platform Settings --}}
@else
<script>
(function() {
    function initAiAgent() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('aiAgentComponent', () => ({
                isOpen: false,
                isListening: false,
                isSpeaking: false,
                soundEnabled: true,
                detectedLang: 'en',
                isRtl: false,
                speechLang: 'en-US',
                inputMessage: '',
                currentlySpeakingMsgId: null,
                currentUtterance: null,
                _watchdogTimer: null,
                availableVoices: [],
                recognition: null,
                synth: window.speechSynthesis || null,

                languages: {
                    auto: { 
                        code: 'auto', 
                        tag: 'en-US', 
                        placeholder: 'Ask in any language or click mic to speak...', 
                        greeting: 'Hello! I am your Invoicy AI Assistant. Ask me anything in English, Urdu, Roman Urdu, Arabic, Hindi, Turkish, French, Spanish, or click mic to speak!', 
                        suggestions: ['Iske bare mein batao', 'Create new invoice', 'How do drafts work?', 'Open Support Desk'] 
                    },
                    ur: { 
                        code: 'ur', 
                        tag: 'ur-PK', 
                        placeholder: 'اردو میں سوال پوچھیں یا مائیک دبائیں...', 
                        greeting: 'السلام علیکم! میں آپ کا AI ایجنٹ ہوں۔ انوائس بنانے، نجی ڈرافٹس، ریونیو اعدادوشمار یا سپورٹ کے بارے میں پوچھیں یا مائیک دبائیں!', 
                        suggestions: ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'] 
                    },
                    'ur-roman': { 
                        code: 'ur-roman', 
                        tag: 'ur-PK', 
                        placeholder: 'Roman Urdu me sawaal poochein ya mic click karein...', 
                        greeting: 'Hello! Main aap ka Invoicy AI Agent hoon. Naya invoice banane, 45+ presets, drafts, balance & stats ya support desk ke bare me poochein!', 
                        suggestions: ['Iske bare mein batao', 'Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Mera Balance & Stats'] 
                    },
                    ar: { 
                        code: 'ar', 
                        tag: 'ar-SA', 
                        placeholder: 'اسأل الوكيل الذكي أو اضغط المايك...', 
                        greeting: 'مرحباً! أنا وكيل الذكاء الاصطناعي لمنصة Invoicy. اسألني عن فواتيرك، المسودات، الإعدادات، أو اضغط المايك للأوامر الصوتية!', 
                        suggestions: ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'إحصائيات حسابي', 'فتح مكتب الدعم'] 
                    },
                    en: { 
                        code: 'en', 
                        tag: 'en-US', 
                        placeholder: 'Ask AI Agent or click mic to speak...', 
                        greeting: 'Hello! I am your AI Agent. Ask me anything about your invoices, settings, draft safeguards, support tickets, or click the mic to speak a voice command!', 
                        suggestions: ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk'] 
                    },
                    tr: { 
                        code: 'tr', 
                        tag: 'tr-TR', 
                        placeholder: 'AI Asistanına sor veya sesli konuş...', 
                        greeting: 'Merhaba! Ben Invoicy AI Asistanınızım. Faturalarınız, taslaklar, ayarlar veya destek masası hakkında soru sorabilir veya mikrofon ile konuşabilirsiniz!', 
                        suggestions: ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'] 
                    },
                    hi: { 
                        code: 'hi', 
                        tag: 'hi-IN', 
                        placeholder: 'AI से पूछें या माइक पर बोलें...', 
                        greeting: 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉइस, ड्राफ्ट, सेटिंग्स या सपोर्ट کے बारे में पूछें या हिंदी में वॉइस कमांड दें!', 
                        suggestions: ['नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'त्वरित प्रीसेट', 'सपोर्ट डेस्क खोलें'] 
                    },
                    es: { 
                        code: 'es', 
                        tag: 'es-ES', 
                        placeholder: 'Pregunta a la IA o usa el micrófono...', 
                        greeting: '¡Hola! Soy tu Agente IA de Invoicy. Pregúntame sobre facturas, borradores, soporte o habla en español usando el micrófono.', 
                        suggestions: ['Crear nueva factura', '¿Cómo funcionan los borradores?', 'Plantillas rápidas', 'Abrir soporte'] 
                    },
                    fr: { 
                        code: 'fr', 
                        tag: 'fr-FR', 
                        placeholder: 'Demandez à l\'IA ou parlez au micro...', 
                        greeting: 'Bonjour ! Je suis votre Agent IA Invoicy. Posez-moi des questions sur vos factures, brouillons, ou cliquez sur le micro pour parler en français.', 
                        suggestions: ['Créer une facture', 'Fonctionnement des brouillons', 'Modèles rapides', 'Ouvrir le support'] 
                    },
                    de: { 
                        code: 'de', 
                        tag: 'de-DE', 
                        placeholder: 'Frage an KI oder Mikrofon nutzen...', 
                        greeting: 'Hallo! Ich bin Ihr Invoicy KI-Agent. Fragen Sie mich nach Rechnungen, Entwürfen oder sprechen Sie auf Deutsch mit dem Mikrofon.', 
                        suggestions: ['Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Schnellvorlagen', 'Support öffnen'] 
                    },
                    zh: { 
                        code: 'zh', 
                        tag: 'zh-CN', 
                        placeholder: '向 AI 提问或点击麦克风...', 
                        greeting: '您好！我是 Invoicy AI 助手。向我询问有关发票、草稿、设置或支持的问题，或使用语音提问！', 
                        suggestions: ['创建新发票', '草稿如何运作？', '快捷预设', '打开支持'] 
                    }
                },

                messages: [],

                init() {
                    const initialLang = this.languages.auto;
                    const initialId = 'msg-init-' + Date.now();
                    this.messages = [{
                        id: initialId,
                        sender: 'ai',
                        text: initialLang.greeting,
                        suggestions: initialLang.suggestions
                    }];

                    this.loadVoices();
                    if (this.synth && this.synth.onvoiceschanged !== undefined) {
                        this.synth.onvoiceschanged = () => { this.loadVoices(); };
                    }

                    this.initSpeech();

                    window.addEventListener('open-ai-copilot', () => {
                        this.isOpen = true;
                        this.scrollToBottom();
                    });
                },

                get currentLangObj() {
                    if (this.detectedLang && this.languages[this.detectedLang]) {
                        return this.languages[this.detectedLang];
                    }
                    return this.languages.auto;
                },

                loadVoices() {
                    if (this.synth) {
                        this.availableVoices = this.synth.getVoices() || [];
                    }
                },

                /**
                 * Native Voice Selector.
                 * Prioritizes native Hindi speakers (Google हिन्दी / Kalpana / Hemant).
                 * Urdu falls back to Hindi for authentic phonetics if Urdu pack is missing.
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

                    // 1. Native Hindi Voice Picker
                    if (prefix === 'hi') {
                        const hindiVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('hi') || n.includes('hindi') || n.includes('हिन्दी') || n.includes('kalpana') || n.includes('hemant') || n.includes('swara') || n.includes('madhur');
                        });
                        if (hindiVoice) return hindiVoice;
                    }

                    // 2. Native Urdu Voice Picker with Hindi phonetic fallback
                    if (prefix === 'ur') {
                        const urduVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('ur') || n.includes('urdu') || n.includes('اردو') || n.includes('asad') || n.includes('uzma');
                        });
                        if (urduVoice) return urduVoice;

                        // Fallback to Hindi voice for Urdu/Roman Urdu
                        const hindiFallback = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('hi') || n.includes('hindi') || n.includes('kalpana') || n.includes('hemant');
                        });
                        if (hindiFallback) return hindiFallback;
                    }

                    // 3. Native Arabic Voice Picker
                    if (prefix === 'ar') {
                        const arVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('ar') || n.includes('arabic') || n.includes('العربية') || n.includes('naayf') || n.includes('salma') || n.includes('shakir') || n.includes('zeina') || n.includes('tarik');
                        });
                        if (arVoice) return arVoice;
                    }

                    // 4. Native Turkish Voice Picker
                    if (prefix === 'tr') {
                        const trVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('tr') || n.includes('turkish') || n.includes('türkçe') || n.includes('emre') || n.includes('filiz') || n.includes('ahmet');
                        });
                        if (trVoice) return trVoice;
                    }

                    // 5. Spanish, French, German, Chinese, English
                    if (prefix === 'es') {
                        const esVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('es') || n.includes('spanish') || n.includes('español') || n.includes('helena') || n.includes('laura') || n.includes('pablo');
                        });
                        if (esVoice) return esVoice;
                    }

                    if (prefix === 'fr') {
                        const frVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('fr') || n.includes('french') || n.includes('français') || n.includes('hortense') || n.includes('paul');
                        });
                        if (frVoice) return frVoice;
                    }

                    if (prefix === 'de') {
                        const deVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('de') || n.includes('german') || n.includes('deutsch') || n.includes('stefan') || n.includes('hedda');
                        });
                        if (deVoice) return deVoice;
                    }

                    if (prefix === 'zh') {
                        const zhVoice = voices.find(v => {
                            const l = (v.lang || '').toLowerCase();
                            const n = (v.name || '').toLowerCase();
                            return l.startsWith('zh') || n.includes('chinese') || n.includes('中文') || n.includes('huihui') || n.includes('yaoyao');
                        });
                        if (zhVoice) return zhVoice;
                    }

                    // 6. Generic Exact or Prefix match
                    let match = voices.find(v => v.lang && v.lang.toLowerCase() === target);
                    if (match) return match;

                    match = voices.find(v => v.lang && v.lang.toLowerCase().startsWith(prefix));
                    if (match) return match;

                    // 7. Natural English Voice or Default Voice
                    const enNatural = voices.find(v => {
                        const n = (v.name || '').toLowerCase();
                        return n.includes('natural') || n.includes('neural') || n.includes('google us');
                    });
                    if (enNatural) return enNatural;

                    return voices.find(v => v.default) || voices[0] || null;
                },

                initSpeech() {
                    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                        try {
                            this.recognition = new SpeechRec();
                            this.recognition.continuous = false;
                            this.recognition.interimResults = false;
                            this.recognition.lang = this.speechLang || 'en-US';

                            this.recognition.onstart = () => {
                                this.isListening = true;
                            };

                            this.recognition.onresult = (event) => {
                                this.isListening = false;
                                if (event.results && event.results[0] && event.results[0][0]) {
                                    const transcript = event.results[0][0].transcript;
                                    this.inputMessage = transcript;
                                    this.sendMessage();
                                }
                            };

                            this.recognition.onerror = (event) => {
                                console.warn('Speech recognition notice:', event.error);
                                this.isListening = false;
                                if (event.error === 'not-allowed') {
                                    this.messages.push({
                                        id: 'err-' + Date.now(),
                                        sender: 'ai',
                                        text: 'Microphone permission was not allowed. Please click the mic or lock icon in your browser address bar to allow microphone access, or simply type your message.'
                                    });
                                    this.scrollToBottom();
                                }
                            };

                            this.recognition.onend = () => {
                                this.isListening = false;
                            };
                        } catch (e) {
                            console.warn('Speech recognition init error:', e);
                        }
                    }
                },

                async toggleVoice() {
                    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                        alert('Voice recognition requires Google Chrome, Microsoft Edge, or Safari over HTTPS.');
                        return;
                    }

                    // Stop active speech output if speaking
                    this.stopSpeech();

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                            stream.getTracks().forEach(track => track.stop());
                        } catch (err) {
                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.messages.push({
                                    id: 'err-' + Date.now(),
                                    sender: 'ai',
                                    text: 'Microphone access is blocked by your browser. Please click the 🔒 lock icon next to the URL in your browser address bar, set Microphone to "Allow", and reload.'
                                });
                                this.scrollToBottom();
                                return;
                            }
                        }
                    }

                    if (!this.recognition) {
                        this.initSpeech();
                    }

                    if (this.isListening) {
                        try { this.recognition.stop(); } catch (e) {}
                        this.isListening = false;
                    } else {
                        try {
                            this.recognition.lang = this.speechLang || 'en-US';
                            this.recognition.start();
                            this.isListening = true;
                        } catch (e) {
                            try {
                                this.recognition.abort();
                                setTimeout(() => {
                                    this.recognition.start();
                                    this.isListening = true;
                                }, 250);
                            } catch (e2) {
                                this.isListening = false;
                            }
                        }
                    }
                },

                /**
                 * CONTINUOUS VOICE SYNTHESIS PIPELINE (Issue 1 Root Cause Fix)
                 * Solves Chromium garbage collection bug and cancel() queue race condition.
                 * Every response (1st, 2nd, 3rd, 10th turn) will play voice consistently.
                 */
                speak(text, msgId = null) {
                    if (!this.soundEnabled || !this.synth) {
                        return;
                    }

                    // 1. Clear any stuck watchdog
                    if (this._watchdogTimer) {
                        clearInterval(this._watchdogTimer);
                        this._watchdogTimer = null;
                    }

                    // 2. Cleanly cancel previous audio if still speaking
                    try {
                        if (this.synth.speaking || this.synth.pending) {
                            this.synth.cancel();
                        }
                    } catch (e) {}

                    // 3. Clean markdown formatting, asterisks, bullets, links
                    const cleanText = text
                        .replace(/\*\*(.*?)\*\*/g, '$1')
                        .replace(/\*(.*?)\*/g, '$1')
                        .replace(/•/g, '')
                        .replace(/\[(.*?)\]\(.*?\)/g, '$1')
                        .replace(/[#_~`]/g, '')
                        .trim();

                    if (!cleanText) return;

                    // 4. Asynchronous delay (60ms) allows Chromium audio thread to finish cancel()
                    setTimeout(() => {
                        try {
                            // Resume if paused
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

                            // CHROMIUM BUG FIX: Pin utterance to window and component to prevent V8 garbage collection!
                            window._activeSpeechUtterance = utter;
                            this.currentUtterance = utter;
                            this.currentlySpeakingMsgId = msgId;

                            utter.onstart = () => {
                                this.isSpeaking = true;
                                this.currentlySpeakingMsgId = msgId;

                                // Chromium 15s pause bug watchdog
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
                            };

                            utter.onerror = (err) => {
                                console.warn('TTS playback notice:', err);
                                this.isSpeaking = false;
                                this.currentlySpeakingMsgId = null;
                                window._activeSpeechUtterance = null;
                                this.currentUtterance = null;
                                if (this._watchdogTimer) {
                                    clearInterval(this._watchdogTimer);
                                    this._watchdogTimer = null;
                                }
                            };

                            this.synth.speak(utter);
                        } catch (e) {
                            console.error('Speech synthesis failure:', e);
                            this.isSpeaking = false;
                            this.currentlySpeakingMsgId = null;
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

                stopSpeech() {
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
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    if (!this.soundEnabled) {
                        this.stopSpeech();
                    }
                },

                async clearChat() {
                    this.stopSpeech();
                    try {
                        await fetch('{{ route('ai.copilot.clear') }}', { method: 'POST' });
                    } catch (e) {}
                    const langObj = this.currentLangObj;
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

                    // Stop existing voice immediately on new message
                    this.stopSpeech();

                    const userMsgId = 'msg-user-' + Date.now();
                    this.messages.push({ id: userMsgId, sender: 'user', text: text });
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
                            body: JSON.stringify({ message: text })
                        });

                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }

                        const data = await res.json();
                        
                        // Update detected language, voice and RTL mode
                        if (data.lang) {
                            this.speechLang = data.lang;
                            if (this.recognition) {
                                this.recognition.lang = data.lang;
                            }
                        }
                        if (data.lang_code) {
                            this.detectedLang = data.lang_code;
                        }
                        this.isRtl = !!data.is_rtl;

                        const aiMsgId = 'msg-ai-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
                        this.messages.push({
                            id: aiMsgId,
                            sender: 'ai',
                            text: data.reply,
                            action: data.action || null,
                            suggestions: data.suggestions || []
                        });
                        this.scrollToBottom();

                        // CONTINUOUS VOICE: Speak response aloud for every turn!
                        this.speak(data.reply, aiMsgId);
                    } catch (e) {
                        console.error('AI Agent error:', e);
                        const fallbackLang = this.currentLangObj;
                        const errId = 'msg-err-' + Date.now();
                        this.messages.push({ 
                            id: errId,
                            sender: 'ai', 
                            text: fallbackLang.greeting, 
                            suggestions: fallbackLang.suggestions 
                        });
                        this.scrollToBottom();
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

<div x-data="aiAgentComponent" 
     dir="ltr" 
     style="direction: ltr !important; position: fixed !important; bottom: 24px !important; right: 24px !important; left: auto !important; z-index: 9999;">

    <!-- Floating Trigger Button -->
    <div x-show="!isOpen" x-cloak>
        <button @click="isOpen = true; scrollToBottom()" type="button"
                style="font-weight: 700;"
                class="group flex items-center gap-2.5 px-5 py-3.5 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white rounded-full shadow-2xl transition duration-300 transform hover:scale-105 border-2 border-white/20">
            <span class="text-xs font-bold tracking-wider flex items-center gap-2">
                <i class="ph ph-microphone text-base text-white"></i>
                <span class="font-bold">AI Agent</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-ping"></span>
            </span>
        </button>
    </div>

    <!-- Active Chat Modal -->
    <div x-show="isOpen" x-cloak
         class="w-[360px] sm:w-[440px] h-[590px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200">
        
        <!-- Header (Clean, Zero Clutter) -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 text-white p-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5" style="font-weight: 700;">
                        <span>AI Agent</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                    </h3>
                    <p class="text-[10px] text-slate-300 font-bold flex items-center gap-1.5" style="font-weight: 700;">
                        <span>Intelligent Financial Assistant</span>
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

                <!-- Mute / Unmute Voice Toggle -->
                <button @click="toggleSound()" type="button" :title="soundEnabled ? 'Mute Voice' : 'Unmute Voice'"
                        class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i :class="soundEnabled ? 'ph ph-speaker-high' : 'ph ph-speaker-slash text-rose-400'" class="text-base"></i>
                </button>

                <!-- Close Modal -->
                <button @click="isOpen = false" type="button" class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
        </div>

        <!-- Listening Bar Indicator (Live Waveform) -->
        <div x-show="isListening" x-cloak class="bg-red-600 text-white text-[11px] font-bold py-1.5 px-3 flex items-center justify-between animate-pulse" style="font-weight: 700;">
            <span class="flex items-center gap-2">
                <i class="ph ph-waveform text-sm animate-bounce"></i>
                <span>Listening... Speak your command</span>
            </span>
            <button @click="toggleVoice()" type="button" class="text-xs uppercase underline font-bold">Stop</button>
        </div>

        <!-- Chat Conversation Messages (With dynamic RTL / LTR formatting) -->
        <div id="ai-chat-body" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/60 text-xs">
            <template x-for="(msg, idx) in messages" :key="msg.id || idx">
                <div :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
                    <div :class="msg.sender === 'user' ? 'bg-red-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs shadow-2xs'"
                         :dir="isRtl ? 'rtl' : 'ltr'"
                         class="max-w-[88%] rounded-2xl p-3.5 space-y-2 text-left rtl:text-right">
                        
                        <div x-text="msg.text" 
                             :class="isRtl ? 'text-right' : 'text-left'"
                             class="leading-relaxed font-bold whitespace-pre-line" style="font-weight: 700;"></div>
                        
                        <!-- Interactive Action Button (Stop auto-redirect, give prominent button to click) -->
                        <template x-if="msg.action && msg.action.url">
                            <div class="pt-2">
                                <a :href="msg.action.url" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl font-bold text-xs shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5"
                                   style="font-weight: 700;">
                                    <i class="ph ph-arrow-square-out text-sm"></i>
                                    <span x-text="msg.action.label || 'Open & Proceed ➔'"></span>
                                </a>
                            </div>
                        </template>

                        <!-- Audio Controls for AI Messages (Replay voice & Playing Waveform) -->
                        <template x-if="msg.sender === 'ai'">
                            <div class="pt-1.5 flex items-center justify-between border-t border-slate-100 text-[10px]">
                                <template x-if="currentlySpeakingMsgId === msg.id && isSpeaking">
                                    <div class="flex items-center gap-1.5 text-red-600 font-bold animate-pulse">
                                        <i class="ph ph-waveform text-sm"></i>
                                        <span>Speaking...</span>
                                        <button @click="stopSpeech()" type="button" class="ml-1 text-[9px] uppercase px-1.5 py-0.5 rounded bg-red-100 hover:bg-red-200 text-red-700 transition">Stop</button>
                                    </div>
                                </template>
                                <template x-if="currentlySpeakingMsgId !== msg.id || !isSpeaking">
                                    <button @click="speakMessage(msg)" type="button" 
                                            class="flex items-center gap-1 text-slate-400 hover:text-red-600 transition font-semibold"
                                            title="Replay voice">
                                        <i class="ph ph-speaker-high text-xs"></i>
                                        <span>Play Voice</span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- Suggestion Pills -->
                        <template x-if="msg.suggestions && msg.suggestions.length > 0">
                            <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5" :dir="isRtl ? 'rtl' : 'ltr'">
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
                <button @click="toggleVoice()" type="button" 
                        :class="isListening ? 'bg-red-600 text-white animate-bounce ring-2 ring-red-400' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 transition" 
                        title="Click to speak (hands-free voice commands)">
                    <i class="ph ph-microphone text-lg"></i>
                </button>

                <input type="text" x-model="inputMessage" 
                       :dir="isRtl ? 'rtl' : 'ltr'"
                       :placeholder="currentLangObj ? currentLangObj.placeholder : 'Ask AI Agent or click mic...'"
                       style="font-weight: 700;"
                       class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-red-500 transition">

                <button type="submit" class="w-10 h-10 rounded-2xl bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shrink-0 transition shadow-sm">
                    <i class="ph ph-paper-plane-tilt text-base"></i>
                </button>
            </form>
        </div>

    </div>
</div>
@endif
