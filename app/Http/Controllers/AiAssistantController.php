<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    /**
     * Process AI Agent text and voice inquiries with native multi-language intelligence (Request 11).
     */
    public function chat(Request $request)
    {
        $query = trim($request->input('message', ''));
        if (empty($query)) {
            return response()->json([
                'reply' => 'Please ask a question or give a voice command!',
                'lang' => 'en-US',
                'suggestions' => ['Create new invoice', 'Go to settings', 'Open Help Center']
            ]);
        }

        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $q = mb_strtolower($query, 'UTF-8');

        // Detect language
        $langCode = $this->detectLanguage($q);

        // Process query
        return response()->json($this->generateLocalizedResponse($q, $langCode, $isSuperAdmin));
    }

    /**
     * Detect input language from script or keywords.
     */
    protected function detectLanguage(string $q): string
    {
        // 1. Arabic & Urdu script
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $q)) {
            // Check for characteristic Urdu words
            if (preg_match('/(انوائس|کیسے|کریں|شکریہ|سلام|مدد|نئی|بنائیں|سیٹنگز|چلیں|دکھائیں)/u', $q)) {
                return 'ur';
            }
            return 'ar';
        }

        // 2. Hindi Devanagari script
        if (preg_match('/[\x{0900}-\x{097F}]/u', $q)) {
            return 'hi';
        }

        // 3. Turkish keywords
        if (preg_match('/(merhaba|fatura|faturayı|ayarlar|yardım|nasıl|oluştur|müşteri|hatırlatıcı|destek)/i', $q)) {
            return 'tr';
        }

        // 4. Spanish keywords
        if (preg_match('/(hola|factura|configuración|ayuda|cómo|crear|cliente|recordatorio|soporte|gracias)/i', $q)) {
            return 'es';
        }

        // 5. French keywords
        if (preg_match('/(bonjour|facture|paramètres|aide|comment|créer|client|rappel|support|merci)/i', $q)) {
            return 'fr';
        }

        // 6. German keywords
        if (preg_match('/(hallo|rechnung|einstellungen|hilfe|wie|erstellen|kunde|mahnung|support|danke)/i', $q)) {
            return 'de';
        }

        // Default to active session locale or English
        $appLocale = app()->getLocale();
        return in_array($appLocale, ['ur', 'ar', 'hi', 'tr', 'es', 'fr', 'de']) ? $appLocale : 'en';
    }

    /**
     * Map language code to speech synthesis BCP-47 voice tag.
     */
    protected function getSpeechLangTag(string $lang): string
    {
        return match ($lang) {
            'ur' => 'ur-PK',
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

        // 1. Create Invoice Action
        if (preg_match('/(create invoice|new invoice|make invoice|issue invoice|نیا انوائس|انوائس بنائیں|انشاء فاتورة|فاتورة جديدة|fatura oluştur|yeni fatura|crear factura|nueva factura|créer facture|nouvelle facture|rechnung erstellen|neue rechnung|नया इनवॉइस|बिल बनाएं)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'آپ کے لیے نیا انوائس بلڈر کھولا جا رہا ہے تاکہ آپ جلدی سے انوائس بنا سکیں۔',
                    'ar' => 'جاري فتح منشئ الفواتير لإصدار فاتورة تجارية جديدة الآن.',
                    'tr' => 'Yeni bir fatura oluşturup yayınlamanız için fatura oluşturucu açılıyor.',
                    'hi' => 'नया इनवॉइस बनाने के लिए इनवॉइस बिल्डर खोला जा रहा है।',
                    'es' => 'Abriendo el creador de facturas para emitir y publicar una nueva factura.',
                    'fr' => 'Ouverture du générateur de factures pour créer et publier une nouvelle facture.',
                    'de' => 'Der Rechnungsgenerator wird geöffnet, um eine neue Rechnung zu erstellen.',
                    default => 'Opening the invoice builder for you to create and publish a new invoice.',
                },
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'lang' => $voiceLang,
                'suggestions' => match($lang) {
                    'ur' => ['لائن آئٹمز کیسے شامل کریں؟', 'ریویو اسکرین کیسے کام کرتی ہے؟'],
                    'ar' => ['كيف أضيف بنود الفاتورة؟', 'شرح التحقق بالرمز'],
                    'tr' => ['Kalemleri nasıl eklerim?', 'Önizleme ekranı nasıl çalışır?'],
                    'hi' => ['आइटम कैसे जोड़ें?', 'सत्यापन कैसे काम करता है?'],
                    'es' => ['¿Cómo agrego elementos?', '¿Cómo funciona la revisión?'],
                    'fr' => ['Comment ajouter des lignes ?', 'Explication de la vérification QR'],
                    'de' => ['Wie füge ich Positionen hinzu?', 'Rechnungsrückruf erklären'],
                    default => ['How do I add line items?', 'How does the review screen work?']
                }
            ];
        }

        // 2. View Invoices Dashboard
        if (preg_match('/(go to invoice|view invoice|show invoice|list invoice|invoices|انوائسز|میرے انوائس|الفواتير|عرض الفواتير|faturalar|faturalara git|ver facturas|facturas|voir les factures|factures|rechnungen anzeigen|rechnungsliste|इनवॉइस सूची)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'آپ کے تمام انوائسز کا ڈیش بورڈ کھولا جا رہا ہے۔',
                    'ar' => 'جاري نقلك إلى لوحة الفواتير المسجلة.',
                    'tr' => 'Faturalarınızın listelendiği kontrol paneline yönlendiriliyorsunuz.',
                    'hi' => 'आपके सभी इनवॉइस के डैशबोर्ड पर ले जाया जा रहा है।',
                    'es' => 'Llevándole al panel de control de sus facturas.',
                    'fr' => 'Affichage de votre tableau de bord des factures.',
                    'de' => 'Weiterleitung zu Ihrer Rechnungsübersicht.',
                    default => 'Taking you to your invoices dashboard.',
                },
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
                'lang' => $voiceLang,
                'suggestions' => match($lang) {
                    'ur' => ['انوائس واپس کیسے لیں؟', 'پی ڈی ایف ڈاؤن لوڈ کریں'],
                    'ar' => ['كيف أسترجع فاتورة؟', 'تحميل ملف PDF'],
                    'tr' => ['Fatura nasıl geri çekilir?', 'PDF nasıl indirilir?'],
                    'hi' => ['इनवॉइस वापस कैसे लें?', 'PDF डाउनलोड करें'],
                    'es' => ['¿Cómo revocar una factura?', 'Descargar PDF'],
                    'fr' => ['Comment annuler une facture ?', 'Télécharger le PDF'],
                    'de' => ['Wie rufe ich eine Rechnung zurück?', 'PDF herunterladen'],
                    default => ['How to recall an invoice?', 'How do I download a PDF?']
                }
            ];
        }

        // 3. Settings / SMTP / Email Config
        if (preg_match('/(setting|smtp|email config|mail server|سیٹنگز|ایس ایم ٹی پی|الإعدادات|البريد|ayarlar|e-posta ayarları|configuración|ajustes|paramètres|configuration|einstellungen|mailserver|सेटिंग्स)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'ایجنسی سیٹنگز کھولی جا رہی ہیں۔ یہاں آپ اپنا کسٹم آؤٹ گوئنگ SMTP اور کینیڈین برانڈنگ ترتیب دے سکتے ہیں۔',
                    'ar' => 'جاري فتح إعدادات الوكالة. يمكنك ضبط خادم SMTP البريدي والهوية التجارية الكندية هنا.',
                    'tr' => 'Ajans Ayarları açılıyor. Buradan giden SMTP e-posta sunucunuzu ve marka detaylarınızı yapılandırabilirsiniz.',
                    'hi' => 'एजेंसी सेटिंग्स खोली जा रही हैं। यहां आप अपना कस्टम आउटगोइंग SMTP और ब्रांडिंग सेट कर सकते हैं।',
                    'es' => 'Abriendo la configuración de la agencia para configurar SMTP saliente y marca canadiense.',
                    'fr' => 'Ouverture des paramètres de l\'agence pour configurer votre serveur SMTP et votre image de marque.',
                    'de' => 'Die Agentur-Einstellungen werden geöffnet, um ausgehendes SMTP und Branding einzurichten.',
                    default => 'Opening Agency Settings. Here you can configure your custom Outgoing SMTP and Canadian branding.',
                },
                'action' => ['type' => 'navigate', 'url' => route('settings.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Test SMTP', 'Upload logo', 'Tax settings']
            ];
        }

        // 4. Support Desk (Request 12)
        if (preg_match('/(support|ticket|help desk|sla|مدد ڈیسک|سپورٹ|ٹکٹ|الدعم الفني|تذكرة|destek|bilet|soporte|ticket de soporte|assistance|dépannage|kundendienst|support-ticket|सपोर्ट|टिकट)/u', $q)) {
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
                'suggestions' => ['Create new ticket', 'SLA Guarantee policy']
            ];
        }

        // 5. Help / Documentation
        if (preg_match('/(help|guide|manual|docs|رہنمائی|مدد|دلیل|تعليمات|kılavuz|rehber|guía|manuel|documentation|anleitung|handbuch|सहायता|मार्गदर्शिका)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'جامع انوائسی ہیلپ اور نالج سینٹر کھولا جا رہا ہے۔',
                    'ar' => 'جاري فتح مركز المساعدة والتوثيق الشامل لمنصة Invoicy.',
                    'tr' => 'Kapsamlı Invoicy Yardım ve Bilgi Merkezi açılıyor.',
                    'hi' => 'विस्तृत इनवॉइंसी सहायता केंद्र खोला जा रहा है।',
                    'es' => 'Abriendo el Centro de Ayuda y Documentación de Invoicy.',
                    'fr' => 'Ouverture du Centre d\'Aide et de Documentation Invoicy.',
                    'de' => 'Das umfassende Invoicy Hilfe- und Wissenszentrum wird geöffnet.',
                    default => 'Opening the comprehensive Invoicy Help & Knowledge Center.',
                },
                'action' => ['type' => 'navigate', 'url' => route('help.index')],
                'lang' => $voiceLang,
                'suggestions' => ['Invoice recall guide', 'Explain QR verification']
            ];
        }

        // 6. Greetings & Identity
        if (preg_match('/(who are you|hello|hi|hey|آپ کون ہیں|سلام|السلام عليكم|مرحبا|أهلا|günaydın|selam|hola|buenos días|bonjour|salut|hallo|guten tag|नमस्ते|हेलो)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'سلام! میں آپ کا AI ایجنٹ ہوں۔ میں انوائس بنانے، سیٹنگز درست کرنے، سوالات کے جوابات دینے اور بلنگ میں آپ کی مدد کر سکتا ہوں۔ آپ مائک دبا کر آواز سے بھی بات کر سکتے ہیں!',
                    'ar' => 'أهلاً بك! أنا وكيلك الذكي (AI Agent). يمكنني مساعدتك في إصدار الفواتير، استرجاع الأخطاء، وإدارة الحسابات. يمكنك التحدث صوتياً أو كتابياً.',
                    'tr' => 'Merhaba! Ben sizin AI Ajanınızım. Fatura oluşturma, ayarları yönetme, QR doğrulama ve sorularınızda size yardımcı olabilirim. Mikrofonla sesli komut da verebilirsiniz!',
                    'hi' => 'नमस्ते! मैं आपका AI एजेंट हूं। मैं इनवॉइस बनाने, सेटिंग्स प्रबंधित करने और आपके सवालों का जवाब देने में मदद कर सकता हूं।',
                    'es' => '¡Hola! Soy su Agente de IA. Puedo ayudarle a crear facturas, gestionar clientes, configurar SMTP y responder a sus preguntas.',
                    'fr' => 'Bonjour ! Je suis votre Agent IA. Je peux vous aider à créer des factures, gérer vos paramètres, et répondre à vos questions en direct.',
                    'de' => 'Hallo! Ich bin Ihr KI-Agent. Ich unterstütze Sie bei der Rechnungserstellung, Konfiguration und Beantwortung aller Fragen.',
                    default => 'Hello! I am your AI Agent. I can help you create invoices, navigate the system, manage clients, recall invoices, configure custom SMTP, and answer questions hands-free!',
                },
                'lang' => $voiceLang,
                'suggestions' => match($lang) {
                    'ur' => ['نیا انوائس بنائیں', 'سیٹنگز کھولیں', 'انوائس کیسے واپس لیں؟'],
                    'ar' => ['إنشاء فاتورة جديدة', 'فتح الإعدادات', 'شرح ختم التحقق'],
                    'tr' => ['Yeni fatura oluştur', 'Ayarlara git', 'Fatura nasıl geri çekilir?'],
                    'hi' => ['नया इनवॉइस बनाएं', 'सेटिंग्स खोलें', 'सत्यापन कैसे काम करता है?'],
                    'es' => ['Crear nueva factura', 'Ir a configuración', 'Explicar verificación QR'],
                    'fr' => ['Créer une facture', 'Ouvrir les paramètres', 'Explication QR Code'],
                    'de' => ['Neue Rechnung erstellen', 'Zu Einstellungen', 'QR-Prüfung erklären'],
                    default => ['Create new invoice', 'How do I recall an invoice?', 'Explain QR code verification', 'Go to settings']
                }
            ];
        }

        // 7. QR Verification & Cryptographic Seal
        if (preg_match('/(qr|verify|authentic|seal|scan|کیو آر|تصدیق|التحقق|ختم|doğrulama|karekod|verificación|sello|vérification|authentification|verifizierung|सत्यापन)/u', $q)) {
            return [
                'reply' => match($lang) {
                    'ur' => 'ہر جاری کردہ انوائس میں بین الاقوامی ISO معیار کا 2D کیو آر کوڈ اور کرپٹوگرافک فنگر پرنٹ مہر شامل ہوتی ہے۔ اسکین کرنے پر صارف براہ راست تصدیقی اسکرین پر جا کر مستند حالت دیکھ سکتا ہے۔',
                    'ar' => 'تحتوي كل فاتورة رسمية على رمز QR ثنائي الأبعاد وبصمة تشفير رقمية تثبت أصالة المستند القانونية وحالة السداد.',
                    'tr' => 'Yayınlanan her faturada, belgenin doğruluğunu ve ödeme durumunu anında onaylayan ISO standartlarında bir QR kod ve kriptografik mühür bulunur.',
                    'hi' => 'प्रत्येक इनवॉइस में एक डिजिटल QR कोड और क्रिप्टोग्राफिक सील होती है जिससे प्रामाणिकता और भुगतान की पुष्टि होती है।',
                    'es' => 'Cada factura publicada incluye un código QR 2D auténtico y un sello criptográfico que verifica la autenticidad y el estado de pago.',
                    'fr' => 'Chaque facture publiée intègre un QR code 2D et un sceau cryptographique officiel prouvant l\'authenticité du document.',
                    'de' => 'Jede Rechnung verfügt über einen kryptografischen QR-Code und ein Siegel zur Echtheits- und Zahlungsprüfung.',
                    default => 'Every published invoice includes an authentic 2D QR code and cryptographic fingerprint seal proving document authenticity and payment status.',
                },
                'lang' => $voiceLang,
                'suggestions' => ['Open Verification Screen', 'Create new invoice']
            ];
        }

        // Default response
        return [
            'reply' => match($lang) {
                'ur' => "میں آپ کی بات سمجھ رہا ہوں۔ آپ مجھے 'نیا انوائس بنائیں'، 'سیٹنگز کھولیں'، یا 'ہیلپ سینٹر' جیسے صوتی یا تحریری احکامات دے سکتے ہیں۔ میں کس طرح مدد کروں؟",
                'ar' => "أنا هنا لمساعدتك! يمكنك إعطائي أوامر صوتية أو نصية مثل 'إنشاء فاتورة جديدة'، 'الذهاب إلى الإعدادات'، أو 'مكتب الدعم'. كيف يمكنني خدمتك؟",
                'tr' => "'{$q}' hakkında yardımcı olabilirim. 'Yeni fatura oluştur', 'Ayarlara git' veya 'Destek masası' gibi sesli veya yazılı komutlar verebilirsiniz.",
                'hi' => "मैं आपकी सहायता के लिए तैयार हूं। आप 'नया इनवॉइस बनाएं' या 'सेटिंग्स' जैसे आदेश दे सकते हैं।",
                'es' => "Entendido. Puede darme comandos como 'Crear nueva factura', 'Ir a configuración' o 'Soporte técnico'. ¿En qué puedo ayudarle?",
                'fr' => "Je suis à votre écoute. Vous pouvez me demander de 'Créer une facture', 'Aller aux paramètres' ou 'Ouvrir le support'.",
                'de' => "Ich bin bereit. Sie können Befehle wie 'Neue Rechnung erstellen' oder 'Einstellungen öffnen' geben. Wie kann ich helfen?",
                default => "I understand you are asking about '{$q}'. You can give me voice or text commands like 'Create new invoice', 'Go to settings', 'Open Support Desk', or 'How does QR verification work?'. How may I assist you?",
            },
            'lang' => $voiceLang,
            'suggestions' => match($lang) {
                'ur' => ['نیا انوائس بنائیں', 'سپورٹ ڈیسک کھولیں', 'سیٹنگز'],
                'ar' => ['إنشاء فاتورة جديدة', 'فتح مكتب الدعم', 'الإعدادات'],
                'tr' => ['Yeni fatura oluştur', 'Destek Masası', 'Ayarlar'],
                'hi' => ['नया इनवॉइस बनाएं', 'सपोर्ट डेस्क', 'सेटिंग्स'],
                'es' => ['Crear factura', 'Mesa de soporte', 'Configuración'],
                'fr' => ['Créer une facture', 'Bureau de support', 'Paramètres'],
                'de' => ['Rechnung erstellen', 'Support-Desk', 'Einstellungen'],
                default => ['Create new invoice', 'Open Support Desk', 'Go to settings']
            }
        ];
    }
}
