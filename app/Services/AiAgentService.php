<?php

namespace App\Services;

use App\Models\CmsContent;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\SupportTicket;
use App\Models\Workspace;
use App\Models\Payment;
use App\Models\RecurringInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAgentService
{
    /**
     * Process message from user and return structured AI response.
     */
    public function handleUserMessage(string $query, ?string $selectedLang = null, ?Workspace $workspace = null, $user = null): array
    {
        $startTime = microtime(true);
        $rawQuery = trim($query);
        $q = mb_strtolower($rawQuery, 'UTF-8');

        // Resolve workspace if null
        if (!$workspace) {
            $workspace = $user?->workspace 
                ?? ($user?->current_workspace_id ? Workspace::find($user->current_workspace_id) : null)
                ?? (method_exists($user, 'workspaces') ? $user->workspaces()->first() : null)
                ?? Workspace::first();
        }

        // 1. Language Detection & Normalization (Auto Detect by default)
        $langCode = $this->resolveLanguage($q, $selectedLang);
        $voiceLang = $this->getVoiceTag($langCode);
        $isRtl = in_array($langCode, ['ur', 'ar']);

        // Empty message handler
        if ($rawQuery === '') {
            return [
                'reply' => $this->getEmptyGreeting($langCode),
                'lang' => $voiceLang,
                'lang_code' => $langCode,
                'is_rtl' => $isRtl,
                'suggestions' => $this->getDefaultSuggestions($langCode),
            ];
        }

        // 2. Multi-turn Session Memory
        $history = session()->get('ai_copilot_history', []);
        $history[] = ['role' => 'user', 'content' => $rawQuery, 'time' => now()->toIso8601String()];
        if (count($history) > 16) {
            $history = array_slice($history, -16);
        }
        session()->put('ai_copilot_history', $history);

        // 3. Multi-Turn Dialogue Flow State Machine (e.g. Invoice Creation step-by-step)
        $flowResponse = $this->handleDialogueFlow($q, $rawQuery, $langCode, $voiceLang, $isRtl, $workspace, $user);
        if ($flowResponse !== null) {
            $this->recordAssistantReply($flowResponse['reply']);
            $this->logInteraction($rawQuery, $langCode, 'dialogue_flow', $flowResponse, microtime(true) - $startTime);
            return $flowResponse;
        }

        // 3.5. Instant Warm Human Greeting (Zero latency, prevents slow robotic LLM feature dumps)
        if ($this->isPureGreeting($q)) {
            $greetingResponse = $this->generateGreetingResponse($langCode, $voiceLang, $isRtl);
            $this->recordAssistantReply($greetingResponse['reply']);
            $this->logInteraction($rawQuery, $langCode, 'greeting', $greetingResponse, microtime(true) - $startTime);
            return $greetingResponse;
        }

        // 4. Attempt External LLM if Configured (OpenAI / Gemini)
        $externalResponse = $this->tryExternalLlm($rawQuery, $langCode, $workspace, $user, $history);
        if ($externalResponse !== null) {
            $this->recordAssistantReply($externalResponse['reply']);
            $this->logInteraction($rawQuery, $langCode, 'external_llm', $externalResponse, microtime(true) - $startTime);
            return $externalResponse;
        }

        // 5. Intelligent Local Conversational Engine & Tool Caller (Zero downtime fallback)
        $localResponse = $this->generateLocalIntelligenceResponse($q, $rawQuery, $langCode, $voiceLang, $isRtl, $workspace, $user);
        $this->recordAssistantReply($localResponse['reply']);
        $this->logInteraction($rawQuery, $langCode, 'local_engine', $localResponse, microtime(true) - $startTime);
        return $localResponse;
    }

    /**
     * Resolve incoming query language.
     */
    public function resolveLanguage(string $q, ?string $selectedLang = null): string
    {
        if (!empty($selectedLang) && $selectedLang !== 'auto' && in_array($selectedLang, ['en', 'ur', 'ur-roman', 'ar', 'hi', 'tr', 'es', 'fr', 'de', 'zh'])) {
            if ($selectedLang === 'en' && $this->isNativeUrdu($q)) {
                return 'ur';
            }
            if ($selectedLang === 'en' && $this->isNativeArabic($q)) {
                return 'ar';
            }
            return $selectedLang;
        }

        return $this->detectLanguage($q);
    }

    /**
     * Comprehensive Language Detector.
     */
    public function detectLanguage(string $q): string
    {
        // 1. Native Urdu Script Detection
        if ($this->isNativeUrdu($q)) {
            return 'ur';
        }

        // 2. Native Arabic Script Detection
        if ($this->isNativeArabic($q)) {
            return 'ar';
        }

        // 3. Arabic/Urdu Unicode Block (\x{0600}-\x{06FF})
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $q)) {
            if (preg_match('/(کیا|ہے|ہیں|کے|ساتھ|سے|کا|کی|کو|میں|پر|اچھا|اور|ہم|آپ|بتاؤ|بتائیں|بتا|کرو|کریں|کر|ہو|ہونا|کرنا|سکتے|سکتا|سکتی|نہیں|بھی|یہ|وہ|کیسے|کون|کیوں|انوائس|نوائس|سافٹویئر|سافٹ|پیسہ|پیسے|رقم|ڈرافٹ)/u', $q)) {
                return 'ur';
            }
            if (preg_match('/(فاتورة|فواتير|انشاء|إنشاء|مسودة|مسودات|دفع|عميل|عملاء|مكتب|تذكرة|مساعدة|كيف|اريد|أريد|شكرا|شكرًا|الرجاء|ممكن|تفاصيل|يرجى|نحن|أنت|ماذا|يمكنني)/u', $q)) {
                return 'ar';
            }
            return app()->getLocale() === 'ur' ? 'ur' : (app()->getLocale() === 'ar' ? 'ar' : 'ur');
        }

        // 4. Hindi Devanagari script
        if (preg_match('/[\x{0900}-\x{097F}]/u', $q)) {
            return 'hi';
        }

        // 5. Chinese script
        if (preg_match('/[\x{4E00}-\x{9FFF}]/u', $q)) {
            return 'zh';
        }

        // 6. Roman Urdu / Hinglish
        if (preg_match('/\b(assalam|asalam|walekum|alaikum|walaikum|salam|slam|iske|uske|is|iss|bare|baare|mein|me|batao|bataiye|bataen|bata|kya|hai|hain|kaise|kese|kare|karen|karo|kar|banaye|banayein|banao|madad|shukriya|mujhe|chahiye|kahan|theek|acha|accha|nahi|nhi|haan|bhi|aur|ye|yeh|wo|woh|apna|apni|karna|hoga|hota|chalega|bolo|bhejo|dekho|dikhao|hisaab|paisa|paise|kharcha|kamai|kitna|kitne|mera|meri|mere|saath|sath|kuch|btao|btaen|sakte|sakta|saktee|hun|hoon|karta|karte|bhai|yaar|janab|sahab)\b/i', $q)) {
            return 'ur-roman';
        }

        // 7. Turkish keywords
        if (preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $q) || preg_match('/\b(merhaba|selam|fatura|faturayı|ayarlar|yardım|nasıl|oluştur|müşteri|hatırlatıcı|destek|bilet)\b/i', $q)) {
            return 'tr';
        }

        // 8. Spanish keywords
        if (preg_match('/[áéíóúñ¿¡]/u', $q) || preg_match('/\b(hola|factura|configuración|ayuda|cómo|crear|cliente|recordatorio|soporte|gracias)\b/i', $q)) {
            return 'es';
        }

        // 9. French keywords
        if (preg_match('/[àâçéèêëîïôûùüÿœæ]/u', $q) || preg_match('/\b(bonjour|salut|facture|paramètres|aide|comment|créer|client|rappel|merci)\b/i', $q)) {
            return 'fr';
        }

        // 10. German keywords
        if (preg_match('/[äöüß]/u', $q) || preg_match('/\b(hallo|rechnung|einstellungen|hilfe|wie|erstellen|kunde|mahnung|danke)\b/i', $q)) {
            return 'de';
        }

        return 'en';
    }

    /**
     * Check if query contains native Urdu characters or words.
     */
    protected function isNativeUrdu(string $q): bool
    {
        if (preg_match('/[\x{0679}\x{0688}\x{0691}\x{06BA}\x{06D2}\x{06C2}\x{06C1}\x{06BE}\x{0686}\x{067E}\x{0698}\x{06AF}\x{06A9}\x{06D3}]/u', $q)) {
            return true;
        }

        $urduWords = [
            'کیا', 'ہے', 'ہیں', 'کے', 'ساتھ', 'سے', 'کا', 'کی', 'کو', 'میں', 'پر', 'اچھا', 'اور',
            'ہم', 'آپ', 'بتاؤ', 'بتائیں', 'بتا', 'کرو', 'کریں', 'کر', 'ہو', 'ہونا', 'کرنا', 'سکتے',
            'سکتا', 'سکتی', 'نہیں', 'نا', 'بھی', 'یہ', 'وہ', 'کیسے', 'کون', 'کیوں', 'انوائس', 'نوائس',
            'سافٹویئر', 'سافٹ', 'ویر', 'پیسہ', 'پیسے', 'رقم', 'ڈرافٹ', 'رسید', 'باقی', 'حساب', 'کھاتا',
            'شکریہ', 'سلام', 'مدد', 'سیٹنگز', 'چلیں', 'دکھائیں', 'دیکھیں', 'مسئلہ', 'ٹکٹ', 'گاہک', 'کلائنٹ'
        ];

        foreach ($urduWords as $word) {
            if (mb_strpos($q, $word, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if query is Arabic.
     */
    protected function isNativeArabic(string $q): bool
    {
        // Only check true Arabic-specific letters: Ta Marbuta (ة) and Alef Maksura (ى)
        // Hamza variants (ئ, إ, أ, آ, ؤ) are shared with Urdu and must NOT be Arabic-only!
        if (preg_match('/[\x{0629}\x{0649}]/u', $q)) {
            if (!$this->isNativeUrdu($q)) {
                return true;
            }
        }

        $arabicWords = [
            'فاتورة', 'فواتير', 'انشاء', 'إنشاء', 'مسودة', 'مسودات', 'دفع', 'سداد', 'عميل', 'عملاء',
            'مكتب', 'تذكرة', 'مساعدة', 'كيف', 'اريد', 'أريد', 'شكرا', 'شكرًا', 'الرجاء', 'ممكن',
            'تفاصيل', 'يرجى', 'نحن', 'أنت', 'ماذا', 'يمكنني', 'لوحة', 'تحكم', 'استرجاع', 'تقرير', 'مبيعات'
        ];

        foreach ($arabicWords as $w) {
            if (mb_strpos($q, $w, 0, 'UTF-8') !== false && !$this->isNativeUrdu($q)) {
                return true;
            }
        }

        return false;
    }

    /**
     * BCP-47 voice tag for Speech Synthesis and Recognition.
     */
    public function getVoiceTag(string $langCode): string
    {
        return match ($langCode) {
            'ur', 'ur-roman' => 'ur-PK',
            'ar' => 'ar-SA',
            'hi' => 'hi-IN',
            'tr' => 'tr-TR',
            'es' => 'es-ES',
            'fr' => 'fr-FR',
            'de' => 'de-DE',
            'zh' => 'zh-CN',
            default => 'en-US',
        };
    }

    /**
     * Multi-Turn Conversation State Machine.
     * Keeps track of multi-step invoice creation and customer operations across turns!
     */
    protected function handleDialogueFlow(string $q, string $rawQuery, string $lang, string $voiceLang, bool $isRtl, ?Workspace $workspace, $user): ?array
    {
        $flowState = session()->get('ai_dialogue_state');

        // Check if user wants to cancel an active flow
        if ($flowState && preg_match('/\b(cancel|stop|rehne do|nahi|no|mat karo|khilaf|alghaa|إلغاء)\b/iu', $q)) {
            session()->forget('ai_dialogue_state');
            return [
                'reply' => match ($lang) {
                    'ur' => "ٹھیک ہے، عمل منسوخ کر دیا گیا ہے۔ آپ مجھ سے کسی بھی وقت کچھ اور پوچھ سکتے ہیں۔",
                    'ur-roman' => "Theek hai, process cancel kar diya gaya hai. Aap mujhse kisi bhi waqt koi doosra sawaal pooch sakte hain.",
                    'ar' => "تم إلغاء العملية بنجاح. يمكنك سؤالي عن أي شيء آخر في أي وقت.",
                    default => "Understood, the process has been cancelled. Let me know what else I can assist you with."
                },
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => $this->getDefaultSuggestions($lang),
            ];
        }

        // ==============================================================
        // 1. INVOICE CREATION FLOW (Multi-turn conversational assistant)
        // ==============================================================

        // TRIGGER: User starts invoice creation without providing full details
        $isCreateTrigger = preg_match('/\b(create|make|generate|build|prepare|naya|new|need)\b.*?\b(invoice|bill|draft|فاتورة|انوائس|نوائس)\b/iu', $q)
            || preg_match('/\b(invoice|bill|draft|فاتورة|انوائس|نوائس)\b.*?\b(create|make|banana|banayein|banao|tayyar|chahiye|banana hai)\b/iu', $q)
            || preg_match('/\b(create invoice|new invoice|make invoice|prepare a bill|bill banana|invoice banana|invoice create|naya invoice|نیا انوائس|انوائس بنائیں|انوائس بنانا|انشاء فاتورة|فاتورة جديدة)\b/iu', $q);
        
        if ($isCreateTrigger && !$flowState) {
            // Check if user already gave customer name in the initial prompt e.g. "Create invoice for ABC Trading 2500 AED"
            $parsedCustomer = $this->extractCustomerName($rawQuery);
            $parsedAmount = $this->extractAmount($rawQuery);

            if ($parsedCustomer && $parsedAmount) {
                // All details present! Ask for final confirmation
                session()->put('ai_dialogue_state', [
                    'active_flow' => 'create_invoice',
                    'step' => 'awaiting_confirmation',
                    'data' => [
                        'customer' => $parsedCustomer,
                        'amount' => $parsedAmount['amount'],
                        'currency' => $parsedAmount['currency'] ?? ($workspace->currency ?? 'AED'),
                        'due_date' => 'After 15 days',
                    ]
                ]);

                return [
                    'reply' => match ($lang) {
                        'ur-roman' => "Maine **{$parsedCustomer}** ke liye **{$parsedAmount['amount']} " . ($parsedAmount['currency'] ?? 'AED') . "** ki invoice details prepare kar li hain. Kya main abhi draft invoice create kar doon?",
                        'ur' => "میں نے **{$parsedCustomer}** کے لیے **{$parsedAmount['amount']} " . ($parsedAmount['currency'] ?? 'AED') . "** کی انوائس تیار کر لی ہے۔ کیا میں ابھی ڈرافٹ انوائس جاری کر دوں؟",
                        'ar' => "لقد قمت بتجهيز تفاصيل الفاتورة لشركة **{$parsedCustomer}** بمبلغ **{$parsedAmount['amount']} " . ($parsedAmount['currency'] ?? 'AED') . "**. هل ترغب في إنشائها كمسودة الآن؟",
                        default => "I have prepared the invoice details for **{$parsedCustomer}** for **{$parsedAmount['amount']} " . ($parsedAmount['currency'] ?? 'AED') . "**. Would you like me to create the draft invoice now?"
                    },
                    'lang' => $voiceLang,
                    'lang_code' => $lang,
                    'is_rtl' => $isRtl,
                    'suggestions' => ['Yes, Create Invoice', 'Cancel']
                ];
            }

            // Start step 1: Ask for customer
            session()->put('ai_dialogue_state', [
                'active_flow' => 'create_invoice',
                'step' => 'awaiting_customer',
                'data' => []
            ]);

            return [
                'reply' => match ($lang) {
                    'ur' => "ضرور! میں انوائس بنانے میں آپ کی مدد کرتا ہوں۔ یہ انوائس کس کسٹمر یا کمپنی کے نام بنانی ہے؟ (یا فوری کیش سیل کے لیے 'Walk-in' کہہ سکتے ہیں)",
                    'ur-roman' => "Certainly! Ye invoice kis customer ya company ke liye banana hai? (Ya instant cash sale ke liye aap 'Walk-in' bol sakte hain)",
                    'ar' => "بكل سرور! ما هو اسم العميل أو الشركة التي ترغب في إصدار الفاتورة لها؟ (أو اكتب 'Walk-in' للبيع النقدي المباشر)",
                    'hi' => "ज़रूर! यह इनवॉइस किस ग्राहक या कंपनी के नाम बनानी है? (या कैश सेल के लिए 'Walk-in' कह सकते हैं)",
                    default => "Certainly! Which customer or company should I create the invoice for? (You can also say 'Walk-in' for instant cash sales)"
                },
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => ['Walk-in Customer', 'Cancel']
            ];
        }

        // ACTIVE FLOW: 'create_invoice'
        if ($flowState && ($flowState['active_flow'] ?? '') === 'create_invoice') {
            $step = $flowState['step'] ?? '';
            $data = $flowState['data'] ?? [];

            // STEP 1: Awaiting Customer Name
            if ($step === 'awaiting_customer') {
                $customer = trim($rawQuery);
                // Strip conversational prefixes e.g. "Can you create one for ABC Trading?" -> "ABC Trading"
                $customer = preg_replace('/^(can you create (one )?(for )?|create (one )?(for )?|yes (for )?|make (one )?(for )?|for|naam|name|client|customer|company)\s+/iu', '', $customer);
                $customer = preg_replace('/\?+$/', '', $customer);
                $customer = trim($customer);
                if (empty($customer)) {
                    $customer = 'Walk-in Customer';
                }
                $data['customer'] = $customer;
                $flowState['step'] = 'awaiting_amount';
                $flowState['data'] = $data;
                session()->put('ai_dialogue_state', $flowState);

                return [
                    'reply' => match ($lang) {
                        'ur-roman' => "Got it, **{$customer}**. Is invoice ki kitni amount (rakam) enter karni hai? (Maslan: 2500 AED ya 5000 USD)",
                        'ur' => "ٹھیک ہے، **{$customer}** کے لیے۔ اس انوائس کی کل رقم کیا درج کرنی ہے؟ (مثلاً: 2500 AED یا 5000)",
                        'ar' => "ممتاز لشركة **{$customer}**. ما هو المبلغ المطلوب إدراجه في هذه الفاتورة؟ (مثلاً: 2500 درهم أو 5000 دولار)",
                        default => "Got it for **{$customer}**. What amount should I enter for this invoice? (e.g., 2,500 AED or 5,000 USD)"
                    },
                    'lang' => $voiceLang,
                    'lang_code' => $lang,
                    'is_rtl' => $isRtl,
                    'suggestions' => ['1,000 AED', '2,500 AED', '5,000 AED', 'Cancel']
                ];
            }

            // STEP 2: Awaiting Amount
            if ($step === 'awaiting_amount') {
                $parsed = $this->extractAmount($rawQuery);
                if (!$parsed || $parsed['amount'] <= 0) {
                    return [
                        'reply' => match ($lang) {
                            'ur-roman' => "Meherbani kar ke sahi amount number mein batayein (maslan: 2500 AED ya 5000).",
                            'ur' => "براہ کرم درست رقم درج کریں (مثلاً: 2500 AED یا 5000)۔",
                            default => "Please specify a valid numeric amount (e.g. 2,500 AED or 5,000 USD)."
                        },
                        'lang' => $voiceLang,
                        'lang_code' => $lang,
                        'is_rtl' => $isRtl,
                        'suggestions' => ['2,500 AED', '5,000 AED', 'Cancel']
                    ];
                }

                $data['amount'] = $parsed['amount'];
                $data['currency'] = $parsed['currency'] ?? ($workspace->currency ?? 'AED');
                $flowState['step'] = 'awaiting_due_date';
                $flowState['data'] = $data;
                session()->put('ai_dialogue_state', $flowState);

                return [
                    'reply' => match ($lang) {
                        'ur-roman' => "Payment ki due date kab ki rakhni hai? (Maslan: 15 din baad, agle mahine, ya due on receipt)",
                        'ur' => "ادائیگی کی آخری تاریخ (Due Date) کیا رکھنی ہے؟ (مثلاً: 15 دن بعد، اگلے مہینے، یا فوری ادائیگی)",
                        'ar' => "متى يكون تاريخ استحقاق السداد؟ (مثلاً: بعد 15 يوماً، أو الشهر القادم، أو عند الاستلام فوراً)",
                        default => "When should the payment be due? (e.g., after 15 days, next month, or due on receipt)"
                    },
                    'lang' => $voiceLang,
                    'lang_code' => $lang,
                    'is_rtl' => $isRtl,
                    'suggestions' => ['After 15 days', 'Next month', 'Due on receipt', 'Cancel']
                ];
            }

            // STEP 3: Awaiting Due Date
            if ($step === 'awaiting_due_date') {
                $data['due_date'] = trim($rawQuery);
                $flowState['step'] = 'awaiting_confirmation';
                $flowState['data'] = $data;
                session()->put('ai_dialogue_state', $flowState);

                $cust = $data['customer'];
                $amt = number_format($data['amount'], 2) . ' ' . $data['currency'];
                $due = $data['due_date'];

                return [
                    'reply' => match ($lang) {
                        'ur-roman' => "Maine **{$cust}** ke liye **{$amt}** (Due: {$due}) ki invoice details tayyar kar li hain. Kya main abhi draft invoice create kar doon?",
                        'ur' => "میں نے **{$cust}** کے لیے **{$amt}** (تاریخ استحقاق: {$due}) کی انوائس تفصیلات تیار کر لی ہیں۔ کیا میں ابھی ڈرافٹ انوائس بنا دوں؟",
                        'ar' => "لقد قمت بتجهيز تفاصيل الفاتورة لشركة **{$cust}** بمبلغ **{$amt}** (الاستحقاق: {$due}). هل أعتمد إنشاء المسودة الآن؟",
                        default => "I have prepared the invoice details for **{$cust}** for **{$amt}** (Due: {$due}). Would you like me to create the draft invoice now?"
                    },
                    'lang' => $voiceLang,
                    'lang_code' => $lang,
                    'is_rtl' => $isRtl,
                    'suggestions' => ['Yes, Create Invoice', 'Cancel']
                ];
            }

            // STEP 4: Awaiting Confirmation
            if ($step === 'awaiting_confirmation') {
                if (preg_match('/\b(yes|haan|sure|create|banao|bana do|karo|bana dein|جی|نعم|ok|theek hai|proceed)\b/iu', $q)) {
                    session()->forget('ai_dialogue_state');

                    if (!$workspace) {
                        $workspace = Workspace::first();
                    }

                    // EXECUTE REAL BACKEND FUNCTION CALL
                    $created = $this->toolCreateInvoiceDraft(
                        $workspace,
                        $data['customer'] ?? 'Customer',
                        (float) ($data['amount'] ?? 100),
                        $data['currency'] ?? 'AED',
                        $data['due_date'] ?? 'After 15 days',
                        null,
                        $user
                    );

                    $invNum = $created['invoice_number'];
                    $cust = $data['customer'];
                    $amt = number_format($data['amount'], 2) . ' ' . $data['currency'];

                    return [
                        'reply' => match ($lang) {
                            'ur-roman' => "✅ Invoice **{$invNum}** for **{$cust}** ({$amt}) successfully create ho gaya hai as a draft! Aap neeche diye button par click kar ke review aur publish kar sakte hain:",
                            'ur' => "✅ انوائس **{$invNum}** برائے **{$cust}** ({$amt}) باحفاظت ڈرافٹ کے طور پر بن چکا ہے! آپ نیچے دیے گئے بٹن سے اس کا جائزہ لے کر شائع کر سکتے ہیں:",
                            'ar' => "✅ تم إنشاء الفاتورة **{$invNum}** لشركة **{$cust}** بمبلغ **{$amt}** كمسودة بنجاح! يمكنك مراجعتها ونشرها من الزر أدناه:",
                            default => "✅ Draft Invoice **{$invNum}** for **{$cust}** ({$amt}) has been successfully created! You can review and publish it using the button below:"
                        },
                        'action' => [
                            'type' => 'navigate',
                            'url' => $created['url'],
                            'label' => match ($lang) {
                                'ur' => "انوائس {$invNum} کھولیں ➔",
                                'ur-roman' => "Invoice {$invNum} Kholein ➔",
                                'ar' => "فتح الفاتورة {$invNum} ➔",
                                default => "Open Invoice {$invNum} ➔"
                            }
                        ],
                        'lang' => $voiceLang,
                        'lang_code' => $lang,
                        'is_rtl' => $isRtl,
                        'suggestions' => match ($lang) {
                            'ur' => ['تمام انوائسز دیکھیں', 'ایک اور انوائس بنائیں', 'میرے فنانشل اعدادوشمار'],
                            'ur-roman' => ['View All Invoices', 'Ek Aur Invoice Banayein', 'My Workspace Stats'],
                            default => ['View Invoices', 'Create Another Invoice', 'My Workspace Stats']
                        }
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Check if query is a pure conversational greeting without specific commands.
     */
    public function isPureGreeting(string $q): bool
    {
        return (bool) preg_match('/^(assalam|asalam|walekum|alaikum|walaikum|salam|slam|hello|hi|hey|good morning|good evening|good afternoon|namaste|marhaba|bonjour|hola|hallo|مرحبا|سلام|السلام عليكم|السلام علیکم|وعلیکم السلام|नमस्ते)\b/iu', $q)
            && !preg_match('/\b(create|make|issue|banaye|banayein|banao|draft|preset|presets|tax|recall|overdue|unpaid|how to|invoice|bill|client|customer|ticket|support|طريقة|فاتورة|انوائس|ڈرافٹ)\b/iu', $q);
    }

    /**
     * Generate warm, native greeting response matching the detected language.
     */
    public function generateGreetingResponse(string $lang, string $voiceLang, bool $isRtl): array
    {
        $reply = match ($lang) {
            'ur' => "وعلیکم السلام ورحمۃ اللہ وبرکاتہ! خوش آمدید!
میں آپ کا انوائسی فنانشل اسسٹنٹ ہوں۔ آج میں آپ کی کیا مدد کر سکتا ہوں؟ کیا آپ نیا انوائس بنانا چاہتے ہیں یا اپنے فنانشل ریکارڈز اور کھاتے دیکھنا چاہتے ہیں؟",

            'ur-roman' => "Walaikum Assalam! Khush aamdeed! Main aap ka Invoicy smart assistant hoon.
Aaj main aap ki kya madad kar sakta hoon? Naya invoice banana hai, drafts samajhne hain, ya apna revenue aur balance check karna hai?",

            'ar' => "وعليكم السلام ورحمة الله وبركاته! أهلاً وسهلاً بك في منصة Invoicy.
كيف يمكنني مساعدتك اليوم؟ هل ترغب في إصدار فاتورة جديدة أو استعراض رصيدك المالي؟",

            'hi' => "नमस्ते! Invoicy वित्तीय सहायक में आपका स्वागत है।
आज मैं आपकी क्या सहायता कर सकता हूँ? क्या आप नया इनवॉइस बनाना चाहते हैं या अपने बिलिंग रिकॉर्ड्स देखना चाहते हैं?",

            'tr' => "Merhaba! Invoicy Finans Asistanınıza hoş geldiniz.
Bugün size nasıl yardımcı olabilirim? Yeni bir fatura mı oluşturmak istersiniz, yoksa hesap özetinize mi bakmak istersiniz?",

            default => "Hello and welcome! I am your Invoicy Financial Assistant.
How can I help you today? Would you like to create a new invoice, explore industry presets, or check your workspace stats?",
        };

        return [
            'reply' => $reply,
            'lang' => $voiceLang,
            'lang_code' => $lang,
            'is_rtl' => $isRtl,
            'suggestions' => match ($lang) {
                'ur' => ['نیا انوائس بنائیں', 'انوائس کیسے استعمال کریں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'],
                'ur-roman' => ['Naya Invoice Banayein', 'Invoice kaise use karein?', 'Mera Balance & Stats', 'Support Desk Kholein'],
                'ar' => ['إنشاء فاتورة جديدة', 'كيف أستخدم الفواتير؟', 'إحصائيات حسابي', 'مكتب الدعم'],
                default => ['Create new invoice', 'How do I use invoices?', 'My Workspace Stats', 'Open Support Desk'],
            }
        ];
    }

    /**
     * Localized High-Precision AI Reasoning Engine with Natural Human-like Dialogue.
     */
    protected function generateLocalIntelligenceResponse(string $q, string $rawQuery, string $lang, string $voiceLang, bool $isRtl, ?Workspace $workspace, $user): array
    {
        // -------------------------------------------------------------
        // INTENT 0: Warm, Natural Human Greeting (Handles "Assalam Walekum", "Hello", "Hi")
        // -------------------------------------------------------------
        if ($this->isPureGreeting($q)) {
            return $this->generateGreetingResponse($lang, $voiceLang, $isRtl);
        }

        // -------------------------------------------------------------
        // INTENT 0.5: Capabilities & System Overview ("Iske bare mein batao", "Kya kar sakte hain")
        // -------------------------------------------------------------
        if (preg_match('/(iske bare|uske bare|is bare|software ke bare|kya kar sakte|aur kya kar|kya hota hai|kya kaam karta|kya feature|kya faida|what can this|what can i do|tell me about|explain software|about this software|اچھا اور کیا|اور کیا کر سکتے|سافٹ ویر کے ساتھ|سافٹویئر کے ساتھ|اس کے بارے|انوائس سافٹ ویئر|ماذا يمكن|عن هذا البرنامج|क्या कर सकते|इसके बारे)/iu', $q)) {
            $reply = match ($lang) {
                'ur' => "انوائسی (Invoicy) ایک مکمل اور جدید مالیاتی پلیٹ فارم ہے! اس کے ذریعے آپ:\n\n1. **پیشہ ورانہ انوائسز**: منٹوں میں مستند بلز اور نجی ڈرافٹس تیار کر سکتے ہیں (بشمول فوری واک اِن سیل)۔\n2. **خودکار پیمنٹ ریمائنڈرز**: گاہکوں کو خودکار یاد دہانیاں شیڈول کریں تاکہ ادائیگیاں وقت پر موصول ہوں۔\n3. **آن لائن ادائیگیاں**: سٹرائپ، پے پال اور بینک ٹرانسفر کے ذریعے آسان ادائیگی کی سہولت۔\n4. **لائیو فنانشل ٹریکنگ**: اپنے تمام واجب الادا بلز، کلائنٹ کھاتے اور آمدن کے اعدادوشمار فوری دیکھیں۔\n\nآپ ابھی کیا بنانا یا دیکھنا چاہتے ہیں؟",

                'ur-roman' => "Invoicy aap ka complete financial aur billing system hai! Is ke zariye aap:\n\n1. **Professional Invoices**: Minutes mein standard invoices aur private drafts bana sakte hain (including walk-in counter sales).\n2. **Automated Reminders**: Customers ko payment milestone reminders schedule karein taake payments waqt par milein.\n3. **Online Payments**: Stripe, PayPal aur direct bank transfer se credit card aur cash payments collect karein.\n4. **Live Financial Stats**: Overdue bills, customer accounts aur workspace cashflow real-time monitor karein.\n\nAap abhi kis kaam se shuru karna chahte hain?",

                'ar' => "منصة Invoicy هي نظامك المالي والفوترة المتكامل! يمكنك من خلالها:\n\n1. **إصدار فواتير احترافية**: إنشاء فواتير معتمدة ومسودات مؤمنة وسرية في ثوانٍ.\n2. **تذكيرات الدفع الآلية**: جدولة إشعارات تلقائية للعملاء لتحصيل المستحقات دون تأخير.\n3. **بوابات دفع إلكترونية**: تحصيل المدفوعات عبر البطاقات الائتمانية والتحويل البنكي عبر Stripe وPayPal.\n4. **إحصائيات مالية مباشرة**: متابعة الفواتير المتأخرة، سجلات العملاء، وصافي الإيرادات فورياً.\n\nما الذي ترغب في استكشافه أو بدئه الآن؟",

                'hi' => "Invoicy आपका संपूर्ण वित्तीय और बिलिंग प्लेटफ़ॉर्म है! इसके माध्यम से आप:\n\n1. **पेशेवर इनवॉइस**: मिनटों में इनवॉیس और सुरक्षित ड्राफ्ट तैयार करें।\n2. **स्वचालित पेमेंट रिमाइंडर**: ग्राहकों को समय पर भुगतान के लिए रिमाइंडर भेजें।\n3. **ऑनलाइन भुगतान**: Stripe और बैंक ट्रांसफर के माध्यम से सुरक्षित पेमेंट स्वीकार करें।\n4. **लाइव वित्तीय आँकड़े**: बकाया बिल और ग्राहक खाते आसानी से ट्रैक करें।\n\nआप अभी किस सुविधा का उपयोग करना चाहते हैं?",

                default => "Invoicy is your comprehensive financial and billing assistant! Here is what you can accomplish:\n\n1. **Professional Invoices**: Create GAAP-compliant invoices and private draft safeguards in seconds.\n2. **Automated Payment Reminders**: Schedule proactive milestone alerts so customers pay on time.\n3. **Online Payment Gateways**: Collect credit card and bank payments seamlessly via Stripe & PayPal.\n4. **Live Financial Tracking**: Monitor overdue balances, customer CRM accounts, and real-time revenue.\n\nWhat would you like to start with today?",
            };

            return [
                'reply' => $reply,
                'action' => [
                    'type' => 'navigate',
                    'url' => route('invoices.create'),
                    'label' => match ($lang) {
                        'ur' => 'نیا انوائس بنائیں ➔',
                        'ur-roman' => 'Naya Invoice Banayein ➔',
                        'ar' => 'إنشاء فاتورة جديدة ➔',
                        'hi' => 'नया इनवॉइस बनाएं ➔',
                        default => 'Create New Invoice ➔'
                    }
                ],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'],
                    'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Mera Balance & Stats', 'Support Desk Kholein'],
                    'ar' => ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'إحصائيات حسابي', 'مكتب الدعم'],
                    default => ['Create new invoice', 'How do drafts work?', 'My Workspace Stats', 'Open Support Desk'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 1: Overdue Invoices & Payment Tracking (Backend Tool Call)
        // -------------------------------------------------------------
        if ($workspace && preg_match('/\b(overdue|unpaid invoices|late payment|baki paise|kiske paise|who owes|waajib|متأخرات|فواتير غير مدفوعة|gecikmiş)\b/iu', $q)) {
            $overdueData = $this->toolGetOverdueInvoices($workspace);
            $curr = $workspace->currency ?? 'AED';

            if ($overdueData['count'] === 0) {
                $reply = match ($lang) {
                    'ur-roman' => "Mubarak ho! Aap ki koi bhi overdue ya late payment baki nahi hai. Tamam invoices paid hain ya due date abhi aani hai.",
                    'ur' => "مبارک ہو! آپ کا کوئی بھی انوائس واجب الادا (Overdue) نہیں ہے۔ تمام ادائیگیاں کلیئر ہیں۔",
                    'ar' => "رائع! ليس لديك أي فواتير متأخرة السداد حالياً. جميع الفواتير إما مسددة أو لم يحن موعد استحقاقها بعد.",
                    default => "Great news! You have zero overdue invoices in your workspace right now. All accounts are up to date."
                };
            } else {
                $listStr = "";
                foreach (array_slice($overdueData['invoices'], 0, 3) as $inv) {
                    $listStr .= "\n• **{$inv['number']}** - {$inv['client']} ({$curr} {$inv['due']}) - {$inv['days_overdue']} days overdue";
                }

                $reply = match ($lang) {
                    'ur-roman' => "Aap ke paas **{$overdueData['count']} overdue invoices** hain jinki kul rakam **{$curr} {$overdueData['total_due']}** baki hai:{$listStr}\n\nAap automated reminder bhej kar client ko alert kar sakte hain:",
                    'ur' => "آپ کے پاس **{$overdueData['count']} واجب الادا انوائسز** ہیں جن کی کل رقم **{$curr} {$overdueData['total_due']}** ہے:{$listStr}\n\nآپ یہاں سے براہ راست یاد دہانی بھیج سکتے ہیں:",
                    default => "You have **{$overdueData['count']} overdue invoices** totaling **{$curr} {$overdueData['total_due']}**:{$listStr}\n\nYou can review them or trigger automated milestone reminders:"
                };
            }

            return [
                'reply' => $reply,
                'action' => [
                    'type' => 'navigate',
                    'url' => route('invoices.index'),
                    'label' => 'View Invoices ➔'
                ],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => ['Payment Settings', 'Create Invoice', 'My Workspace Stats']
            ];
        }

        // -------------------------------------------------------------
        // INTENT 2: Customer Search / CRM Lookup (Backend Tool Call)
        // -------------------------------------------------------------
        if ($workspace && (preg_match('/\b(search customer|find customer|customer search|client record|client details|search client|dhundo|talaash|بحث عن عميل)\b/iu', $q) || preg_match('/\b(customer|client)\s+([a-zA-Z0-9\s]+)/iu', $q, $m))) {
            $term = !empty($m[2]) ? trim($m[2]) : $rawQuery;
            $term = preg_replace('/\b(search|find|details|record|karo|batao|ka)\b/iu', '', $term);
            $term = trim($term);

            $clients = $this->toolSearchCustomers($workspace, $term);
            if (count($clients) > 0) {
                $cList = "";
                foreach ($clients as $c) {
                    $cList .= "\n• **{$c['name']}** (" . ($c['company'] ?: 'Individual') . ") - Due: {$c['outstanding']}";
                }
                $reply = match ($lang) {
                    'ur-roman' => "Aap ke search criteria ke mutabiq ye clients mile hain:{$cList}\n\nAap Client CRM mein ja kar poora profile dekh sakte hain:",
                    'ur' => "آپ کے مطلوبہ ریکارڈ کے مطابق یہ کلائنٹس ملے ہیں:{$cList}",
                    default => "I found the following matching clients in your directory:{$cList}\n\nYou can open Client CRM to manage their ledger:"
                };

                return [
                    'reply' => $reply,
                    'action' => [
                        'type' => 'navigate',
                        'url' => route('clients.index'),
                        'label' => 'Open Client CRM ➔'
                    ],
                    'lang' => $voiceLang,
                    'lang_code' => $lang,
                    'is_rtl' => $isRtl,
                    'suggestions' => ['View Invoices', 'Create New Invoice', 'My Workspace Stats']
                ];
            }
        }

        // -------------------------------------------------------------
        // INTENT 3: Step-by-Step How To Use Invoices
        // -------------------------------------------------------------
        if (preg_match('/(kaise use|kaise istemal|kaise kaam|use kar sakta|use kaise|how to use|how do i use|how does it work|طريقة الاستخدام|كيف استخدم|كيفية استخدام|انوائس کیسے|کیسے استعمال|کیسے کام کرتا|उपयोग कैसे करें|nasıl kullanılır)/u', $q) || (preg_match('/(invoice|انوائس|فاتورة|fatura)/u', $q) && preg_match('/(use|kaise|kese|kare|karen|karo|batao|seekhna|karna hai|chalega)/u', $q))) {
            $reply = match ($lang) {
                'ur-roman' => "Invoicy mein invoice banana aur use karna bohat aasan hai! Aap bas 3 aasan steps follow karein:

1. **Client select karein**: Apne saved client ko chunein ya instant counter sale ke liye direct **Walk-in mode** use karein (client signup ki zaroorat nahi).
2. **Services ya items add karein**: Apne line items likhein ya hamare **1-click industry presets** se direct pre-priced rates select karein.
3. **Publish karein ya Draft save karein**: Jab tak aap draft rakhein ge, ye bilkul private rahega. 'Issue & Publish' karte hi authentic QR seal lag jata hai aur customer ke liye online payment link active ho jata hai.

Aap neeche button par click kar ke foran naya invoice bana sakte hain:",

                'ur' => "انوائسی میں انوائس بنانا اور استعمال کرنا نہایت آسان ہے! آپ صرف 3 مراحل میں اپنا بل تیار کر سکتے ہیں:

1. **کلائنٹ منتخب کریں**: اپنے رجسٹرڈ کلائنٹ کو چنیں یا فوری کاؤنٹر سیل کے لیے **واک اِن (Walk-in)** موڈ منتخب کریں (بغیر اکاؤنٹ فوری بلنگ)۔
2. **سروسز یا پروڈکٹس شامل کریں**: اپنے آئٹمز درج کریں یا ہمارے **1-کلک انڈسٹری پری سیٹس** سے ریڈی میڈ ریٹس لگائیں۔
3. **شائع (Publish) کریں یا ڈرافٹ رکھیں**: جب تک ڈرافٹ رہے گا، مکمل نجی رہے گا۔ پبلش کرتے ہی کیو آر کوڈ لگ جاتا ہے اور گاہک کے لیے سٹرائپ/بینک پورٹل فعال ہو جاتا ہے۔

ابھی نیا انوائس بنانے کے لیے نیچے دیے گئے بٹن پر کلک کریں:",

                'ar' => "استخدام الفواتير في Invoicy بسيط وسريع جداً! يمكنك إصدار فاتورة احترافية في 3 خطوات فقط:

1. **اختيار العميل**: اختر عميلاً مسجلاً أو استخدم وضع **البيع الفوري (Walk-in)** لإصدار فاتورة نقدية سريعة بنقرة واحدة.
2. **إضافة الخدمات والبنود**: أضف تفاصيل العمل أو استخدم **نماذج الخدمات الجاهزة (Presets)** بأسعارها المعتمدة.
3. **نشر الفاتورة أو حفظها كمسودة**: فواتير المسودة سرية بالكامل. وعند الضغط على نشر، يتم تفعيل رمز QR وبوابات الدفع الإلكتروني فوراً.

اضغط على الزر أدناه للبدء في إنشاء فاتورتك الآن:",

                default => "Using invoices in Invoicy is quick and straightforward! You can create and issue an invoice in 3 simple steps:

1. **Select your Client**: Choose an existing client or use **Walk-in mode** for instant retail cash sales without customer signup.
2. **Add Items or Services**: Type custom line items or choose from our **45+ 1-click industry presets** for pre-priced standard rates.
3. **Publish or Save as Draft**: Drafts remain 100% confidential. When you click Publish, a cryptographic QR seal is generated and online payment gateways are unlocked for your customer.

Click the button below to start creating your invoice:",
            };

            return [
                'reply' => $reply,
                'action' => [
                    'type' => 'navigate',
                    'url' => route('invoices.create'),
                    'label' => match ($lang) {
                        'ur' => 'نیا انوائس بنائیں ➔',
                        'ur-roman' => 'Naya Invoice Banayein ➔',
                        'ar' => 'إنشاء فاتورة جديدة الآن ➔',
                        'tr' => 'Yeni Fatura Oluştur ➔',
                        'hi' => 'नया इनवॉइस बनाएं ➔',
                        default => 'Open Invoice Builder ➔'
                    }
                ],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['ڈرافٹس کیسے کام کرتے ہیں؟', '1-کلک انڈسٹری سروسز', 'میرے فنانشل اعدادوشمار'],
                    'ur-roman' => ['Drafts kaise kaam karte hain?', '1-Click Presets', 'Mera Balance & Stats'],
                    'ar' => ['كيف تعمل المسودات؟', 'نماذج الخدمات الجاهزة', 'إحصائيات حسابي'],
                    default => ['How do drafts work?', '1-Click Presets', 'My Workspace Stats'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 4: Live Workspace Stats & Balance Queries (Backend Tool Call)
        // -------------------------------------------------------------
        if ($workspace && preg_match('/(kitna balance|kitne paise|kitni invoices|meri kamai|mera hisaab|mera balance|stats|revenue|summary|balance|outstanding|unpaid|kitna baki|kitna paisa|کتنے پیسے|کتنا بیلنس|میرے انوائسز|کتنی رقم|میرا ریوینیو|كم رصيدي|كم عدد الفواتير|المبالغ المعلقة|إيراداتي|bakiyem|toplam fatura|gelir|cuánto debo|total facturas)/u', $q)) {
            $stats = $this->getWorkspaceStats($workspace);
            $curr = $workspace->currency ?? 'AED';

            $reply = match ($lang) {
                'ur-roman' => "📊 **Aap ke Workspace ke live financial stats ye hain:**

• Total Invoices: **{$stats['total_invoices']}**
• Collected Revenue: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} Paid)
• Outstanding Due: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} Unpaid)
• Private Drafts: **{$stats['draft_count']}**
• Saved Clients: **{$stats['client_count']}**

Aap invoices dashboard khol kar har bill ki detail dekh sakte hain:",

                'ur' => "📊 **آپ کے ورک اسپیس کے تازہ ترین مالیاتی اعدادوشمار:**

• کل جاری شدہ انوائسز: **{$stats['total_invoices']}**
• وصول شدہ رقم: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} ادا شدہ)
• بقایا رقم (Unpaid): **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} واجب الادا)
• نجی ڈرافٹ انوائسز: **{$stats['draft_count']}**
• رجسٹرڈ کلائنٹس: **{$stats['client_count']}**

تفصیلات دیکھنے کے لیے نیچے دیے گئے بٹن پر کلک کریں:",

                'ar' => "📊 **ملخص الأرقام المالية لمساحة عملك حالياً:**

• إجمالي الفواتير: **{$stats['total_invoices']}**
• الإيرادات المحصلة: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} مدفوعة)
• المبالغ المعلقة والمستحقة: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} غير مدفوعة)
• فواتير المسودة: **{$stats['draft_count']}**
• العملاء المسجلون: **{$stats['client_count']}**",

                default => "📊 **Live Financial Overview for your Workspace:**

• Total Invoices: **{$stats['total_invoices']}**
• Collected Revenue: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} Paid)
• Outstanding Balance: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} Unpaid)
• Private Drafts: **{$stats['draft_count']}**
• Saved Clients: **{$stats['client_count']}**",
            };

            return [
                'reply' => $reply,
                'action' => [
                    'type' => 'navigate',
                    'url' => route('invoices.index'),
                    'label' => match ($lang) {
                        'ur' => 'تمام انوائسز دیکھیں ➔',
                        'ur-roman' => 'Invoices Dashboard Kholein ➔',
                        'ar' => 'عرض لوحة الفواتير ➔',
                        default => 'View Invoices Dashboard ➔'
                    }
                ],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['تمام انوائسز دیکھیں', 'نیا انوائس بنائیں', 'کلائنٹس دیکھیں'],
                    'ur-roman' => ['View All Invoices', 'Naya Invoice Banayein', 'Saved Clients'],
                    'ar' => ['عرض جميع الفواتير', 'إنشاء فاتورة جديدة', 'إدارة العملاء'],
                    default => ['View Invoices', 'Create New Invoice', 'Client CRM'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 5: Structured Invoicy Knowledge Retrieval (RAG)
        // -------------------------------------------------------------
        $knowledge = $this->retrieveKnowledge($q);
        if ($knowledge !== null) {
            return [
                'reply' => $knowledge['reply'][$lang] ?? $knowledge['reply']['en'],
                'action' => $knowledge['action'] ?? null,
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => $knowledge['suggestions'][$lang] ?? $this->getDefaultSuggestions($lang),
            ];
        }

        // -------------------------------------------------------------
        // NATURAL CONVERSATIONAL FALLBACK (Friendly, Helpful, Human Tone)
        // -------------------------------------------------------------
        $reply = match ($lang) {
            'ur' => "میں آپ کی مکمل مدد کے لیے حاضر ہوں۔ آپ مجھ سے نیا بل بنانے، ادائیگیاں وصول کرنے، نجی ڈرافٹس سمجھنے، یا اپنے کھاتے کے ریکارڈز کے بارے میں کچھ بھی دریافت کر سکتے ہیں۔

آپ فی الحال کس چیز میں مدد چاہتے ہیں؟",

            'ur-roman' => "Main aap ki poori madad ke liye hazir hoon! Aap mujhse naya invoice banane, payment gateways configure karne, drafts samajhne ya apne account ke hisaab kitaab ke bare mein pooch sakte hain.

Aap abhi kis cheez ke bare mein janna chahte hain?",

            'ar' => "أنا هنا دائماً لمساعدتك وخدمتك! يمكنك سؤالي عن إصدار الفواتير، قبول المدفوعات الإلكترونية، حماية المسودات، أو مراجعة أرقامك المالية.

كيف ترغب أن نبدأ اليوم؟",

            default => "I'm right here to assist you! You can ask me how to create invoices, accept online customer payments, understand draft safeguards, or view your workspace stats.

What would you like to explore today?",
        };

        return [
            'reply' => $reply,
            'lang' => $voiceLang,
            'lang_code' => $lang,
            'is_rtl' => $isRtl,
            'suggestions' => $this->getDefaultSuggestions($lang),
        ];
    }

    /**
     * Comprehensive Structured RAG Knowledge Base.
     */
    protected function retrieveKnowledge(string $q): ?array
    {
        // 1. Draft Safeguards
        if (preg_match('/\b(draft|drafts|why draft|publish|private bill|مسودہ|مسودة|taslak)\b/iu', $q)) {
            return [
                'reply' => [
                    'en' => "🔒 **Draft Invoice Safeguards & Confidentiality:**\n\nInvoicy keeps draft invoices 100% private. While in draft:\n1. No client emails or payment notices are dispatched.\n2. PDF downloads and customer payment portals are disabled.\n3. Cryptographic QR verification codes remain inactive.\n\nOnly when you click **'Issue & Publish'** does the invoice become official.",
                    'ur-roman' => "🔒 **Draft Invoices ki Security & Safeguards:**\n\nInvoicy mein draft invoices 100% private rehti hain. Jab tak aap draft rakhein:\n1. Customer ko koi email ya notification nahi jati.\n2. PDF downloads aur online payment portal links disabled rehte hain.\n3. ISO QR verification code inactive rehta hai.\n\nJab aap **'Issue & Publish'** par click karein ge, tabhi invoice official hota hai.",
                    'ur' => "🔒 **ڈرافٹ انوائسز کی رازداری اور سیکیورٹی:**\n\nانوائسی میں ڈرافٹ انوائسز مکمل نجی رہتی ہیں۔ جب تک آپ ڈرافٹ رکھیں:\n1. کلائنٹ کو کوئی نوٹیفکیشن یا ای میل نہیں جاتی۔\n2. پی ڈی ایف ڈاؤن لوڈ اور آن لائن پیمنٹ لنکس غیر فعال رہتے ہیں۔\n3. کیو آر تصدیقی کوڈ لاک رہتا ہے۔",
                    'ar' => "🔒 **حماية وسرية فواتير المسودة (Draft):**\n\nفواتير المسودة سرية للغاية ومؤمنة في Invoicy. لا يتم إرسال أي بريد للعميل وتظل روابط الدفع ورمز QR معطلة حتى تضغط على 'نشر الفاتورة'."
                ],
                'action' => ['type' => 'navigate', 'url' => route('invoices.create'), 'label' => 'Create Draft Invoice ➔'],
                'suggestions' => ['How do I recall an invoice?', 'Explain QR code', 'My Workspace Stats']
            ];
        }

        // 2. 45+ Industry Presets & Catalog
        if (preg_match('/\b(preset|presets|industry|catalog|computer shop|transportation|limo|limousine|industrial|construction|distributor|marketing|پری سیٹ|قوالب)\b/iu', $q)) {
            return [
                'reply' => [
                    'en' => "📦 **45+ 1-Click Industry Presets:**\n\nInvoicy includes pre-configured service catalogs for 7 major industries:\n• **Computer Shop:** Hardware diagnostics, OS installation, SSD upgrade.\n• **Transportation & Logistics:** Pallet freight, cross-border haulage.\n• **Limousine & Taxi:** Airport VIP transfer, hourly chauffeur.\n• **Construction & Industrial:** Concrete pouring, CNC machining.\n• **Wholesale & Marketing:** Bulk distribution, SEO retainer, social ads.",
                    'ur-roman' => "📦 **45+ 1-Click Industry Presets:**\n\nInvoicy mein 7 major industries ke liye ready-made presets hain:\n• Computer Shop (hardware repair, SSD upgrade)\n• Transportation & Logistics (route freight, pallets)\n• Limousine Taxi (airport transfer, chauffeur)\n• Construction & Manufacturing (site work, fabrication)\n• Marketing Agencies (SEO retainers, ad campaigns)",
                    'ur' => "📦 **45+ انڈسٹری سروس پری سیٹس:**\n\nانوائسی میں 7 بڑی صنعتوں کے لیے 45 سے زائد پری سیٹس شامل ہیں جن میں کمپیوٹر شاپ، لاجسٹکس، لیموزین، تعمیرات اور مارکیٹنگ ایجنسیاں شامل ہیں۔",
                    'ar' => "📦 **أكثر من 45 قالباً ذكياً لسبع قطاعات تجارية:**\n\nتوفر المنصة قوالب جاهزة لقطاعات صيانة الحواسيب، النقل واللوجستيات، الليموزين، المقاولات، والتسويق الإلكتروني."
                ],
                'action' => ['type' => 'navigate', 'url' => route('invoices.create'), 'label' => 'Explore Presets in Builder ➔'],
                'suggestions' => ['Create new invoice', 'How do drafts work?', 'Payment Gateways']
            ];
        }

        // 3. Invoice Recall Feature
        if (preg_match('/\b(recall|withdraw|revert|edit published|mistake|ریکال|سحب الفاتورة)\b/iu', $q)) {
            return [
                'reply' => [
                    'en' => "🔄 **Invoice Recall Feature:**\n\nIf you published an invoice with an error, open the invoice and click **'Recall to Draft'**.\n\nThis immediately:\n1. Locks the public payment portal and revokes client link access.\n2. Reverts the invoice to a private draft.\n3. Allows you to make edits, correct line items, and republish safely.",
                    'ur-roman' => "🔄 **Invoice Recall Feature:**\n\nAgar aap ne invoice publish kar di hai aur koi ghalti ho gayi hai, toh aap invoice view page par ja kar **'Recall to Draft'** click kar sakte hain.\n\nIs se customer ka online link foran lock ho jata hai aur aap aasaani se changes kar ke dobara publish kar sakte hain.",
                    'ur' => "🔄 **انوائس ریکال فیچر:**\n\nاگر شائع شدہ انوائس میں کوئی غلطی ہو جائے تو آپ 'Recall to Draft' پر کلک کر کے پبلک پورٹل کو فوری لاک کر سکتے ہیں اور باحفاظت تصحیح کر کے دوبارہ شائع کر سکتے ہیں۔",
                    'ar' => "🔄 **ميزة استرجاع الفاتورة (Recall):**\n\nيمكنك الضغط على زر 'Recall to Draft' من صفحة الفاتورة لقفل الرابط الخارجي فوراً وإعادتها كمسودة لتعديلها بأمان."
                ],
                'action' => ['type' => 'navigate', 'url' => route('invoices.index'), 'label' => 'Go to Invoices ➔'],
                'suggestions' => ['View Invoices', 'Create New Invoice', 'Support Desk']
            ];
        }

        // 4. Payment Gateways (Stripe, PayPal, Bank)
        if (preg_match('/\b(stripe|paypal|bank wire|apple pay|credit card|payment gateway|طرق الدفع|ادائیگی)\b/iu', $q)) {
            return [
                'reply' => [
                    'en' => "💳 **Payment Gateway Integrations:**\n\nInvoicy supports multiple online and offline payment channels:\n• **Stripe:** Accepts credit/debit cards, Apple Pay, and Google Pay.\n• **PayPal:** International buyer protection and PayPal checkout.\n• **Bank Wire Transfer:** Automatically attaches your IBAN, Swift, and routing instructions to invoice PDFs and client portals.",
                    'ur-roman' => "💳 **Payment Gateways in Invoicy:**\n\nInvoicy mein aap Stripe (Credit card, Apple Pay), PayPal, aur Direct Bank Transfer configure kar sakte hain. Customer invoice portal se direct card se payment kar sakta hai.",
                    'ur' => "💳 **آن لائن پیمنٹ گیٹ ویز:**\n\nآپ سٹرائپ (کریڈٹ کارڈز، ایپل پے)، پے پال اور لوکل بینک ٹرانسفر کو ایکٹیویٹ کر سکتے ہیں تاکہ کلائنٹ ایک کلک میں انوائس ادا کر سکے۔",
                    'ar' => "💳 **بوابات الدفع الإلكتروني:**\n\nتدعم المنصة الربط مع Stripe (لبطاقات الائتمان وApple Pay) وPayPal وحسابات التحويل البنكي المباشرة."
                ],
                'action' => ['type' => 'navigate', 'url' => route('settings.payment'), 'label' => 'Configure Payment Gateways ➔'],
                'suggestions' => ['Payment Settings', 'My Workspace Stats', 'Create Invoice']
            ];
        }

        // 5. 24h SLA Support Desk
        if (preg_match('/\b(support|help desk|ticket|problem|complaint|سپورٹ|تذكرة|دعم)\b/iu', $q)) {
            return [
                'reply' => [
                    'en' => "🎫 **24h Turnaround SLA Support Desk:**\n\nOur dedicated engineering and support teams guarantee a verified resolution within 24 hours for all billing, gateway, and technical tickets.",
                    'ur-roman' => "🎫 **24h SLA Support Desk:**\n\nHamari senior technical team 24 hours ke andar har billing aur technical ticket ka hal faraham karti hai. Aap naya ticket open kar sakte hain ya existing status track kar sakte hain.",
                    'ur' => "🎫 **24 گھنٹے کا سپورٹ ڈیسک:**\n\nہماری سینئر انجینئرنگ ٹیم 24 گھنٹے کے اندر ہر ٹکٹ کا حل فراہم کرتی ہے۔",
                    'ar' => "🎫 **مكتب الدعم الفني وضمان الاستجابة خلال 24 ساعة:**\n\nفريقنا المتخصص ملتزم بمعالجة وحل جميع التذاكر الفنية في أقل من 24 ساعة."
                ],
                'action' => ['type' => 'navigate', 'url' => route('support.index'), 'label' => 'Open Support Desk ➔'],
                'suggestions' => ['Open New Ticket', 'View Active Tickets', 'My Workspace Stats']
            ];
        }

        return null;
    }

    /**
     * BACKEND TOOL: Atomically create a real draft invoice in workspace.
     */
    public function toolCreateInvoiceDraft(Workspace $workspace, string $clientName, float $amount, ?string $currency = null, ?string $dueDate = null, ?string $notes = null, $user = null): array
    {
        $currency = $currency ?: ($workspace->currency ?? 'AED');
        $userId = $user ? $user->id : (Auth::id() ?? 1);

        // 1. Find or create Client record
        $client = Client::withoutGlobalScopes()->firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'name' => $clientName,
            ],
            [
                'email' => Str::slug($clientName) . '@client.local',
                'preferred_currency' => $currency,
            ]
        );

        // 2. Generate Next Invoice Sequence Number
        $numberService = app(InvoiceNumberService::class);
        $invNumber = $numberService->generateNextNumber($workspace);

        $amountCents = (int) round($amount * 100);
        $dueParsed = $this->parseDueDate($dueDate);

        // 3. Create Draft Invoice
        $invoice = Invoice::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'invoice_number' => $invNumber,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => $dueParsed,
            'currency' => $currency,
            'subtotal_cents' => $amountCents,
            'tax_name' => 'VAT',
            'tax_rate' => 0,
            'tax_cents' => 0,
            'discount_cents' => 0,
            'total_cents' => $amountCents,
            'paid_cents' => 0,
            'due_cents' => $amountCents,
            'notes' => $notes ?: 'Created via Invoicy AI Copilot',
            'created_by_user_id' => $userId,
            'workspace_snapshot' => ['name' => $workspace->name, 'currency' => $currency],
            'client_snapshot' => ['name' => $client->name, 'email' => $client->email],
        ]);

        // 4. Create primary invoice line item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Professional Billing Services',
            'category' => 'General',
            'quantity' => 1,
            'rate_cents' => $amountCents,
            'amount_cents' => $amountCents,
        ]);

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invNumber,
            'client_name' => $client->name,
            'total' => number_format($amount, 2) . ' ' . $currency,
            'due_date' => $dueParsed,
            'status' => 'draft',
            'url' => route('invoices.show', $invoice->id),
        ];
    }

    /**
     * BACKEND TOOL: Search customers in current workspace.
     */
    public function toolSearchCustomers(Workspace $workspace, string $term): array
    {
        $clients = Client::where('workspace_id', $workspace->id)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('company_name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%");
            })
            ->take(5)
            ->get();

        $curr = $workspace->currency ?? 'AED';
        $results = [];
        foreach ($clients as $c) {
            $results[] = [
                'id' => $c->id,
                'name' => $c->name,
                'company' => $c->company_name,
                'email' => $c->email,
                'outstanding' => $curr . ' ' . number_format($c->outstanding_balance_cents / 100, 2),
                'url' => route('clients.index')
            ];
        }
        return $results;
    }

    /**
     * BACKEND TOOL: Get overdue invoices in workspace.
     */
    public function toolGetOverdueInvoices(Workspace $workspace): array
    {
        $overdue = Invoice::where('workspace_id', $workspace->id)
            ->whereIn('status', ['overdue', 'unpaid', 'sent', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->with('client')
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get();

        $curr = $workspace->currency ?? 'AED';
        $totalDueCents = 0;
        $invoices = [];

        foreach ($overdue as $inv) {
            $dueCents = ($inv->total_cents ?? 0) - ($inv->paid_cents ?? 0);
            $totalDueCents += $dueCents;
            $days = Carbon::parse($inv->due_date)->diffInDays(now());

            $invoices[] = [
                'number' => $inv->invoice_number,
                'client' => $inv->client?->name ?: 'Client',
                'due' => number_format($dueCents / 100, 2),
                'days_overdue' => $days,
                'url' => route('invoices.show', $inv->id)
            ];
        }

        return [
            'count' => $overdue->count(),
            'total_due' => number_format($totalDueCents / 100, 2),
            'invoices' => $invoices,
        ];
    }

    /**
     * Parse human due dates into YYYY-MM-DD.
     */
    protected function parseDueDate(?string $str): string
    {
        if (empty($str)) {
            return now()->addDays(15)->toDateString();
        }

        $s = mb_strtolower(trim($str));

        if (str_contains($s, 'tomorrow') || str_contains($s, 'kal')) {
            return now()->addDay()->toDateString();
        }
        if (str_contains($s, 'next month') || str_contains($s, 'agle mahine') || str_contains($s, 'الشهر القادم')) {
            return now()->addMonth()->toDateString();
        }
        if (str_contains($s, 'week') || str_contains($s, 'hafta')) {
            return now()->addWeek()->toDateString();
        }
        if (preg_match('/(\d+)\s*(days|din|يوم)/i', $s, $m)) {
            return now()->addDays((int) $m[1])->toDateString();
        }
        if (str_contains($s, 'receipt') || str_contains($s, 'foran') || str_contains($s, 'instant')) {
            return now()->toDateString();
        }

        try {
            return Carbon::parse($str)->toDateString();
        } catch (\Throwable $e) {
            return now()->addDays(15)->toDateString();
        }
    }

    /**
     * Extract customer name from natural query.
     */
    protected function extractCustomerName(string $text): ?string
    {
        if (preg_match('/(?:for|client|customer|naam|name|braye|برعاية|لشركة|للعميل)\s+([A-Za-z0-9\s\.\,\-\&]{2,30})/iu', $text, $m)) {
            $name = trim($m[1]);
            // clean up tailing numbers or keywords
            $name = preg_replace('/\s+\d+.*$/', '', $name);
            return trim($name);
        }
        return null;
    }

    /**
     * Extract amount and currency from text.
     */
    protected function extractAmount(string $text): ?array
    {
        if (preg_match('/([\d\.\,]+)\s*(aed|usd|eur|gbp|sar|pkr|inr|درهم|دولار|روپے)?/iu', $text, $m)) {
            $rawNum = str_replace(',', '', $m[1]);
            if (is_numeric($rawNum)) {
                $curr = !empty($m[2]) ? strtoupper(trim($m[2])) : 'AED';
                if ($curr === 'درهم') $curr = 'AED';
                if ($curr === 'دولار') $curr = 'USD';
                return ['amount' => (float) $rawNum, 'currency' => $curr];
            }
        }
        return null;
    }

    /**
     * Try external LLM (OpenAI or Gemini) if configured.
     */
    protected function tryExternalLlm(string $rawQuery, string $langCode, ?Workspace $workspace, $user, array $history): ?array
    {
        $openaiKey = CmsContent::getVal('openai_api_key', config('services.openai.api_key', ''));
        $geminiKey = CmsContent::getVal('gemini_api_key', config('services.gemini.api_key', ''));
        $model = CmsContent::getVal('ai_model', 'gpt-4o-mini');
        $provider = CmsContent::getVal('ai_provider', 'auto');

        if (($provider === 'openai' || $provider === 'auto') && !empty($openaiKey)) {
            try {
                $wsContext = $this->buildWorkspaceSummaryText($workspace);
                $systemPrompt = $this->buildSystemPrompt($langCode, $wsContext);

                $messages = [['role' => 'system', 'content' => $systemPrompt]];
                foreach (array_slice($history, -8) as $h) {
                    $messages[] = ['role' => $h['role'] === 'user' ? 'user' : 'assistant', 'content' => $h['content']];
                }

                $res = Http::withToken($openaiKey)
                    ->timeout(8)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 0.4,
                        'max_tokens' => 600,
                    ]);

                if ($res->successful()) {
                    $json = $res->json();
                    $reply = $json['choices'][0]['message']['content'] ?? null;
                    if (!empty($reply)) {
                        return [
                            'reply' => $reply,
                            'lang' => $this->getVoiceTag($langCode),
                            'lang_code' => $langCode,
                            'is_rtl' => in_array($langCode, ['ur', 'ar']),
                            'suggestions' => $this->getDefaultSuggestions($langCode),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI invocation error: ' . $e->getMessage());
            }
        }

        if (($provider === 'gemini' || ($provider === 'auto' && empty($openaiKey))) && !empty($geminiKey)) {
            try {
                $wsContext = $this->buildWorkspaceSummaryText($workspace);
                $systemPrompt = $this->buildSystemPrompt($langCode, $wsContext);
                $geminiModel = Str::contains($model, 'gemini') ? $model : 'gemini-1.5-flash';

                $res = Http::timeout(8)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiKey}", [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [['text' => "{$systemPrompt}\n\nUser Question: {$rawQuery}"]]
                            ]
                        ],
                    ]);

                if ($res->successful()) {
                    $json = $res->json();
                    $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($reply)) {
                        return [
                            'reply' => $reply,
                            'lang' => $this->getVoiceTag($langCode),
                            'lang_code' => $langCode,
                            'is_rtl' => in_array($langCode, ['ur', 'ar']),
                            'suggestions' => $this->getDefaultSuggestions($langCode),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini invocation error: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Get live workspace statistics.
     */
    public function getWorkspaceStats(Workspace $workspace): array
    {
        $totalInvoices = Invoice::where('workspace_id', $workspace->id)->count();
        $paidInvoices = Invoice::where('workspace_id', $workspace->id)->where('status', 'paid')->get();
        $unpaidInvoices = Invoice::where('workspace_id', $workspace->id)->whereIn('status', ['unpaid', 'overdue', 'partially_paid', 'sent'])->get();
        $draftCount = Invoice::where('workspace_id', $workspace->id)->where('status', 'draft')->count();
        $clientCount = Client::where('workspace_id', $workspace->id)->count();

        $paidCents = $paidInvoices->sum('total_cents');
        $unpaidCents = $unpaidInvoices->sum(function ($inv) {
            return ($inv->total_cents ?? 0) - ($inv->paid_cents ?? 0);
        });

        return [
            'total_invoices' => $totalInvoices,
            'paid_count' => $paidInvoices->count(),
            'paid_revenue' => number_format($paidCents / 100, 2),
            'unpaid_count' => $unpaidInvoices->count(),
            'outstanding_revenue' => number_format($unpaidCents / 100, 2),
            'draft_count' => $draftCount,
            'client_count' => $clientCount,
        ];
    }

    /**
     * Build text summary of workspace for LLM prompt context.
     */
    protected function buildWorkspaceSummaryText(?Workspace $workspace): string
    {
        if (!$workspace) {
            return "Workspace: Default Workspace";
        }

        $stats = $this->getWorkspaceStats($workspace);
        $curr = $workspace->currency ?? 'AED';

        return "Active Workspace: '{$workspace->name}', Currency: {$curr}, Total Invoices: {$stats['total_invoices']}, Collected Revenue: {$curr} {$stats['paid_revenue']}, Outstanding Due: {$curr} {$stats['outstanding_revenue']}, Drafts: {$stats['draft_count']}, Saved Clients: {$stats['client_count']}.";
    }

    /**
     * Build system prompt for external LLM.
     */
    protected function buildSystemPrompt(string $langCode, string $wsContext): string
    {
        $customPrompt = CmsContent::getVal('ai_system_prompt', '');

        $langDirective = match ($langCode) {
            'ur-roman' => "MANDATORY LANGUAGE: The user is writing in Roman Urdu (Urdu written in Latin alphabet, e.g. 'Assalam Walekum', 'naya invoice banana hai'). You MUST reply strictly in Roman Urdu. NEVER reply in English or Devanagari script.",
            'ur' => "MANDATORY LANGUAGE: The user is writing in Urdu script. You MUST reply strictly in native Urdu (Nastaliq/Arabic alphabet). NEVER reply in English or Arabic.",
            'ar' => "MANDATORY LANGUAGE: The user is writing in Arabic. You MUST reply strictly in fluent, professional Arabic. NEVER reply in English.",
            'hi' => "MANDATORY LANGUAGE: The user is writing in Hindi (Devanagari script). You MUST reply strictly in Hindi.",
            'tr' => "MANDATORY LANGUAGE: The user is writing in Turkish. You MUST reply strictly in Turkish.",
            'es' => "MANDATORY LANGUAGE: The user is writing in Spanish. You MUST reply strictly in Spanish.",
            'fr' => "MANDATORY LANGUAGE: The user is writing in French. You MUST reply strictly in French.",
            'de' => "MANDATORY LANGUAGE: The user is writing in German. You MUST reply strictly in German.",
            default => "MANDATORY LANGUAGE: The user is writing in English. Reply in English."
        };
        
        $base = "{$langDirective}

You are the conversational financial AI Agent for 'Invoicy by RedMaple', a high-precision multi-tenant SaaS billing platform.
You are knowledgeable, conversational, helpful, concise, and speak naturally like a human assistant.

Key Platform Knowledge:
- Fast Invoicing: Full client invoices or Walk-in instant cash counter billing without signup.
- 45+ 1-Click Industry Presets across 7 sectors (Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, Marketing Agency).
- Draft Safeguard: Draft invoices are 100% private. No emails are sent, and PDF downloads, payment links, and QR codes are disabled until published.
- Automated Payment Reminders: Triggered at 45, 30, 15, 7, 3, and 2 days before due date.
- Recall Option: Published invoices can be recalled to draft for safe corrections.
- ISO QR Verification: Tamper-proof cryptographic 2D QR seal on all published invoices.
- 24h SLA Support Desk: Dedicated ticketing support.
- Payment Gateways: Stripe, PayPal, offline bank transfers.

CONVERSATIONAL RULES:
- Detect the user's language automatically and reply strictly in the user's language.
- When user greets (e.g. 'Assalam Walekum', 'Hello', 'Hi'), reply warmly and politely without robotic feature dumping.
- Never dump all 5 platform features in a giant bullet list unless specifically asked 'What are all the features?'.
- Keep responses friendly, conversational, concise, and offer clear next steps.

{$wsContext}";

        if (!empty($customPrompt)) {
            $base .= "\n\nAdditional Administrator Instructions:\n" . $customPrompt;
        }

        return $base;
    }

    /**
     * Default suggestions for chat pill interface.
     */
    public function getDefaultSuggestions(string $lang): array
    {
        return match ($lang) {
            'ur' => ['نیا انوائس بنائیں', 'انوائس کیسے استعمال کریں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'],
            'ur-roman' => ['Naya Invoice Banayein', 'Invoice kaise use karein?', 'Mera Balance & Stats', 'Support Desk Kholein'],
            'ar' => ['إنشاء فاتورة جديدة', 'كيف أستخدم الفواتير؟', 'إحصائيات حسابي', 'مكتب الدعم'],
            'tr' => ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'],
            'hi' => ['नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'त्वरित प्रीसेट', 'सपोर्ट डेस्क खोलें'],
            default => ['Create new invoice', 'How do I use invoices?', 'My Workspace Stats', 'Open Support Desk'],
        };
    }

    /**
     * Initial greeting when user opens widget or message is empty.
     */
    public function getEmptyGreeting(string $lang): string
    {
        return match ($lang) {
            'ur' => 'السلام علیکم! میں آپ کا AI ایجنٹ ہوں۔ انوائسز، ڈرافٹس، ریکال آپشن، سیٹنگز یا سپورٹ کے بارے میں پوچھیں یا مائیک سے بات کریں!',
            'ur-roman' => 'Hello! Main aap ka Invoicy AI Agent hoon. Naya invoice banane, drafts, presets, payment gateways ya support desk ke bare me poochein ya mic se bolein!',
            'ar' => 'مرحباً! أنا وكيل الذكاء الاصطناعي لمنصة Invoicy. اسألني عن فواتيرك، المسودات، الإعدادات، أو اضغط المايك للأوامر الصوتية!',
            'tr' => 'Merhaba! Ben Invoicy AI Asistanınızım. Faturalarınız, taslaklar, ayarlar veya destek masası hakkında soru sorabilir veya mikrofon ile konuşabilirsiniz!',
            'hi' => 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉइस, ड्राफ्ट, सेटिंग्स या सपोर्ट के बारे में पूछें या माइक से बात करें!',
            default => 'Hello! I am your Invoicy AI Assistant. Ask me anything about your invoices, settings, draft safeguards, support tickets, or click the mic to speak!',
        };
    }

    /**
     * Record assistant reply in session history.
     */
    protected function recordAssistantReply(string $reply): void
    {
        $history = session()->get('ai_copilot_history', []);
        $history[] = ['role' => 'assistant', 'content' => $reply, 'time' => now()->toIso8601String()];
        if (count($history) > 16) {
            $history = array_slice($history, -16);
        }
        session()->put('ai_copilot_history', $history);
    }

    /**
     * Clear conversation history and reset active dialogue flows.
     */
    public function clearHistory(): void
    {
        session()->forget('ai_copilot_history');
        session()->forget('ai_dialogue_state');
    }

    /**
     * Structured Logging for Issue 4 Auditability.
     */
    protected function logInteraction(string $query, string $lang, string $engine, array $response, float $latency): void
    {
        Log::channel('daily')->info('[INVOICY_AI_AGENT]', [
            'query' => Str::limit($query, 120),
            'lang' => $lang,
            'engine' => $engine,
            'has_action' => isset($response['action']),
            'latency_ms' => round($latency * 1000, 2),
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
