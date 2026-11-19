<script>
(function() {
    function initAiAgent() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('aiAgentComponent', () => ({
                isOpen: false,
                isListening: false,
                isSpeaking: false,
                soundEnabled: true,
                selectedLang: 'auto', // 'auto' or explicit language code
                detectedLang: 'en',
                isRtl: false,
                speechLang: 'en-US',
                inputMessage: '',
                openLangMenu: false,
                availableVoices: [],
                recognition: null,
                synth: window.speechSynthesis || null,

                languages: {
                    auto: { 
                        code: 'auto', 
                        tag: 'en-US', 
                        flag: '🌐', 
                        label: 'Auto', 
                        name: 'Auto Detect', 
                        placeholder: 'Ask in any language or click mic to speak...', 
                        greeting: 'Hello! I am your Invoicy AI Assistant. Ask me anything in English, Urdu, Roman Urdu, Arabic, Hindi, Turkish, French, Spanish, or click mic to speak!', 
                        suggestions: ['Iske bare mein batao', 'Create new invoice', 'How do drafts work?', 'Open Support Desk'] 
                    },
                    ur: { 
                        code: 'ur', 
                        tag: 'ur-PK', 
                        flag: '🇵🇰', 
                        label: 'اردو', 
                        name: 'اردو (Urdu)', 
                        placeholder: 'اردو میں سوال پوچھیں یا مائیک دبائیں...', 
                        greeting: 'السلام علیکم! میں آپ کا AI ایجنٹ ہوں۔ انوائس بنانے، نجی ڈرافٹس، ریونیو اعدادوشمار یا سپورٹ کے بارے میں پوچھیں یا مائیک دبائیں!', 
                        suggestions: ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'] 
                    },
                    'ur-roman': { 
                        code: 'ur-roman', 
                        tag: 'ur-PK', 
                        flag: '🇵🇰', 
                        label: 'Roman', 
                        name: 'Roman Urdu', 
                        placeholder: 'Roman Urdu me sawaal poochein ya mic click karein...', 
                        greeting: 'Hello! Main aap ka Invoicy AI Agent hoon. Naya invoice banane, 45+ presets, drafts, balance & stats ya support desk ke bare me poochein!', 
                        suggestions: ['Iske bare mein batao', 'Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Mera Balance & Stats'] 
                    },
                    ar: { 
                        code: 'ar', 
                        tag: 'ar-SA', 
                        flag: '🇸🇦', 
                        label: 'العربية', 
                        name: 'العربية (Arabic)', 
                        placeholder: 'اسأل الوكيل الذكي أو اضغط المايك...', 
                        greeting: 'مرحباً! أنا وكيل الذكاء الاصطناعي لمنصة Invoicy. اسألني عن فواتيرك، المسودات، الإعدادات، أو اضغط المايك للأوامر الصوتية!', 
                        suggestions: ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'إحصائيات حسابي', 'فتح مكتب الدعم'] 
                    },
                    en: { 
                        code: 'en', 
                        tag: 'en-US', 
                        flag: '🇺🇸', 
                        label: 'EN', 
                        name: 'English', 
                        placeholder: 'Ask AI Agent or click mic to speak...', 
                        greeting: 'Hello! I am your AI Agent. Ask me anything about your invoices, settings, draft safeguards, support tickets, or click the mic to speak a voice command!', 
                        suggestions: ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk'] 
                    },
                    tr: { 
                        code: 'tr', 
                        tag: 'tr-TR', 
                        flag: '🇹🇷', 
                        label: 'TR', 
                        name: 'Türkçe', 
                        placeholder: 'AI Asistanına sor veya sesli konuş...', 
                        greeting: 'Merhaba! Ben Invoicy AI Asistanınızım. Faturalarınız, taslaklar, ayarlar veya destek masası hakkında soru sorabilir veya mikrofon ile konuşabilirsiniz!', 
                        suggestions: ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'] 
                    },
                    hi: { 
                        code: 'hi', 
                        tag: 'hi-IN', 
                        flag: '🇮🇳', 
                        label: 'हिन्दी', 
                        name: 'हिन्दी (Hindi)', 
                        placeholder: 'AI से पूछें या माइक पर बोलें...', 
                        greeting: 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉइस, ड्राफ्ट, सेटिंग्स या सपोर्ट के बारे में पूछें या हिंदी में वॉइस कमांड दें!', 
                        suggestions: ['नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'त्वरित प्रीसेट', 'सपोर्ट डेस्क खोलें'] 
                    },
                    es: { 
                        code: 'es', 
                        tag: 'es-ES', 
                        flag: '🇪🇸', 
                        label: 'ES', 
                        name: 'Español', 
                        placeholder: 'Pregunta a la IA o usa el micrófono...', 
                        greeting: '¡Hola! Soy tu Agente IA de Invoicy. Pregúntame sobre facturas, borradores, soporte o habla en español usando el micrófono.', 
                        suggestions: ['Crear nueva factura', '¿Cómo funcionan los borradores?', 'Plantillas rápidas', 'Abrir soporte'] 
                    },
                    fr: { 
                        code: 'fr', 
                        tag: 'fr-FR', 
                        flag: '🇫🇷', 
                        label: 'FR', 
                        name: 'Français', 
                        placeholder: 'Demandez à l\'IA ou parlez au micro...', 
                        greeting: 'Bonjour ! Je suis votre Agent IA Invoicy. Posez-moi des questions sur vos factures, brouillons, ou cliquez sur le micro pour parler en français.', 
                        suggestions: ['Créer une facture', 'Fonctionnement des brouillons', 'Modèles rapides', 'Ouvrir le support'] 
                    },
                    de: { 
                        code: 'de', 
                        tag: 'de-DE', 
                        flag: '🇩🇪', 
                        label: 'DE', 
                        name: 'Deutsch', 
                        placeholder: 'Frage an KI oder Mikrofon nutzen...', 
                        greeting: 'Hallo! Ich bin Ihr Invoicy KI-Agent. Fragen Sie mich nach Rechnungen, Entwürfen oder sprechen Sie auf Deutsch mit dem Mikrofon.', 
                        suggestions: ['Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Schnellvorlagen', 'Support öffnen'] 
                    },
                    zh: { 
                        code: 'zh', 
                        tag: 'zh-CN', 
                        flag: '🇨🇳', 
                        label: '中文', 
                        name: '中文 (Chinese)', 
                        placeholder: '向 AI 提问或点击麦克风...', 
                        greeting: '您好！我是 Invoicy AI 助手。向我询问有关发票、草稿、设置或支持的问题，或使用语音提问！', 
                        suggestions: ['创建新发票', '草稿如何运作？', '快捷预设', '打开支持'] 
                    }
                },

                messages: [],

                init() {
                    const initialLang = this.languages.auto;
                    this.messages = [{
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
                    if (this.selectedLang !== 'auto' && this.languages[this.selectedLang]) {
                        return this.languages[this.selectedLang];
                    }
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

                getVoiceForLang(langTag) {
                    if (!this.synth) return null;
                    let voices = this.availableVoices;
                    if (!voices || voices.length === 0) {
                        voices = this.synth.getVoices() || [];
                        this.availableVoices = voices;
                    }
                    if (!voices || voices.length === 0) return null;

                    const target = (langTag || 'en-US').toLowerCase();
                    const prefix = target.split('-')[0];

                    // 1. Exact match e.g. 'ur-pk', 'ar-sa', 'tr-tr'
                    let match = voices.find(v => v.lang && v.lang.toLowerCase() === target);
                    if (match) return match;

                    // 2. Prefix match e.g. 'ur', 'ar', 'hi', 'tr', 'es', 'fr', 'de'
                    match = voices.find(v => v.lang && v.lang.toLowerCase().startsWith(prefix));
                    if (match) return match;

                    // 3. Name contains language keyword
                    const langNames = {
                        'tr': 'turkish',
                        'ar': 'arabic',
                        'ur': 'urdu',
                        'hi': 'hindi',
                        'es': 'spanish',
                        'fr': 'french',
                        'de': 'german',
                        'zh': 'chinese',
                        'en': 'english'
                    };
                    const nameKey = langNames[prefix];
                    if (nameKey) {
                        match = voices.find(v => v.name && v.name.toLowerCase().includes(nameKey));
                        if (match) return match;
                    }

                    return null;
                },

                setLanguage(langCode) {
                    this.selectedLang = langCode;
                    const langObj = this.languages[langCode] || this.languages.auto;
                    this.speechLang = langObj.tag;
                    this.isRtl = (langCode === 'ur' || langCode === 'ar');

                    if (this.recognition) {
                        this.recognition.lang = this.speechLang;
                    }

                    this.messages.push({
                        sender: 'ai',
                        text: langObj.greeting,
                        suggestions: langObj.suggestions
                    });
                    this.scrollToBottom();

                    // Speak greeting
                    this.speak(langObj.greeting);
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
                    if (this.isSpeaking && this.synth) {
                        this.synth.cancel();
                        this.isSpeaking = false;
                    }

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                            stream.getTracks().forEach(track => track.stop());
                        } catch (err) {
                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.messages.push({
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

                speak(text, onComplete = null) {
                    if (!this.soundEnabled || !this.synth) {
                        if (onComplete) {
                            setTimeout(onComplete, 600);
                        }
                        return;
                    }
                    try {
                        this.synth.cancel();

                        // Clean markdown formatting before TTS
                        const cleanText = text
                            .replace(/\*\*(.*?)\*\*/g, '$1')
                            .replace(/\*(.*?)\*/g, '$1')
                            .replace(/•/g, '')
                            .replace(/\[(.*?)\]\(.*?\)/g, '$1')
                            .replace(/[#_~`]/g, '')
                            .trim();

                        const utter = new SpeechSynthesisUtterance(cleanText);
                        const targetLang = this.speechLang || 'en-US';
                        utter.lang = targetLang;
                        utter.rate = 1.0;
                        utter.pitch = 1.0;

                        // Match browser TTS voice for the native language
                        const matchedVoice = this.getVoiceForLang(targetLang);
                        if (matchedVoice) {
                            utter.voice = matchedVoice;
                        }

                        utter.onstart = () => { this.isSpeaking = true; };
                        utter.onend = () => {
                            this.isSpeaking = false;
                            if (onComplete) {
                                setTimeout(onComplete, 300);
                            }
                        };
                        utter.onerror = (err) => {
                            this.isSpeaking = false;
                            if (onComplete) onComplete();
                        };
                        this.synth.speak(utter);

                        // Safety timer fallback to prevent stuck states
                        if (onComplete) {
                            const fallbackMs = Math.max(3000, cleanText.length * 85);
                            setTimeout(() => {
                                if (this.isSpeaking) {
                                    this.isSpeaking = false;
                                    onComplete();
                                }
                            }, fallbackMs);
                        }
                    } catch (e) {
                        if (onComplete) onComplete();
                    }
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    if (!this.soundEnabled && this.synth) {
                        this.synth.cancel();
                        this.isSpeaking = false;
                    }
                },

                async clearChat() {
                    try {
                        await fetch('{{ route('ai.copilot.clear') }}', { method: 'POST' });
                    } catch (e) {}
                    const langObj = this.currentLangObj;
                    this.messages = [{
                        sender: 'ai',
                        text: langObj.greeting,
                        suggestions: langObj.suggestions
                    }];
                    this.scrollToBottom();
                },

                async sendMessage(customText = null) {
                    const text = customText || this.inputMessage.trim();
                    if (!text) return;

                    // Stop active voice before user sends new message
                    if (this.synth) {
                        this.synth.cancel();
                        this.isSpeaking = false;
                    }

                    this.messages.push({ sender: 'user', text: text });
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
                                lang: this.selectedLang !== 'auto' ? this.selectedLang : null 
                            })
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

                        this.messages.push({
                            sender: 'ai',
                            text: data.reply,
                            action: data.action || null,
                            suggestions: data.suggestions || []
                        });
                        this.scrollToBottom();

                        if (data.action && data.action.type === 'navigate') {
                            this.speak(data.reply, () => {
                                window.location.href = data.action.url;
                            });
                        } else {
                            this.speak(data.reply);
                        }
                    } catch (e) {
                        console.error('AI Agent error:', e);
                        const fallbackLang = this.currentLangObj;
                        this.messages.push({ 
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

    <!-- Floating Trigger Button (Bold & Distinct) -->
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
         class="w-[360px] sm:w-[440px] h-[580px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200">
        
        <!-- Header with Multi-Language Dropdown Selector -->
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
                <!-- Multi-Language Selector Dropdown (Defaults to 🌐 Auto Detect) -->
                <div class="relative" x-data="{ openLang: false }">
                    <button @click="openLang = !openLang" @click.outside="openLang = false" type="button"
                            class="px-2 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold flex items-center gap-1 transition"
                            title="Language: Auto Detect or choose manual">
                        <span x-text="currentLangObj ? currentLangObj.flag : '🌐'"></span>
                        <span x-text="currentLangObj ? currentLangObj.label : 'Auto'"></span>
                        <i class="ph ph-caret-down text-[10px]"></i>
                    </button>
                    
                    <div x-show="openLang" x-cloak
                         class="absolute right-0 mt-1.5 w-44 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl py-1 z-50 text-[11px] max-h-64 overflow-y-auto">
                        <div class="px-3 py-1 text-[9px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">Select Language</div>
                        <template x-for="(l, code) in languages" :key="code">
                            <button @click="setLanguage(code); openLang = false" type="button"
                                    :class="selectedLang === code ? 'bg-red-600/30 text-white font-bold' : 'text-slate-300 hover:bg-slate-800'"
                                    class="w-full px-3 py-1.5 text-left flex items-center justify-between transition">
                                <span class="flex items-center gap-2">
                                    <span x-text="l.flag"></span>
                                    <span x-text="l.name"></span>
                                </span>
                                <span x-show="selectedLang === code" class="text-emerald-400 text-xs">✓</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Reset Chat Session -->
                <button @click="clearChat()" type="button" title="New Conversation / Clear Context"
                        class="p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </button>

                <!-- Mute / Unmute Toggle -->
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
                <span>Listening in <span x-text="currentLangObj ? currentLangObj.name : 'Auto'"></span>... Speak now</span>
            </span>
            <button @click="toggleVoice()" type="button" class="text-xs uppercase underline font-bold">Stop</button>
        </div>

        <!-- Chat Conversation Messages (With dynamic RTL / LTR formatting) -->
        <div id="ai-chat-body" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/60 text-xs">
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
                    <div :class="msg.sender === 'user' ? 'bg-red-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs shadow-2xs'"
                         :dir="isRtl ? 'rtl' : 'ltr'"
                         class="max-w-[88%] rounded-2xl p-3.5 space-y-2 text-left rtl:text-right">
                        
                        <div x-text="msg.text" 
                             :class="isRtl ? 'text-right' : 'text-left'"
                             class="leading-relaxed font-bold whitespace-pre-line" style="font-weight: 700;"></div>
                        
                        <template x-if="msg.action && msg.action.type === 'navigate'">
                            <div class="pt-1.5">
                                <a :href="msg.action.url" class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-600 text-white rounded-lg font-bold text-[10px] hover:bg-red-700 transition shadow-xs" style="font-weight: 700;">
                                    <i class="ph ph-arrow-square-out"></i> Proceed Now
                                </a>
                            </div>
                        </template>

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
                        :title="'Click to speak in ' + (currentLangObj ? currentLangObj.name : 'Auto')">
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
