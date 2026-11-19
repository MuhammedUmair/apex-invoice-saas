<?php

namespace App\Services;

use App\Models\CmsContent;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\SupportTicket;
use App\Models\Workspace;
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
        $rawQuery = trim($query);
        $q = mb_strtolower($rawQuery, 'UTF-8');

        // 1. Language Detection & Normalization
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
        if (count($history) > 12) {
            $history = array_slice($history, -12);
        }
        session()->put('ai_copilot_history', $history);

        // 3. Attempt External LLM if Configured (OpenAI / Gemini)
        $externalResponse = $this->tryExternalLlm($rawQuery, $langCode, $workspace, $user, $history);
        if ($externalResponse !== null) {
            $this->recordAssistantReply($externalResponse['reply']);
            return $externalResponse;
        }

        // 4. Fallback to Localized Intelligence Engine (100% reliable, zero downtime)
        $localResponse = $this->generateLocalIntelligenceResponse($q, $rawQuery, $langCode, $voiceLang, $isRtl, $workspace, $user);
        $this->recordAssistantReply($localResponse['reply']);
        return $localResponse;
    }

    /**
     * Resolve incoming query language.
     * Guaranteed never to mistake Urdu for Arabic.
     */
    public function resolveLanguage(string $q, ?string $selectedLang = null): string
    {
        // If user manually chose a specific language (not auto), honor it unless query is in a distinct script
        if (!empty($selectedLang) && $selectedLang !== 'auto' && in_array($selectedLang, ['en', 'ur', 'ur-roman', 'ar', 'hi', 'tr', 'es', 'fr', 'de', 'zh'])) {
            // Still check if query contains native Urdu or Arabic script to avoid mismatch
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
        // 1. Native Urdu Script Detection (FIRST: prevents Urdu from ever hitting Arabic regex)
        if ($this->isNativeUrdu($q)) {
            return 'ur';
        }

        // 2. Native Arabic Script Detection
        if ($this->isNativeArabic($q)) {
            return 'ar';
        }

        // 3. Any Remaining Arabic/Urdu Unicode Block (\x{0600}-\x{06FF})
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $q)) {
            // Check for any Urdu words, suffixes or grammar
            if (preg_match('/(کیا|ہے|ہیں|کے|ساتھ|سے|کا|کی|کو|میں|پر|اچھا|اور|ہم|آپ|بتاؤ|بتائیں|بتا|کرو|کریں|کر|ہو|ہونا|کرنا|سکتے|سکتا|سکتی|نہیں|بھی|یہ|وہ|کیسے|کون|کیوں|انوائس|نوائس|سافٹویئر|سافٹ|پیسہ|پیسے|رقم|ڈرافٹ)/u', $q)) {
                return 'ur';
            }
            // Check for explicit Arabic words
            if (preg_match('/(فاتورة|فواتير|انشاء|إنشاء|مسودة|مسودات|دفع|عميل|عملاء|مكتب|تذكرة|مساعدة|كيف|اريد|أريد|شكرا|شكرًا|الرجاء|ممكن|تفاصيل|يرجى|نحن|أنت|ماذا|يمكنني)/u', $q)) {
                return 'ar';
            }
            // If neither matches, check app locale
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

        // 6. Roman Urdu / Hinglish (e.g. "iske bare mein batao", "naya invoice kaise banayein", "kya kar sakte hain")
        if (preg_match('/\b(iske|uske|is|iss|bare|baare|mein|me|batao|bataiye|bataen|bata|kya|hai|hain|kaise|kese|kare|karen|karo|kar|banaye|banayein|banao|madad|shukriya|mujhe|chahiye|kahan|theek|acha|accha|nahi|nhi|haan|bhi|aur|ye|yeh|wo|woh|apna|apni|karna|hoga|hota|chalega|bolo|bhejo|dekho|dikhao|hisaab|paisa|paise|kharcha|kamai|kitna|kitne|mera|meri|mere|saath|sath|kuch|btao|btaen|sakte|sakta|saktee)\b/i', $q)) {
            return 'ur-roman';
        }

        // 7. Turkish keywords
        if (preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $q) || preg_match('/\b(merhaba|fatura|faturayı|ayarlar|yardım|nasıl|oluştur|müşteri|hatırlatıcı|destek|bilet)\b/i', $q)) {
            return 'tr';
        }

        // 8. Spanish keywords
        if (preg_match('/[áéíóúñ¿¡]/u', $q) || preg_match('/\b(hola|factura|configuración|ayuda|cómo|crear|cliente|recordatorio|soporte|gracias)\b/i', $q)) {
            return 'es';
        }

        // 9. French keywords
        if (preg_match('/[àâçéèêëîïôûùüÿœæ]/u', $q) || preg_match('/\b(bonjour|facture|paramètres|aide|comment|créer|client|rappel|merci)\b/i', $q)) {
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
        // Exclusive Urdu characters: ٹ, ڈ, ڑ, ں, ے, ۂ, ہ, ھ, چ, پ, ژ, گ, ک, ۓ
        if (preg_match('/[\x{0679}\x{0688}\x{0691}\x{06BA}\x{06D2}\x{06C2}\x{06C1}\x{06BE}\x{0686}\x{067E}\x{0698}\x{06AF}\x{06A9}\x{06D3}]/u', $q)) {
            return true;
        }

        // Common Urdu grammatical tokens and vocabulary
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
        // Exclusive Arabic letters: ة, ى, ئ, ؤ, إ, أ, آ
        if (preg_match('/[\x{0629}\x{0649}\x{0626}\x{0624}\x{0625}\x{0623}\x{0622}]/u', $q)) {
            // Confirm it doesn't also have Urdu markers
            if (!$this->isNativeUrdu($q)) {
                return true;
            }
        }

        // Common Arabic words
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
     * Try external LLM (OpenAI or Gemini) if configured in SuperAdmin or .env.
     */
    protected function tryExternalLlm(string $rawQuery, string $langCode, ?Workspace $workspace, $user, array $history): ?array
    {
        $openaiKey = CmsContent::getVal('openai_api_key', config('services.openai.api_key', ''));
        $geminiKey = CmsContent::getVal('gemini_api_key', config('services.gemini.api_key', ''));
        $model = CmsContent::getVal('ai_model', 'gpt-4o-mini');
        $provider = CmsContent::getVal('ai_provider', 'auto');

        // Check if OpenAI is usable
        if (($provider === 'openai' || $provider === 'auto') && !empty($openaiKey)) {
            try {
                $wsContext = $this->buildWorkspaceSummaryText($workspace);
                $systemPrompt = $this->buildSystemPrompt($langCode, $wsContext);

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach (array_slice($history, -6) as $h) {
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
                Log::warning('OpenAI Copilot invocation error: ' . $e->getMessage());
            }
        }

        // Check if Gemini is usable
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
                Log::warning('Gemini Copilot invocation error: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Localized High-Precision AI Reasoning Engine.
     */
    protected function generateLocalIntelligenceResponse(string $q, string $rawQuery, string $lang, string $voiceLang, bool $isRtl, ?Workspace $workspace, $user): array
    {
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        // -------------------------------------------------------------
        // INTENT 0: Overview & Capabilities ("Iske bare mein batao" / "اچھا اور کیا کر سکتے ہیں")
        // -------------------------------------------------------------
        if (preg_match('/(iske bare mein|uske bare mein|ye kya hai|yeh kya hai|is ke bare|about|overview|kya karta hai|batao|explain invoicy|invoicy kya hai|kya kar sakte|aur kya|کیا کر سکتے|سافٹویئر کے ساتھ|سافٹ ویئر|اس کے بارے|پلیٹ فارم کیا ہے|ماذا يمكنني|عن المنصة|ما هي ميزات|qu\'est-ce que|was kann|que se puede)/u', $q)) {
            $reply = match ($lang) {
                'ur' => "انوائسی (Invoicy) ایک مکمل کلاؤڈ بلنگ اور فنانشل مینجمنٹ پلیٹ فارم ہے! آپ اس سافٹ ویئر کے ساتھ یہ سب کچھ کر سکتے ہیں:

1. ⚡ **پروفیشنل انوائسز بنائیں**: رجسٹرڈ کلائنٹس کے لیے تفصیلی بل یا بغیر اکاؤنٹ واک اِن (Walk-in) کیش سیلز۔
2. 📦 **45+ انڈسٹری پری سیٹس**: کمپیوٹر شاپ، ٹرانسپورٹ، لیموزین، تعمیرات، انڈسٹریل، ڈسٹری بیوٹر اور مارکیٹنگ کے لیے 1-کلک ریٹس۔
3. 🔒 **محفوظ ڈرافٹس (Drafts)**: جب تک آپ 'Issue & Publish' نہ کریں، گاہک کو کوئی ای میل نہیں جائے گی اور پی ڈی ایف محفوظ رہے گی۔
4. ⏰ **خودکار ادائیگی کے ریمائنڈرز**: ڈیو ڈیٹ سے 45، 30، 15، 7، 3 اور 2 دن پہلے خودکار نوٹیفکیشنز۔
5. 💳 **آن لائن ادائیگیاں**: سٹرائپ (Stripe)، پے پال (PayPal) اور لوکل بینک ٹرانسفر کے ذریعے رقوم وصول کریں۔
6. 🛡️ **کیو آر کوڈ تصدیق**: جعلسازی سے پاک آئی ایس او (ISO) ڈیجیٹل ویریفکیشن۔
7. 🎫 **24 گھنٹے کا سپورٹ ڈیسک**: کسی بھی مسئلے کی صورت میں ہماری انجینئرنگ ٹیم فوری حل فراہم کرتی ہے۔

آپ کیا کرنا چاہتے ہیں؟ نیا انوائس بنائیں یا کوئی فیچر سمجھنا چاہتے ہیں؟",

                'ur-roman' => "Ye Invoicy ka advanced SaaS financial platform hai! Is software ke sath aap ye tamam operations perform kar sakte hain:

1. ⚡ **Naya Invoice Create Karein**: Registered clients ke liye detail bill ya walk-in retail counter mode me instant cash billing.
2. 📦 **45+ Industry Service Presets**: Computer shop, transportation, limousine taxi, industrial, construction, distribution, aur marketing agencies ke liye 1-click ready made items.
3. 🔒 **Private Draft Safeguards**: Draft invoice customer ko bilkul dispatch nahi hoti jab tak aap use 'Issue & Publish' na karein.
4. ⏰ **Automated Payment Reminders**: Due date se 45, 30, 15, 7, 3, aur 2 din pehle auto reminders dispatch hote hain.
5. 💳 **Online Payment Gateways**: Stripe, PayPal, aur direct bank transfer ke zariye online payments collect karein.
6. 🛡️ **QR Code Verification**: Har published invoice par fraud-proof cryptographic QR seal hota hai.
7. 🎫 **24h Turnaround Support Desk**: SLA backed customer support assistance.

Aap abhi kya karna chahte hain? Naya invoice banayein ya koi specific setting dekhni hai?",

                'ar' => "منصة Invoicy هي النظام المالي والفوترة السحابي الشامل لإدارة أعمالك! إليك أبرز ما يمكنك تنفيذه عبر المنصة:

1. ⚡ **إصدار فواتير احترافية**: للعملاء المسجلين أو عبر وضع البيع الفوري (Walk-in) دون تسجيل العميل.
2. 📦 **أكثر من 45 قالباً للخدمات**: لـ 7 قطاعات رئيسية (صيانة الحواسيب، النقل، الليموزين، المقاولات، التوزيع، التسويق، والصناعة).
3. 🔒 **حماية المسودات السرية**: المسودات سرية بالكامل ولا ترسل للعميل حتى تعتمدها عبر 'نشر الفاتورة'.
4. ⏰ **جدولة تذكيرات السداد الآلية**: تنبيهات دورية ذكية قبل مواعيد الاستحقاق.
5. 💳 **بوابات دفع متعددة**: قبول الدفع عبر Stripe وPayPal والتحويل البنكي.
6. 🛡️ **ختم التحقق الرقمي QR**: لمنع تزوير الفواتير والمطابقة المالية.
7. 🎫 **مكتب الدعم الفني خلال 24 ساعة**: متابعة دورية لكل استفساراتك.

كيف ترغب بالبدء اليوم؟ هل ننشئ فاتورة جديدة أو نستعرض بيانات حسابك؟",

                'hi' => "Invoicy एक अत्याधुनिक क्लाउड बिलिंग और वित्तीय प्रबंधन प्लेटफ़ॉर्म है! आप इसके साथ निम्नलिखित कार्य कर सकते हैं:

1. ⚡ **प्रोफेशनल इनवॉइस बनाएं**: पंजीकृत ग्राहकों के लिए या वॉक-इन मोड से तत्काल कैश बिलिंग।
2. 📦 **45+ उद्योग प्रीसेट**: कंप्यूटर शॉप, ट्रांसपोर्ट, निर्माण, और एजेंसी आदि के लिए 1-क्लिक दरें।
3. 🔒 **सुरक्षित ड्राफ्ट**: पब्लिश करने से पहले ड्राफ्ट पूर्णतः निजी रहते हैं।
4. ⏰ **स्वचालित भुगतान रिमाइंडर**: देय तिथि से पहले स्वचालित सूचनाएं।
5. 💳 **ऑनलाइन भुगतान गेटवे**: Stripe, PayPal और बैंक ट्रांसफर से सीधे भुगतान लें।
6. 🛡️ **QR कोड सत्यापन**: जालसाजी-रोधी डिजिटल मुहर।
7. 🎫 **24 घंटे सपोर्ट डेस्क**: तत्काल सहायता टिकट।

आप क्या करना चाहेंगे?",

                'tr' => "Invoicy, işletmeler için yeni nesil bulut faturalama ve finans platformudur! Bu yazılımla şunları yapabilirsiniz:

1. ⚡ **Profesyonel Fatura Oluşturma**: Kayıtlı müşteriler veya Hızlı Satış (Walk-in) kasası ile anında tahsilat.
2. 📦 **45+ Sektörel Hazır Şablon**: Bilgisayar servisi, taşımacılık, inşaat, dağıtım ve ajanslar için tek tıkla kalem ekleme.
3. 🔒 **Gizli Taslak Koruması**: Taslaklar siz 'Yayınla' diyene kadar müşteriye gönderilmez ve gizli kalır.
4. ⏰ **Otomatik Ödeme Hatırlatıcıları**: Vade tarihinden önce akıllı e-posta hatırlatmaları.
5. 💳 **Online Tahsilat Entegrasyonu**: Stripe, PayPal ve banka havalesi desteği.
6. 🛡️ **QR Kod ile Doğrulama**: Sahteciliği önleyen kriptografik karekod mührü.
7. 🎫 **24 Saat SLA Destek Masası**: Hızlı teknik çözüm biletleri.

Nasıl yardımcı olabilirim?",

                default => "Invoicy is a next-generation SaaS financial and billing suite! Here is what you can accomplish:

1. ⚡ **Create Professional Invoices**: Full customer invoices or Walk-in instant cash retail billing.
2. 📦 **45+ 1-Click Industry Presets**: Pre-priced line items across 7 sectors (Computer Shop, Transportation, Limousine Taxi, Construction, Industrial, Distributor, and Marketing).
3. 🔒 **Private Draft Safeguards**: Drafts are completely private—no emails dispatched and PDFs/QR codes are locked until published.
4. ⏰ **Automated Milestone Reminders**: Automatic schedules at 45, 30, 15, 7, 3, and 2 days before due dates.
5. 💳 **Online Payment Gateways**: Collect credit card and bank payments via Stripe, PayPal, and bank transfers.
6. 🛡️ **ISO QR Code Verification**: Tamper-proof cryptographic seals on every issued invoice.
7. 🎫 **24h SLA Support Desk**: High-priority ticketing assistance for your team.

How may I assist you right now? Would you like to create an invoice or explore your workspace data?",
            };

            return [
                'reply' => $reply,
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'میرے فنانشل اعدادوشمار', 'سپورٹ ڈیسک کھولیں'],
                    'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', 'Mera Balance & Stats', 'Support Desk Kholein'],
                    'ar' => ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'إحصائيات حسابي', 'فتح مكتب الدعم'],
                    'tr' => ['Yeni Fatura Oluştur', 'Taslaklar Nasıl Çalışır?', 'Hesap Özeti & Rapor', 'Destek Masası Aç'],
                    default => ['Create new invoice', 'How do drafts work?', 'My Workspace Stats', 'Open Support Desk'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 1: Live Workspace Stats & Revenue Balance Queries
        // -------------------------------------------------------------
        if ($workspace && preg_match('/(kitna balance|kitne paise|kitni invoices|meri kamai|stats|revenue|summary|balance|outstanding|unpaid|kitna baki|kitna paisa|کتنے پیسے|کتنا بیلنس|میرے انوائسز|کتنی رقم|میرا ریوینیو|كم رصيدي|كم عدد الفواتير|المبالغ المعلقة|إيراداتي|bakiyem|toplam fatura|gelir|cuánto debo|total facturas)/u', $q)) {
            $stats = $this->getWorkspaceStats($workspace);
            $curr = $workspace->currency ?? 'USD';

            $reply = match ($lang) {
                'ur' => "📊 **آپ کے ورک اسپیس کے تازہ ترین مالیاتی اعدادوشمار:**

• کل جاری شدہ انوائسز: **{$stats['total_invoices']}**
• وصول شدہ رقم: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} ادا شدہ)
• بقایا رقم (Unpaid): **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} واجب الادا)
• نجی ڈرافٹ انوائسز: **{$stats['draft_count']}**
• رجسٹرڈ کلائنٹس: **{$stats['client_count']}**

کیا آپ فواتیر کی فہرست دیکھنا چاہتے ہیں یا نیا انوائس جاری کرنا چاہتے ہیں؟",

                'ur-roman' => "📊 **Aap ke Workspace ke live financial stats ye hain:**

• Total Invoices: **{$stats['total_invoices']}**
• Collected Revenue: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} Paid)
• Outstanding Due: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} Unpaid)
• Private Drafts: **{$stats['draft_count']}**
• Saved Clients: **{$stats['client_count']}**

Aap invoices dashboard dekhna chahte hain ya naya invoice banana chahte hain?",

                'ar' => "📊 **ملخص الأرقام المالية لمساحة عملك حالياً:**

• إجمالي الفواتير: **{$stats['total_invoices']}**
• الإيرادات المحصلة: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} مدفوعة)
• المبالغ المعلقة والمستحقة: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} غير مدفوعة)
• فواتير المسودة: **{$stats['draft_count']}**
• العملاء المسجلون: **{$stats['client_count']}**

هل ترغب في الانتقال إلى لوحة الفواتير الآن؟",

                default => "📊 **Live Financial Overview for your Workspace:**

• Total Invoices: **{$stats['total_invoices']}**
• Collected Revenue: **{$curr} {$stats['paid_revenue']}** ({$stats['paid_count']} Paid)
• Outstanding Balance: **{$curr} {$stats['outstanding_revenue']}** ({$stats['unpaid_count']} Unpaid)
• Private Drafts: **{$stats['draft_count']}**
• Saved Clients: **{$stats['client_count']}**

Would you like to review your invoices dashboard or create a new invoice?",
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
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
        // INTENT 2: Create Invoice / Walk-in Mode / "Naya Invoice"
        // -------------------------------------------------------------
        if (preg_match('/(create invoice|new invoice|make invoice|issue invoice|walk-in|walkin|counter sale|cash sale|naya invoice|invoice banana|bill banana|kaise banaye|invoice kaise|kaise banayein|naya bill|نیا انوائس|انوائس بنائیں|بل بنائیں|انوائس بنانا|انشاء فاتورة|فاتورة جديدة|fatura oluştur|yeni fatura|crear factura|nueva factura|créer facture|nouvelle facture|rechnung erstellen|नया इनवॉइस|बिल बनाएं)/u', $q)) {
            $reply = match ($lang) {
                'ur' => 'آپ کے لیے فوری **انوائس بلڈر** کھولا جا رہا ہے۔ آپ اپنے رجسٹرڈ کلائنٹس کو منتخب کر سکتے ہیں یا فوری کیش سیل کے لیے **واک اِن (Walk-in)** موڈ منتخب کر کے بغیر لاگ ان 1 کلک میں بل تیار کر سکتے ہیں۔',
                'ur-roman' => 'Aap ke liye naya **Invoice Builder** open kiya ja raha hai. Aap registered client select kar sakte hain ya instant counter sale ke liye **Walk-in mode** use kar sakte hain.',
                'ar' => 'جاري نقلك إلى **منشئ الفواتير**. يمكنك اختيار عميل مسجل أو استخدام وضع **البيع الفوري (Walk-in)** لإصدار فاتورة نقدية سريعة بنقرة واحدة.',
                'tr' => 'Sizin için **Fatura Oluşturucu** açılıyor. Kayıtlı müşterinizi seçebilir veya Hızlı Satış (Walk-in) modunu kullanabilirsiniz.',
                'hi' => 'नया इनवॉइस बनाने के लिए **इनवॉइस बिल्डर** खोला जा रहा है। आप ग्राहक चुन सकते हैं या वॉक-इन मोड का उपयोग कर सकते हैं।',
                default => 'Opening the **Invoice Builder** for you. You can select existing registered clients or use Walk-in mode for instant cash and retail billing without client signup.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['1-کلک انڈسٹری سروسز', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'میرے تمام انوائسز'],
                    'ur-roman' => ['1-Click Industry Presets', 'Drafts kaise kaam karte hain?', 'Invoices Dashboard'],
                    'ar' => ['قوالب الخدمات الجاهزة', 'كيف تعمل المسودات؟', 'لوحة الفواتير'],
                    default => ['Industry Presets', 'How do drafts work?', 'Invoices Dashboard'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 3: Draft Safeguard vs Published Invoices
        // -------------------------------------------------------------
        if (preg_match('/(draft|drafts|why draft|publish|issue|published|ڈرافٹ|ڈرافٹس|مسودہ|مسودة|taslak|taslak fatura|borrador|brouillon|entwurf)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '🔒 **ڈرافٹ انوائسز کی رازداری اور سیکیورٹی:**

انوائسی میں ڈرافٹ انوائسز مکمل طور پر نجی رہتی ہیں۔ جب تک آپ "Issue & Publish" کا بٹن نہ دبائیں:
1. کلائنٹ کو کوئی ای میل یا نوٹیفکیشن نہیں بھیجا جاتا۔
2. پی ڈی ایف ڈاؤن لوڈ اور آن لائن پیمنٹ پورٹل غیر فعال رہتا ہے۔
3. کیو آر تصدیقی کوڈ غیر فعال رہتا ہے۔
اس طرح آپ بغیر کسی غلطی کے خطرے کے آرام سے بلنگ مکمل کر سکتے ہیں۔',

                'ur-roman' => '🔒 **Draft Invoices ki Security & Safeguards:**

Invoicy me Draft invoices 100% private hoti hain. Jab tak aap "Issue & Publish" par click na karein:
1. Customer ko koi email ya notification nahi jata.
2. PDF downloads aur online payment portal links disabled rehte hain.
3. ISO QR verification code inactive rehta hai.
Aap bila jhijhak items verify kar ke publish kar sakte hain.',

                'ar' => '🔒 **حماية وسرية فواتير المسودة (Draft):**

فواتير المسودة سرية للغاية ومؤمنة في Invoicy. حتى تقوم بالضغط على "نشر الفاتورة":
1. لن يتم إرسال أي بريد إلكتروني للعميل.
2. روابط تحميل PDF وبوابة الدفع معطلة تماماً.
3. رمز التحقق الذكي QR معطل لضمان عدم تسريب البيانات قبل الاعتماد.',

                default => '🔒 **Draft Invoice Safeguards & Confidentiality:**

Draft invoices in Invoicy are completely private. Until you click "Issue & Publish":
1. No emails or payment notices are dispatched to your customer.
2. PDF downloads and customer payment portal links are securely disabled.
3. ISO QR verification codes remain inactive to prevent accidental sharing.',
            };

            return [
                'reply' => $reply,
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['نیا انوائس بنائیں', 'انوائس کیسے ریکال کریں؟', 'کیو آر کوڈ کی وضاحت'],
                    'ur-roman' => ['Naya Invoice Banayein', 'Invoice recall kaise karein?', 'QR Verification'],
                    'ar' => ['إنشاء فاتورة جديدة', 'كيف أسحب الفاتورة؟', 'شرح رمز QR'],
                    default => ['Create new invoice', 'How to recall an invoice?', 'Explain QR code'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 4: Industry Presets & Catalog
        // -------------------------------------------------------------
        if (preg_match('/(preset|presets|industry|catalog|computer shop|transportation|limo|limousine|industrial|construction|distributor|marketing|پری سیٹ|کیٹلاگ|خدمات|önayar|hizmetler|servicios|catálogo|services prédéfinis)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '📦 انوائسی میں 7 بڑی صنعتوں کے لیے **45 سے زائد فوری پری سیٹس** شامل ہیں:
• کمپیوٹر شاپ (ہارڈ ویئر مرمت، ایس ایس ڈی اپ گریڈ)
• ٹرانسپورٹ اور لاجسٹکس (روٹ فریٹ، پیلٹ)
• لیموزین اور ایئرپورٹ ٹرانسفر
• کنسٹرکشن اور انڈسٹریل مینوفیکچرنگ
• ہول سیل ڈسٹری بیوٹرز اور ڈیجیٹل مارکیٹنگ ایجنسیاں۔
ایک کلک پر ریٹ اور پیمائشی یونٹ خودکار شامل ہو جاتے ہیں۔',

                'ur-roman' => '📦 Invoicy me 7 major industries ke liye **45+ 1-click presets** available hain: Computer Shop, Logistics & Transport, Limousine Taxi, Construction, Industrial Manufacturing, Wholesale Distribution, aur Marketing Agencies. Ek click par unit rates add ho jate hain.',

                'ar' => '📦 توفر منصة Invoicy أكثر من **45 قالباً ذكياً لسبع قطاعات تجارية** تشمل صيانة الحواسيب، النقل واللوجستيات، الليموزين، المقاولات والإنشاءات، التصنيع، والتسويق الإلكتروني مع تسعير تلقائي.',

                default => '📦 Invoicy provides **45+ 1-click presets across 7 major industries**: Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, and Marketing Agency with auto-synced rates and measurement units.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['انوائس بلڈر کھولیں', 'ڈرافٹ پروٹیکشن', 'پیمنٹ گیٹ وے'],
                    default => ['Open Invoice Builder', 'Draft Safeguards', 'Payment Gateways'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 5: Recall Published Invoices
        // -------------------------------------------------------------
        if (preg_match('/(recall|withdraw|revert|edit published|mistake in invoice|ریکال|واپس|تبدیل|سحب|استرجاع|geri çek|düzelt|retirer)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '🔄 **انوائس ریکال فیچر:** اگر آپ نے کوئی انوائس شائع (Publish) کر دی ہو اور بعد میں ترمیم کی ضرورت پیش آئے، تو آپ انوائس ویو اسکرین پر جا کر "Recall to Draft" کے بٹن پر کلک کر سکتے ہیں۔ اس سے پبلک پورٹل فوری لاک ہو جاتا ہے اور آپ باحفاظت ترامیم کر کے دوبارہ جاری کر سکتے ہیں۔',
                'ur-roman' => '🔄 **Invoice Recall Feature:** Agar aap ne invoice publish kar di hai aur correction karni hai, toh aap invoice view page par "Recall to Draft" click kar sakte hain. Public access lock ho jayegi aur aap changes save kar ke dobara publish kar sakte hain.',
                'ar' => '🔄 **ميزة استرجاع الفاتورة (Recall):** إذا قمت بنشر فاتورة ورغبت في تعديلها، يمكنك الضغط على زر "Recall to Draft" من صفحة الفاتورة لقفل الرابط الخارجي فوراً وإعادتها كمسودة لتعديلها بأمان.',
                default => '🔄 **Invoice Recall Feature:** If you published an invoice with mistakes, open the invoice detail screen and click "Recall to Draft". This locks the public portal and revokes client access so you can edit and republish safely.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['انوائسز دیکھیں', 'نیا انوائس بنائیں', 'سپورٹ ڈیسک'],
                    default => ['View Invoices', 'Create New Invoice', 'Support Desk'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 6: 24h SLA Support Desk
        // -------------------------------------------------------------
        if (preg_match('/(support|help desk|ticket|problem|issue|sla|سپورٹ|ٹکٹ|مدد|مسئلہ|شکایت|تذكرة|دعم|destek|bilet|ayuda|soporte)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '🎫 **24 گھنٹے کا سپورٹ ڈیسک:** ہماری سینئر سپورٹ اور انجینئرنگ ٹیم 24 گھنٹے کے اندر ہر ٹکٹ کا مصدقہ حل فراہم کرتی ہے۔ آپ نیا ٹکٹ درج کر سکتے ہیں یا اپنے موجودہ ٹکٹس کی پیش رفت دیکھ سکتے ہیں۔',
                'ur-roman' => '🎫 **24h Turnaround Support Desk:** Hamari technical support team har ticket ka 24 hours ke andar guaranteed SLA resolution deti hai. Aap naya ticket create kar sakte hain ya existing status track kar sakte hain.',
                'ar' => '🎫 **مكتب الدعم الفني وضمان الاستجابة خلال 24 ساعة:** فريقنا المتخصص ملتزم بمعالجة وحل جميع التذاكر الفنية في أقل من 24 ساعة.',
                default => '🎫 **24h SLA Support Desk:** Our dedicated support and engineering pods are committed to resolving your billing and technical inquiries within 24 hours.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('support.index')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['نیا سپورٹ ٹکٹ کھولیں', 'تمام ٹکٹس دیکھیں', 'ہیلپ سینٹر'],
                    default => ['Open New Ticket', 'View Active Tickets', 'Help Center'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 7: Payment Gateways (Stripe, PayPal, Bank)
        // -------------------------------------------------------------
        if (preg_match('/(payment|stripe|paypal|bank|gateway|online payment|ادا|پیمنٹ|بینک|دفع|سداد|bypassing|öde|ödeme)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '💳 **آن لائن پیمنٹ گیٹ ویز:** آپ انوائسی میں سٹرائپ (کریڈٹ کارڈز، ایپل پے)، پے پال، اور لوکل بینک ٹرانسفر کو باآسانی ایکٹیویٹ کر سکتے ہیں تاکہ کلائنٹ ایک کلک میں انوائس ادا کر سکے۔',
                'ur-roman' => '💳 **Payment Gateways:** Aap Stripe (Credit cards, Apple Pay), PayPal aur Bank Transfer configure kar sakte hain taake clients invoice portal se direct online payment kar sakein.',
                'ar' => '💳 **بوابات الدفع الإلكتروني:** يمكنك ربط حساب Stripe وPayPal وحسابات التحويل البنكي لتحصيل قيمة الفواتير إلكترونياً وبطريقة آمنة.',
                default => '💳 **Payment Gateway Integrations:** You can configure Stripe (Credit cards, Apple Pay), PayPal, and offline bank transfer wire instructions in your workspace settings.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('settings.payment')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['پیمنٹ سیٹنگز کھولیں', 'نیا انوائس بنائیں', 'ڈیش بورڈ'],
                    default => ['Payment Settings', 'Create Invoice', 'Dashboard'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 8: Client CRM Management
        // -------------------------------------------------------------
        if (preg_match('/(client|customer|crm|add client|گاہک|کلائنٹ|عميل|عملاء|müşteri|kullanıcı)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '👥 **کلائنٹ سی آر ایم (CRM):** آپ اپنے تمام کاروباری کلائنٹس، ان کے رابطے، ای میلز، ٹیکس نمبر اور ادائیگی کی تاریخ کو ایک جگہ منظم کر سکتے ہیں۔',
                'ur-roman' => '👥 **Client CRM:** Aap apne tamam customers, contact details, tax numbers aur unki invoice payment history ko CRM me manage kar sakte hain.',
                'ar' => '👥 **إدارة العملاء CRM:** يمكنك حفظ بيانات عملائك وتفاصيل الاتصال والأرقام الضريبية وسجل فواتير كل عميل بسهولة.',
                default => '👥 **Client CRM Management:** You can manage customer profiles, contact directories, tax registration numbers, and historic payment ledgers.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('clients.index')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => match ($lang) {
                    'ur' => ['تمام کلائنٹس دیکھیں', 'نیا کلائنٹ شامل کریں', 'نیا انوائس بنائیں'],
                    default => ['View Clients', 'Add New Client', 'Create Invoice'],
                }
            ];
        }

        // -------------------------------------------------------------
        // INTENT 9: Settings & Branding
        // -------------------------------------------------------------
        if (preg_match('/(setting|settings|logo|prefix|currency|tax|vat|سیٹنگز|لوگو|کرنسی|ٹیکس|إعدادات|شعار|عملة|ayarlar|logo)/u', $q)) {
            $reply = match ($lang) {
                'ur' => '⚙️ آپ کے ورک اسپیس کی تمام جنرل سیٹنگز، کمپنی لوگو، ڈیفالٹ کرنسی، ٹیکس ریٹس اور انوائس پری فکس کا پیج کھولا جا رہا ہے۔',
                'ur-roman' => '⚙️ Workspace settings page open kiya ja raha hai jahan aap apna brand logo, currency, tax rates aur invoice sequence format set kar sakte hain.',
                'ar' => '⚙️ جاري فتح إعدادات مساحة العمل لضبط شعار الشركة، العملة الافتراضية، نسب الضرائب، وتخصيص الفواتير.',
                default => '⚙️ Navigating to your Workspace Settings where you can configure brand logos, currency, VAT/Tax rates, and invoice prefixes.',
            };

            return [
                'reply' => $reply,
                'action' => ['type' => 'navigate', 'url' => route('settings.index')],
                'lang' => $voiceLang,
                'lang_code' => $lang,
                'is_rtl' => $isRtl,
                'suggestions' => ['Payment Gateways', 'Invoice Templates', 'Support Desk']
            ];
        }

        // -------------------------------------------------------------
        // DEFAULT FALLBACK: Intelligent Contextual Response
        // -------------------------------------------------------------
        $reply = match ($lang) {
            'ur' => "میں آپ کی بات سمجھ رہا ہوں۔ میں انوائس بنانے، نجی ڈرافٹس، 45+ انڈسٹری سروس پری سیٹس، کیو آر کوڈ کی تصدیق، آن لائن ادائیگی کے گیٹ ویز، یا 24 گھنٹے کے سپورٹ ڈیسک میں آپ کی رہنمائی کر سکتا ہوں۔ آپ کس فیچر کے بارے میں جاننا چاہتے ہیں؟",
            'ur-roman' => "Main aap ki baat samajh raha hoon. Main naya invoice banane, draft safeguards, 1-click industry presets, live balance & revenue stats, payment gateways ya 24h SLA support desk me aap ki madad kar sakta hoon. Aap kis bare me janna chahte hain?",
            'ar' => "أنا هنا لمساعدتك! يمكنني إرشادك حول إنشاء الفواتير، قوالب الخدمات الذكية، حماية المسودات، بوابات الدفع، أو فتح تذكرة دعم فني. كيف يمكنني خدمتك؟",
            'tr' => "Size yardımcı olmaktan mutluluk duyarım! Yeni fatura oluşturma, 45+ sektörel hazır şablon, taslak koruması, ödeme yöntemleri veya Destek Masası hakkında soru sorabilirsiniz.",
            'hi' => "मैं आपकी सहायता के लिए तैयार हूँ! आप इनवॉइस निर्माण, 45+ सेवा प्रीसेट, ड्राफ्ट सुरक्षा, भुगतान गेटवे, या सपोर्ट टिकट के बारे में पूछ सकते हैं।",
            default => "I understand your request. I can help you create invoices, use 45+ 1-click industry presets, explain draft safeguards, check live workspace revenue, set up payment gateways, or submit a 24h SLA support ticket. How may I assist you?",
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
     * Get live workspace statistics.
     */
    public function getWorkspaceStats(Workspace $workspace): array
    {
        $totalInvoices = Invoice::where('workspace_id', $workspace->id)->count();
        $paidInvoices = Invoice::where('workspace_id', $workspace->id)->where('status', 'paid')->get();
        $unpaidInvoices = Invoice::where('workspace_id', $workspace->id)->whereIn('status', ['unpaid', 'overdue', 'partially_paid'])->get();
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
            return "Workspace: None (Super Admin mode)";
        }

        $stats = $this->getWorkspaceStats($workspace);
        $curr = $workspace->currency ?? 'USD';

        return "Active Workspace: '{$workspace->name}', Currency: {$curr}, Total Invoices: {$stats['total_invoices']}, Collected Revenue: {$curr} {$stats['paid_revenue']}, Outstanding Due: {$curr} {$stats['outstanding_revenue']}, Drafts: {$stats['draft_count']}, Saved Clients: {$stats['client_count']}.";
    }

    /**
     * Build system prompt for external LLM.
     */
    protected function buildSystemPrompt(string $langCode, string $wsContext): string
    {
        $customPrompt = CmsContent::getVal('ai_system_prompt', '');
        
        $base = "You are the AI Agent for 'Invoicy by RedMaple', a high-precision multi-tenant SaaS billing and financial platform.
You are knowledgeable, conversational, helpful, and concise.

Key Platform Knowledge:
- Fast Invoicing: Full client invoices or Walk-in instant cash counter billing without signup.
- 45+ 1-Click Industry Presets across 7 sectors (Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, Marketing Agency).
- Draft Safeguard: Draft invoices are 100% private. No emails are sent, and PDF downloads, payment links, and QR codes are disabled until published.
- Automated Payment Reminders: Triggered at 45, 30, 15, 7, 3, and 2 days before due date.
- Recall Option: Published invoices can be recalled to draft for safe corrections.
- ISO QR Verification: Tamper-proof cryptographic 2D QR seal on all published invoices.
- 24h SLA Support Desk: Dedicated ticketing support.
- Payment Gateways: Stripe, PayPal, offline bank transfers.

CRITICAL MULTILINGUAL RULES:
- Detect the user's language automatically and reply strictly in the user's language.
- When user writes in Urdu script, respond ONLY in native Urdu (Nastaliq/Arabic script). NEVER respond in Arabic.
- When user writes in Roman Urdu/Hinglish (e.g. 'iske bare mein batao', 'naya invoice kaise banayein'), respond naturally in Roman Urdu/Hinglish.
- When user writes in Arabic, respond in fluent professional Arabic.
- When user writes in English, Turkish, Spanish, French, German, Hindi, respond in that respective language.
- Keep responses friendly, structured with bullet points where appropriate, and offer clear next steps.

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
            'ur' => ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'فوری سروس پری سیٹس', 'سپورٹ ڈیسک کھولیں'],
            'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', '1-Click Presets', 'Support Desk Kholein'],
            'ar' => ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'قوالب الخدمات الجاهزة', 'فتح مكتب الدعم'],
            'tr' => ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'],
            'hi' => ['नया इनवॉیس बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'त्वरित प्रीसेट', 'सपोर्ट डेस्क खोलें'],
            'es' => ['Crear nueva factura', '¿Cómo funcionan los borradores?', 'Plantillas rápidas', 'Abrir soporte'],
            'fr' => ['Créer une facture', 'Fonctionnement des brouillons', 'Modèles rapides', 'Ouvrir le support'],
            'de' => ['Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Schnellvorlagen', 'Support öffnen'],
            default => ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk'],
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
            'hi' => 'नमस्ते! मैं आपका Invoicy AI सहायक हूँ। इनवॉیس, ड्राफ्ट, सेटिंग्स या सपोर्ट के बारे में पूछें या माइक से बात करें!',
            'es' => '¡Hola! Soy tu Agente IA de Invoicy. Pregúntame sobre facturas, borradores, soporte o habla usando el micrófono.',
            'fr' => 'Bonjour ! Je suis votre Agent IA Invoicy. Posez-moi des questions sur vos factures, brouillons ou parlez au micro.',
            'de' => 'Hallo! Ich bin Ihr Invoicy KI-Agent. Fragen Sie mich nach Rechnungen, Entwürfen oder sprechen Sie mit dem Mikrofon.',
            default => 'Hello! I am your AI Agent. Ask me anything about your invoices, settings, draft safeguards, support tickets, or click the mic to speak a voice command!',
        };
    }

    /**
     * Record assistant reply in session history.
     */
    protected function recordAssistantReply(string $reply): void
    {
        $history = session()->get('ai_copilot_history', []);
        $history[] = ['role' => 'assistant', 'content' => $reply, 'time' => now()->toIso8601String()];
        if (count($history) > 12) {
            $history = array_slice($history, -12);
        }
        session()->put('ai_copilot_history', $history);
    }

    /**
     * Clear conversation history.
     */
    public function clearHistory(): void
    {
        session()->forget('ai_copilot_history');
    }
}
