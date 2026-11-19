<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    /**
     * Process AI Agent text and voice inquiries with native multi-language intelligence (Request 8 & 11).
     */
    public function chat(Request $request)
    {
        $requestedLang = $request->input('lang');
        $query = trim($request->input('message', ''));
        $q = mb_strtolower($query, 'UTF-8');
        
        // 1. Determine active language (Content detection takes precedence over default 'en')
        $detected = $this->detectLanguage($q);
        if ($detected !== 'en') {
            $langCode = $detected;
        } elseif (!empty($requestedLang) && in_array($requestedLang, ['ur', 'ur-roman', 'ar', 'hi', 'tr', 'es', 'fr', 'de'])) {
            $langCode = $requestedLang;
        } else {
            $langCode = 'en';
            if (session('locale') && in_array(session('locale'), ['ur', 'ar', 'hi', 'tr', 'es', 'fr', 'de'])) {
                if (!preg_match('/\b(open|create|draft|publish|ticket|setting|settings|help|how|what|why|guide|tell|show|recall|verify|qr|payment|pay|customer|portal|email|smtp|promo|coupon|preset|presets|service|services|hello|hi|tour|walkin|walk-in)\b/i', $query)) {
                    $langCode = session('locale');
                }
            }
        }

        $voiceLang = $this->getSpeechLangTag($langCode);

        if (empty($query)) {
            return response()->json([
                'reply' => match($langCode) {
                    'ur', 'ur-roman' => 'Aap koi sawaal poochhein ya mic par click kar ke bolein!',
                    'ar' => 'يرجى طرح سؤال أو إعطاء أمر صوتي عبر الميكروفون!',
                    'tr' => 'Lütfen bir soru sorun veya mikrofon ile sesli komut verin!',
                    'hi' => 'कृपया कोई प्रश्न पूछें या माइक पर क्लिक करके बोलें!',
                    'es' => '¡Por favor haga una pregunta o use el micrófono!',
                    'fr' => 'Veuillez poser une question ou donner une commande vocale !',
                    'de' => 'Bitte stellen Sie eine Frage oder geben Sie einen Sprachbefehl !',
                    default => 'Please ask a question or give a voice command!',
                },
                'lang' => $voiceLang,
                'suggestions' => match($langCode) {
                    'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', '1-Click Presets', 'Support Desk Kholein'],
                    'ur' => ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'فوری سروسز', 'سپورٹ ڈیسک کھولیں'],
                    'tr' => ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'],
                    'ar' => ['إنشاء فاتورة جديدة', 'كيف تعمل المسودات؟', 'نماذج جاهزة', 'فتح مكتب الدعم'],
                    'es' => ['Crear nueva factura', '¿Cómo funcionan los borradores?', 'Plantillas rápidas', 'Abrir soporte'],
                    'fr' => ['Créer une facture', 'Fonctionnement des brouillons', 'Modèles rapides', 'Ouvrir le support'],
                    'de' => ['Neue Rechnung erstellen', 'Wie funktionieren Entwürfe?', 'Schnellvorlagen', 'Support öffnen'],
                    'hi' => ['नया इनवॉइस बनाएं', 'ड्राफ्ट कैसे काम करते हैं?', 'त्वरित प्रीसेट', 'सपोर्ट डेस्क खोलें'],
                    default => ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk'],
                }
            ]);
        }

        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        // Process query
        return response()->json($this->generateLocalizedResponse($q, $langCode, $isSuperAdmin));
    }

    /**
     * Detect input language with robust Roman Urdu, Arabic, Hindi, Turkish, and European language detection.
     */
    protected function detectLanguage(string $q): string
    {
        // 1. Arabic & Urdu native script
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $q)) {
            if (preg_match('/(انوائس|کیسے|کریں|شکریہ|سلام|مدد|نئی|بنائیں|سیٹنگز|چلیں|دکھائیں|مسئلہ|ٹکٹ|ڈرافٹ)/u', $q)) {
                return 'ur';
            }
            return 'ar';
        }

        // 2. Hindi Devanagari script
        if (preg_match('/[\x{0900}-\x{097F}]/u', $q)) {
            return 'hi';
        }

        // 3. Roman Urdu / Hinglish (e.g. "iske bare mein batao", "kaise kare", "batao", "madad", "ye kya hai")
        if (preg_match('/\b(iske|uske|is|iss|bare|baare|mein|me|batao|bataiye|bataen|kya|hai|hain|kaise|kese|kare|karen|karo|banaye|banayein|banao|madad|shukriya|mujhe|chahiye|kahan|theek|acha|accha|nahi|haan|bhi|aur|ye|yeh|wo|woh|apna|apni|karna|hoga|hota|chalega|bolo|bhejo|dekho|dikhao|hisaab|paisa|paise|kharcha|kamai)\b/i', $q)) {
            return 'ur-roman';
        }

        // 4. Turkish keywords (distinct Turkish words or characters)
        if (preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $q) || preg_match('/\b(merhaba|fatura|faturayı|ayarlar|yardım|nasıl|oluştur|müşteri|hatırlatıcı|destek|bilet)\b/i', $q)) {
            return 'tr';
        }

        // 5. Spanish keywords
        if (preg_match('/[áéíóúñ¿¡]/u', $q) || preg_match('/\b(hola|factura|configuración|ayuda|cómo|crear|cliente|recordatorio|soporte|gracias)\b/i', $q)) {
            return 'es';
        }

        // 6. French keywords
        if (preg_match('/[àâçéèêëîïôûùüÿœæ]/u', $q) || preg_match('/\b(bonjour|facture|paramètres|aide|comment|créer|client|rappel|merci)\b/i', $q)) {
            return 'fr';
        }

        // 7. German keywords
        if (preg_match('/[äöüß]/u', $q) || preg_match('/\b(hallo|rechnung|einstellungen|hilfe|wie|erstellen|kunde|mahnung|danke)\b/i', $q)) {
            return 'de';
        }

        // 8. English Priority for standard commands
        if (preg_match('/\b(open|support|desk|create|invoice|invoices|new|draft|publish|ticket|tickets|setting|settings|help|how|what|why|guide|tell|show|recall|verify|qr|payment|pay|customer|portal|email|smtp|promo|coupon|preset|presets|service|services|hello|hi|tour|walkin|walk-in)\b/i', $q)) {
            return 'en';
        }

        // Default to English
        return 'en';
    }

    /**
     * Map language code to speech synthesis BCP-47 voice tag.
     */
    protected function getSpeechLangTag(string $lang): string
    {
        return match ($lang) {
            'ur', 'ur-roman' => 'ur-PK',
            'ar' => 'ar-SA',
            'hi' => 'hi-IN',
            'tr' => 'tr-TR',
            'es' => 'es-ES',
            'fr' => 'fr-FR',
            'de' => 'de-DE',
            default => 'en-US',
        };
    }

    /**
     * Generate localized response, actions, and smart suggestions.
     */
    protected function generateLocalizedResponse(string $q, string $lang, bool $isSuperAdmin): array
    {
        $voiceLang = $this->getSpeechLangTag($lang);

        // 0. Overview / "Iske bare mein batao" / General Explanation
        if (preg_match('/(iske bare mein|ye kya hai|yeh kya hai|is ke bare|about|overview|kya karta hai|batao|explain invoicy|invoicy kya hai)/i', $q)) {
            return [
                'reply' => match($lang) {
                    'ur-roman' => "Ye Invoicy SaaS billing platform hai! Yahan aap professional invoices create kar sakte hain, 1-click industry presets add kar sakte hain, drafts ko verify kar ke publish kar sakte hain, automated payment reminders bhej sakte hain, aur Stripe, PayPal ya bank transfer se online payments le sakte hain. Aap kya karna chahte hain?",
                    'ur' => "یہ انوائسی ساس بلنگ پلیٹ فارم ہے! یہاں آپ پروفیشنل انوائسز بنا سکتے ہیں، 1-کلک انڈسٹری سروسز استعمال کر سکتے ہیں، ڈرافٹ انوائسز کو محفوظ رکھ سکتے ہیں اور آن لائن ادائیگیاں وصول کر سکتے ہیں۔ آپ کیا کرنا چاہتے ہیں؟",
                    'ar' => "هذه منصة Invoicy لإدارة الفواتير والمدفوعات المتكاملة! يمكنك إصدار فواتير احترافية، استخدام نماذج الخدمات الجاهزة، وحماية المسودات وقبول الدفع عبر الإنترنت.",
                    'tr' => "Bu Invoicy SaaS fatura platformudur! Profesyonel faturalar oluşturabilir, 1-tıkla sektör şablonları ekleyebilir, taslakları güvenle yönetebilir ve ödemeleri tahsil edebilirsiniz.",
                    default => "Invoicy is a next-generation SaaS billing suite. You can create invoices, use 1-click industry service presets, protect drafts before publishing, schedule automated payment reminders, and accept online card or bank payments."
                },
                'lang' => $voiceLang,
                'suggestions' => match($lang) {
                    'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', '1-Click Presets', 'Support Desk Kholein'],
                    'ur' => ['نیا انوائس بنائیں', 'ڈرافٹس کیسے کام کرتے ہیں؟', 'فوری سروسز', 'سپورٹ ڈیسک کھولیں'],
                    'tr' => ['Yeni fatura oluştur', 'Taslaklar nasıl çalışır?', 'Hızlı Şablonlar', 'Destek Masası Aç'],
                    default => ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk']
                }
            ];
        }

        // 1. Create Invoice & Walk-in Mode
        if (preg_match('/(create invoice|new invoice|make invoice|issue invoice|walk-in|walkin|counter sale|cash sale|naya invoice|invoice banana|bill banana|kaise banaye|invoice kaise|kaise banayein|naya bill|نیا انوائس|انوائس بنائیں|انشاء فاتورة|فاتورة جديدة|fatura oluştur|yeni fatura|crear factura|nueva factura|créer facture|nouvelle facture|rechnung erstellen|neue rechnung|नया इनवॉइस|बिल बनाएं)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur-roman' => 'Aap ke liye naya Invoice Builder open kiya ja raha hai. Aap walk-in customer ya registered client select kar ke foran bill bana sakte hain.',
                    'ur' => 'آپ کے لیے نیا انوائس بلڈر کھولا جا رہا ہے۔ آپ اپنے رجسٹرڈ کلائنٹس منتخب کر سکتے ہیں یا واک ان موڈ کے ذریعے بغیر لاگ ان فوری کیش بل بنا سکتے ہیں۔',
                    'ar' => 'جاري فتح منشئ الفواتير لإصدار فاتورة جديدة. يمكنك اختيار عميل مسجل أو استخدام وضع العميل الفوري Walk-in.',
                    'tr' => 'Yeni bir fatura oluşturmanız için fatura oluşturucu açılıyor. Kayıtlı müşteri seçebilir veya Hızlı Satış modunu kullanabilirsiniz.',
                    'hi' => 'नया इनवॉइस बनाने के लिए इनवॉइस बिल्डर खोला जा रहा है। आप ग्राहक चुन सकते हैं या वॉक-इन मोड का उपयोग कर सकते हैं।',
                    'es' => 'Abriendo el creador de facturas. Puede seleccionar un cliente existente o usar el modo Walk-in para venta rápida.',
                    'fr' => 'Ouverture du générateur de factures. Sélectionnez un client ou utilisez le mode vente directe sans inscription.',
                    'de' => 'Der Rechnungs-Builder wird geöffnet. Sie können einen Kunden wählen oder den Walk-in-Modus für Sofortverkäufe nutzen.',
                    default => 'Opening Invoice Builder. You can select registered clients or use Walk-in mode for instant cash and retail billing without client signup.',
                },
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'lang' => $voiceLang,
                'suggestions' => match($lang) {
                    'ur-roman' => ['1-Click Industry Presets', 'Drafts kaise kaam karte hain?', 'Invoices Dashboard'],
                    'ur' => ['انڈسٹری سروس پری سیٹس', 'ڈرافٹ انوائس کیسے کام کرتا ہے؟', 'میرے انوائسز'],
                    default => ['Industry Service Presets', 'How do drafts work?', 'View Invoices Dashboard']
                }
            ];
        }

        // 2. Industry Presets & Service Catalog (Item 1 & 8)
        if (preg_match('/(preset|presets|industry|catalog|computer shop|transportation|limo|limousine|industrial|construction|distributor|marketing|پری سیٹ|کیٹلاگ|خدمات|önayar|hizmetler|servicios|catálogo|services prédéfinis)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'انوائسی میں 7 بڑی صنعتوں کے لیے 45 سے زائد پری سیٹس شامل ہیں: کمپیوٹر شاپ، ٹرانسپورٹ، لیموزین، انڈسٹریل، کنسٹرکشن، ڈسٹری بیوٹر، اور مارکیٹنگ ایجنسی۔ ایک کلک پر ریٹ اور پیمائشی یونٹ خودکار سیٹ ہو جاتے ہیں۔',
                    'ar' => 'توفر منصة Invoicy أكثر من 45 قالباً لسبع قطاعات رئيسية (صيانة الحواسيب، النقل، الليموزين، الصناعة، المقاولات، التوزيع، والتسويق الرقمي) مع تسعير ووحدات قياس ذكية.',
                    'tr' => 'Invoicy, 7 sektör için 45+ hazır hizmet şablonu sunar: Bilgisayar Mağazası, Taşımacılık, Limuzin & Taksi, Endüstriyel, İnşaat, Dağıtım ve Pazarlama Ajansı.',
                    'hi' => 'Invoicy में 7 प्रमुख उद्योगों (कंप्यूटर शॉप, ट्रांसपोर्ट, लिमोसिन, निर्माण, डिस्ट्रीब्यूटर, एजेंसी) के लिए 45+ सेवा प्रीसेट उपलब्ध हैं।',
                    default => 'Invoicy includes 45+ 1-click presets for 7 major industries: Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, and Marketing Agency with auto-synced rate units.',
                },
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'lang' => $voiceLang,
                'suggestions' => ['Create new invoice', 'How do drafts work?', 'Payment Gateway setup']
            ];
        }

        // 3. Draft Safeguard vs Published Invoices
        if (preg_match('/(draft|drafts|why draft|publish|issue|published|ڈرافٹ|مسودہ|مسودة|taslak|taslak fatura|borrador|brouillon|entwurf)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'ڈرافٹ انوائسز مکمل طور پر نجی اور محفوظ ہوتی ہیں۔ گاہکوں کو کوئی ای میل نہیں بھیجی جاتی، اور پی ڈی ایف، پیمنٹ پورٹل، اور کیو آر تصدیقی اسکرین مکمل طور پر غیر فعال رہتی ہیں جب تک آپ "Issue & Publish" نہ کریں۔',
                    'ar' => 'فواتير المسودة (Draft) محمية بالكامل وسرية. لا يتم إرسال أي بريد للعملاء، كما أن روابط PDF وبوابة الدفع ورمز التحقق معطلة تماماً حتى تقوم بالضغط على نشر الفاتورة.',
                    'tr' => 'Taslak faturalar tamamen gizlidir. Müşteriye e-posta gönderilmez; PDF indirme, ödeme portalı ve QR doğrulama ekranı fatura yayınlanana kadar kilitlidir.',
                    'hi' => 'ड्राफ्ट इनवॉइस पूरी तरह से सुरक्षित हैं। कोई ईमेल नहीं भेजा जाता है और PDF, पेमेंट पोर्टल व QR सत्यापन केवल पब्लिश करने के बाद सक्रिय होते हैं।',
                    default => 'Draft invoices are completely private and safeguarded. No emails are sent, and PDF downloads, payment portals, and QR verification screens are disabled until you click "Issue & Publish".',
                },
                'lang' => $voiceLang,
                'suggestions' => ['Create new invoice', 'How to recall an invoice?', 'Explain QR code verification']
            ];
        }

        // 4. PDF Generation & Password Protection
        if (preg_match('/(pdf|download pdf|print|password|پی ڈی ایف|ڈاؤن لوڈ|تحميل pdf|طباعة|indir|imprimer|drucken)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'جاری کردہ انوائسز سے آپ فوری طور پر GAAP معیار کا آفیشل پی ڈی ایف ڈاؤن لوڈ کر سکتے ہیں۔ آپ کلائنٹ کی سیکیورٹی کے لیے پاس ورڈ پروٹیکشن بھی فعال کر سکتے ہیں۔',
                    'ar' => 'يمكنك تنزيل مستند PDF رسمي متوافق مع معايير GAAP للفواتير المنشورة، مع إمكانية تشفير الملف بكلمة مرور لحماية البيانات المالية.',
                    'tr' => 'Yayınlanan faturalardan GAAP standartlarında resmi PDF indirebilir ve müşteri güvenliği için parola koruması uygulayabilirsiniz.',
                    default => 'Published invoices generate official, GAAP-compliant PDFs. You can also enforce password protection on client PDFs for confidential financial security.',
                },
                'lang' => $voiceLang,
                'suggestions' => ['View Invoices Dashboard', 'How to recall an invoice?', 'Explain QR code verification']
            ];
        }

        // 5. Recall / Pull Back Sent Invoices
        if (preg_match('/(recall|pull back|revoke|cancel invoice|واپس لیں|استرجاع|fatura geri çek|anular factura|annuler facture|rechnung zurückrufen)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'اگر کسی انوائس میں کوئی غلطی ہو جائے، تو آپ "Recall / Pull Back" کا بٹن دبا کر فوری طور پر گاہک کے پورٹل کو لاک اور غیر فعال کر سکتے ہیں تاکہ کوئی غیر مجاز ادائیگی نہ ہو۔',
                    'ar' => 'ميزة Recall / Pull Back تتيح لك سحب الفاتورة الصادرة على الفور وإغلاق بوابة الدفع لمنع أي سداد بالخطأ.',
                    'tr' => 'Recall / Pull Back özelliği sayesinde gönderilmiş bir faturayı anında geri çekebilir ve ödeme portalını kilitleyerek hatalı ödemeleri önleyebilirsiniz.',
                    default => 'The Recall / Pull Back feature allows you to immediately revoke public access to a sent invoice, locking the customer payment portal to prevent payments while you make revisions.',
                },
                'lang' => $voiceLang,
                'suggestions' => ['View Invoices Dashboard', 'How do drafts work?', 'Create new invoice']
            ];
        }

        // 6. Automated Milestone Reminders
        if (preg_match('/(reminder|reminders|milestone|schedule|45 days|ریماScript|یاد دہانی|تذكير|hatırlatıcı|recordatorio|rappel|mahnung)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'انوائسی کا خودکار سسٹم مقررہ تاریخ سے 45، 30، 15، 7، 3، اور 2 دن پہلے کلائنٹس کو یاد دہانی ای میل بھیجتا ہے، اور دکاندار کو بھی مطلع کرتا ہے۔',
                    'ar' => 'نظام التذكير الآلي يرسل تنبيهات سداد عند 6 محطات زمنية (45، 30، 15، 7، 3، ويومان قبل تاريخ الاستحقاق) مع إشعارات لصاحب الوكالة.',
                    'tr' => 'Otomatik hatırlatma sistemi vadesi gelmeden 45, 30, 15, 7, 3 ve 2 gün önce müşteriye nazik ödeme hatırlatmaları gönderir.',
                    default => 'Invoicy features automated milestone payment reminders dispatched 45, 30, 15, 7, 3, and 2 days before due date, along with vendor notification alerts.',
                },
                'action' => ['type' => 'navigate', 'url' => route('reminders.index')],
                'lang' => $voiceLang,
                'suggestions' => ['View Reminders', 'Create new invoice', 'Open Support Desk']
            ];
        }

        // 7. QR Verification & Authenticity Seals
        if (preg_match('/(qr|verify|authentic|seal|scan|کیو آر|تصدیق|التحقق|ختم|doğrulama|karekod|verificación|sello|vérification|authentification|verifizierung|सत्यापन)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'ہر جاری کردہ انوائس میں بین الاقوامی ISO معیار کا 2D کیو آر کوڈ اور کرپٹوگرافک فنگر پرنٹ مہر شامل ہوتی ہے۔ اسکین کرنے پر صارف براہ راست تصدیقی اسکرین پر جا کر مستند حالت دیکھ سکتا ہے۔',
                    'ar' => 'تحتوي كل فاتورة رسمية على رمز QR ثنائي الأبعاد وبصمة تشفير رقمية تثبت أصالة المستند القانونية وحالة السداد دون الحاجة لتسجيل الدخول.',
                    'tr' => 'Yayınlanan her faturada, belgenin doğruluğunu ve ödeme durumunu anında onaylayan ISO standartlarında bir QR kod ve kriptografik mühür bulunur.',
                    'hi' => 'प्रत्येक इनवॉइस में एक डिजिटल QR कोड और क्रिप्टोग्राफिक सील होती है जिससे प्रामाणिकता और भुगतान की पुष्टि होती है।',
                    default => 'Every published invoice includes an authentic 2D QR code and cryptographic fingerprint seal proving document authenticity and payment status on the public verification portal.',
                },
                'lang' => $voiceLang,
                'suggestions' => ['Create new invoice', 'How do drafts work?', 'View Invoices Dashboard']
            ];
        }

        // 8. Payment Gateways (Stripe, PayPal, Bank Wire)
        if (preg_match('/(payment|stripe|paypal|bank|gateway|settlement|credit card|پیمنٹ|ادائیگی|الدفع|ödeme|pago|paiement|zahlung|भुगतान)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'انوائسی اسٹرائپ، پے پال اور براہ راست بینک ٹرانسفر کو سپورٹ کرتی ہے۔ اگر کسی اکاؤنٹ میں گیٹ وے کنفیگر نہ ہو، تو پورٹل پر پیمنٹ بٹن خودکار طور پر چھپ جاتے ہیں اور براہ راست بینک ہدایات دکھائی جاتی ہیں۔',
                    'ar' => 'تدعم المنصة الدفع عبر Stripe و PayPal والتحويل البنكي. إذا لم تكن البوابات مهيأة، تُخفى أزرار الدفع تلقائياً وتظهر بيانات التحويل البنكي المباشر.',
                    'tr' => 'Invoicy, Stripe, PayPal ve Banka Havalesini destekler. Ödeme ağ geçitleri yapılandırılmamışsa, müşteriye sadece banka havalesi bilgileri gösterilir.',
                    default => 'Invoicy supports Stripe, PayPal, and Direct Bank Wire transfers. If gateways are unconfigured, online payment buttons are automatically hidden and direct vendor settlement instructions are shown.',
                },
                'action' => ['type' => 'navigate', 'url' => route('settings.index') . '#gateways'],
                'lang' => $voiceLang,
                'suggestions' => ['Configure Settings', 'Create new invoice', 'Open Support Desk']
            ];
        }

        // 9. Promo Codes & Expiration Durations
        if (preg_match('/(promo|coupon|discount|voucher|expiration|duration|پروما|کوپن|تخفيض|خصم|kupon|promosyon|descuento|rabatt|कूपन)/u', $q)) {
            $destUrl = $isSuperAdmin ? route('admin.coupons.index') : route('membership.index');
            return [
                'reply' => match($lang) {
                    'ur' => 'پرومو کوڈز میں معیاد ختم ہونے کا مقررہ وقت (1 گھنٹہ، 24 گھنٹے، 7 دن وغیرہ) منتخب کیا جا سکتا ہے۔ معیاد ختم ہونے پر سسٹم خودکار طور پر ڈسکاؤنٹ بند کر دیتا ہے۔',
                    'ar' => 'يمكنك إنشاء وتطبيق كوبونات خصم مع تحديد مدة الصلاحية (ساعة واحدة، 24 ساعة، 7 أيام، إلخ). ينتهي الخصم تلقائياً عند انقضاء الوقت المحدد.',
                    'tr' => 'Promosyon kodları belirli bir süreye (1 saat, 24 saat, 7 gün vb.) ayarlanabilir ve belirlenen sürede otomatik olarak sona erer.',
                    default => 'Promo codes feature decided expiration durations (1h, 12h, 24h, 7d, 30d, custom date). When expired, the coupon is rejected automatically.',
                },
                'action' => ['type' => 'navigate', 'url' => $destUrl],
                'lang' => $voiceLang,
                'suggestions' => $isSuperAdmin ? ['Manage Coupons', 'Dashboard', 'Support Desk'] : ['View Plans', 'Create invoice', 'Support Desk']
            ];
        }

        // 10. Consumer Support Desk & 24h SLA Guarantee (Item 5)
        if (preg_match('/(support|ticket|tickets|help desk|sla|turnaround|مدد ڈیسک|سپورٹ|ٹکٹ|الدعم الفني|تذكرة|destek|bilet|soporte|ticket de soporte|assistance|dépannage|kundendienst|support-ticket|सपोर्ट|टिकट)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'کنزیومر سپورٹ ڈیسک کھولا جا رہا ہے۔ ہماری 24 گھنٹے کی ٹرن اراؤنڈ SLA گارنٹی ہر ٹکٹ پر لاگو ہے۔',
                    'ar' => 'جاري فتح مكتب دعم الوكالات. كل تذكرة مدعومة بضمان الاستجابة والحل خلال 24 ساعة.',
                    'tr' => 'Tüketici Destek Masası açılıyor. Her talep 24 saatlik SLA dönüş garantimiz kapsamındadır.',
                    'hi' => 'सपोर्ट डेस्क खोला जा रहा है। हमारी 24 घंटे की टर्नअराउंड SLA गारंटी सक्रिय है।',
                    'es' => 'Abriendo el servicio de soporte. Cada ticket está respaldado por nuestra garantía de SLA de 24 horas.',
                    'fr' => 'Ouverture du support client. Chaque ticket bénéficie de notre garantie SLA de 24 heures.',
                    'de' => 'Der Support-Desk wird geöffnet. Jedes Ticket unterliegt unserer 24-Stunden-SLA-Garantie.',
                    default => 'Opening Agency Consumer Support Desk. Every ticket is backed by our 24-hour turnaround SLA guarantee.',
                },
                'action' => ['type' => 'navigate', 'url' => route('support.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Create new ticket', 'SLA Guarantee policy', 'Back to Dashboard']
            ];
        }

        // 11. Agency Settings, Custom SMTP & Canadian Branding
        if (preg_match('/(setting|settings|smtp|email config|mail server|brand|logo|tax|سیٹنگز|ایس ایم ٹی پی|الإعدادات|البريد|ayarlar|e-posta ayarları|configuración|ajustes|paramètres|configuration|einstellungen|mailserver|सेटिंग्स)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'ایجنسی سیٹنگز کھولی جا رہی ہیں۔ یہاں آپ اپنا کسٹم آؤٹ گوئنگ SMTP اور برانڈنگ لوگو ترتیب دے سکتے ہیں۔',
                    'ar' => 'جاري فتح إعدادات الوكالة. يمكنك ضبط خادم SMTP البريدي وشعار العلامة التجارية هنا.',
                    'tr' => 'Ajans Ayarları açılıyor. Buradan giden SMTP e-posta sunucunuzu ve marka detaylarınızı yapılandırabilirsiniz.',
                    'hi' => 'एजेंसी सेटिंग्स खोली जा रही हैं। यहां आप अपना कस्टम आउटगोइंग SMTP और ब्रांडिंग सेट कर सकते हैं।',
                    default => 'Opening Agency Settings. Here you can configure your custom Outgoing SMTP, upload company branding, and adjust tax rates.',
                },
                'action' => ['type' => 'navigate', 'url' => route('settings.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Test SMTP', 'Upload logo', 'Tax settings']
            ];
        }

        // 12. Super Admin Control Panel (/cp & /admin)
        if (preg_match('/(admin|superadmin|control panel|cp|metrics|revenue|کنٹرول پینل|لوحة التحكم|yönetim|kontrol paneli|panel de control)/u', $q)) {
            $destUrl = $isSuperAdmin ? route('admin.dashboard') : route('cp.login');
            return [
                'reply' => match($lang) {
                    'ur' => 'پلیٹ فارم سپر ایڈمن کنٹرول پینل پر منتقل کیا جا رہا ہے۔ یہ سیکشن 2FA سیکیورٹی سے محفوظ ہے۔',
                    'ar' => 'جاري نقلك إلى لوحة تحكم الإدارة العليا Control Panel المحمية برمز التحقق الثنائي.',
                    'tr' => 'Süper Yönetici Kontrol Paneline yönlendiriliyorsunuz. Bu alan iki faktörlü kimlik doğrulamasıyla korunmaktadır.',
                    default => 'Directing you to the Platform Super Admin Control Panel, guarded by dedicated two-factor authentication and platform audit logging.',
                },
                'action' => ['type' => 'navigate', 'url' => $destUrl],
                'lang' => $voiceLang,
                'suggestions' => ['System Health', 'Revenue Analytics', 'Support Desk']
            ];
        }

        // 13. Help, Documentation & Guided Tour (Item 2)
        if (preg_match('/(help|guide|manual|docs|tutorial|tour|رہنمائی|مدد|دلیل|تعليمات|kılavuz|rehber|guía|manuel|documentation|anleitung|handbuch|सहायता|मार्गदर्शिका)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'جامع انوائسی ہیلپ اور نالج سینٹر کھولا جا رہا ہے۔ آپ ٹاپ بار میں "Guide & Tour" کے بٹن سے خوش آمدیدی گائیڈ بھی دیکھ سکتے ہیں۔',
                    'ar' => 'جاري فتح مركز المساعدة والتوثيق لمنصة Invoicy. يمكنك أيضاً إعادة مشاهدة الجولة التوجيهية في أي وقت.',
                    'tr' => 'Kapsamlı Invoicy Yardım ve Bilgi Merkezi açılıyor. Ayrıca üst çubuktan Rehber & Tur özelliğini başlatabilirsiniz.',
                    default => 'Opening the Invoicy Help & Knowledge Center. You can also replay the interactive Getting Started Walkthrough anytime from the top bar.',
                },
                'action' => ['type' => 'navigate', 'url' => route('help.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Create new invoice', 'How do drafts work?', 'Support Desk']
            ];
        }

        // 14. View Invoices Dashboard
        if (preg_match('/(go to invoice|view invoice|show invoice|list invoice|invoices|انوائسز|میرے انوائس|الفواتير|عرض الفواتير|faturalar|faturalara git|ver facturas|facturas|voir les factures|factures|rechnungen anzeigen|rechnungsliste|इनवॉइस सूची)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'آپ کے تمام انوائسز کا ڈیش بورڈ کھولا جا رہا ہے۔',
                    'ar' => 'جاري نقلك إلى لوحة الفواتير المسجلة.',
                    'tr' => 'Faturalarınızın listelendiği kontrol paneline yönlendiriliyorsunuz.',
                    default => 'Taking you to your invoices dashboard.',
                },
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Create new invoice', 'How to recall an invoice?', 'How do drafts work?']
            ];
        }

        // Smart Default Contextual Fallback
        return [
            'reply' => match($lang) {
                'ur-roman' => "Main aap ki baat samajh raha hoon. Main naya invoice banane, draft protection, 1-click industry presets, QR verification, ya 24h SLA Support Ticket me aap ki madad kar sakta hoon. Aap kis bare me janna chahte hain?",
                'ur' => "میں آپ کی بات سمجھ رہا ہوں۔ میں انوائس بنانے، ڈرافٹ پروٹیکشن، 1-کلک انڈسٹری پری سیٹس، کیو آر ویریفکیشن، اور 24 گھنٹے کے سپورٹ ڈیسک میں آپ کی مدد کر سکتا ہوں۔ آپ کس بارے میں جاننا چاہتے ہیں؟",
                'ar' => "أنا هنا لمساعدتك! يمكنني إرشادك حول إنشاء الفواتير، قوالب الخدمات الذكية، حماية المسودات، أو فتح تذكرة دعم فني. كيف يمكنني خدمتك؟",
                'tr' => "'{$q}' hakkında yardımcı olabilirim. Yeni fatura oluşturma, 1-tıkla sektör şablonları, taslak koruması veya Destek Masası hakkında bana sorabilirsiniz.",
                'hi' => "मैं आपकी सहायता के लिए तैयार हूं। आप इनवॉइस निर्माण, सेवा प्रीसेट, ड्राफ्ट सुरक्षा, या सहायता टिकट के बारे में पूछ सकते हैं।",
                'es' => "Entendido. Puedo ayudarle a crear facturas, usar plantillas por industria, explicar borradores seguros o abrir un ticket de soporte. ¿En qué puedo asistirle?",
                'fr' => "Je suis à votre écoute. Je peux vous guider pour créer des factures, utiliser les modèles industriels ou ouvrir un ticket de support.",
                'de' => "Ich bin bereit. Ich kann Sie bei der Rechnungserstellung, Vorlagen, Entwürfen oder beim Support-Desk unterstützen. Wie kann ich helfen?",
                default => "I understand you are asking about '{$q}'. I can help you create invoices, use 1-click industry service presets, explain draft safeguards, configure payment gateways, or open a 24h SLA Support Ticket. How may I assist you?",
            },
            'lang' => $voiceLang,
            'suggestions' => match($lang) {
                'ur-roman' => ['Naya Invoice Banayein', 'Drafts kaise kaam karte hain?', '1-Click Presets', 'Support Desk Kholein'],
                'ur' => ['نیا انوائس بنائیں', 'ڈرافٹ انوائس کیسے کام کرتا ہے؟', 'سپورٹ ڈیسک کھولیں'],
                default => ['Create new invoice', 'How do drafts work?', '1-Click Presets', 'Open Support Desk']
            }
        ];
    }
}
