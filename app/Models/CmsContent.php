<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $fillable = [
        'key',
        'section',
        'content',
        'locale',
    ];

    /**
     * Helper to get CMS content with a default fallback
     */
    public static function getVal(string $key, string $default = ''): string
    {
        $item = static::where('key', $key)->first();
        return ($item && $item->content !== null && $item->content !== '') ? (string) $item->content : $default;
    }

    /**
     * Helper to set CMS content key/value
     */
    public static function setVal(string $key, ?string $content, string $section = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['content' => $content, 'section' => $section]
        );
    }

    /**
     * Get Master System Brand Logo URL (used everywhere throughout system and web)
     */
    public static function getSystemLogo(): ?string
    {
        // 1. Primary: System logo from CMS
        $logo = static::getVal('system_logo_url');
        if (!empty($logo)) {
            return $logo;
        }

        // 2. Website logo from CMS
        $webLogo = static::getVal('website_logo_url');
        if (!empty($webLogo)) {
            return $webLogo;
        }

        // 3. Login logo from CMS
        $loginLogo = static::getVal('login_logo_url');
        if (!empty($loginLogo)) {
            return $loginLogo;
        }

        // 4. Authenticated user's workspace logo
        if (auth()->check() && auth()->user()->workspace && !empty(auth()->user()->workspace->logo_url)) {
            return auth()->user()->workspace->logo_url;
        }

        return null;
    }

    /**
     * Get Website Main Logo URL
     */
    public static function getWebsiteLogo(): ?string
    {
        return static::getSystemLogo();
    }

    /**
     * Get Website Login Screen Logo URL
     */
    public static function getLoginLogo(): ?string
    {
        return static::getSystemLogo();
    }

    /**
     * Get Website Favicon URL (if set, otherwise null)
     */
    public static function getFaviconUrl(): ?string
    {
        $val = static::getVal('favicon_url');
        return !empty($val) ? $val : null;
    }

    /**
     * Get Navigation Items for Header Menu
     */
    public static function getNavItems(): array
    {
        $raw = static::getVal('navigation_items');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                // Filter only active items
                return array_values(array_filter($decoded, fn($item) => !isset($item['active']) || (bool)$item['active']));
            }
        }

        // Default navigation items if none configured
        $defaultNav = [
            ['label' => 'Features', 'url' => '#features', 'target' => '_self', 'active' => true],
            ['label' => 'Reminders & Planning', 'url' => '#reminders', 'target' => '_self', 'active' => true],
            ['label' => 'Future SaaS Suite', 'url' => '#saas-suite', 'target' => '_self', 'active' => true],
            ['label' => 'Pricing', 'url' => '#pricing', 'target' => '_self', 'active' => true],
            ['label' => 'Docs', 'url' => '/docs', 'target' => '_self', 'active' => true],
        ];

        // Also check if any published website pages should appear in nav
        try {
            $customPages = WebsitePage::inNav()->get();
            foreach ($customPages as $cp) {
                $defaultNav[] = [
                    'label' => $cp->title,
                    'url' => url('/p/' . $cp->slug),
                    'target' => '_self',
                    'active' => true,
                ];
            }
        } catch (\Throwable $e) {
            // In case table not yet migrated during bootstrap
        }

        return $defaultNav;
    }

    /**
     * Get all nav items including inactive for builder admin
     */
    public static function getAllNavItemsForAdmin(): array
    {
        $raw = static::getVal('navigation_items');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['label' => 'Features', 'url' => '#features', 'target' => '_self', 'active' => true],
            ['label' => 'Reminders & Planning', 'url' => '#reminders', 'target' => '_self', 'active' => true],
            ['label' => 'Future SaaS Suite', 'url' => '#saas-suite', 'target' => '_self', 'active' => true],
            ['label' => 'Pricing', 'url' => '#pricing', 'target' => '_self', 'active' => true],
            ['label' => 'Docs', 'url' => '/docs', 'target' => '_self', 'active' => true],
        ];
    }

    /**
     * Get Footer Navigation Items (with fallback to default)
     */
    public static function getFooterNavItems(): array
    {
        $raw = static::getVal('footer_navigation_items');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return array_values(array_filter($decoded, fn($item) => !isset($item['active']) || (bool)$item['active']));
            }
        }

        $defaultFooter = [
            ['label' => 'Privacy Policy', 'url' => url('/p/privacy-policy'), 'target' => '_self', 'active' => true],
            ['label' => 'Terms of Service', 'url' => url('/p/terms-of-service'), 'target' => '_self', 'active' => true],
            ['label' => 'About Us', 'url' => url('/p/about-us'), 'target' => '_self', 'active' => true],
            ['label' => 'Documentation', 'url' => route('docs.index'), 'target' => '_self', 'active' => true],
            ['label' => 'redmapleagency.com', 'url' => 'https://redmapleagency.com', 'target' => '_blank', 'active' => true],
        ];

        try {
            $footerPages = WebsitePage::inFooter()->get();
            $existingUrls = array_column($defaultFooter, 'url');
            foreach ($footerPages as $fp) {
                $pUrl = url('/p/' . $fp->slug);
                if (!in_array($pUrl, $existingUrls)) {
                    $defaultFooter[] = [
                        'label' => $fp->title,
                        'url' => $pUrl,
                        'target' => '_self',
                        'active' => true,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return $defaultFooter;
    }

    /**
     * Get all footer nav items for builder admin
     */
    public static function getAllFooterNavItemsForAdmin(): array
    {
        $raw = static::getVal('footer_navigation_items');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['label' => 'Privacy Policy', 'url' => url('/p/privacy-policy'), 'target' => '_self', 'active' => true],
            ['label' => 'Terms of Service', 'url' => url('/p/terms-of-service'), 'target' => '_self', 'active' => true],
            ['label' => 'About Us', 'url' => url('/p/about-us'), 'target' => '_self', 'active' => true],
            ['label' => 'Documentation', 'url' => route('docs.index'), 'target' => '_self', 'active' => true],
            ['label' => 'redmapleagency.com', 'url' => 'https://redmapleagency.com', 'target' => '_blank', 'active' => true],
        ];
    }

    /**
     * Get Welcome Tour Slides (dynamic array of sections with fallback to legacy steps 1-4)
     */
    public static function getWelcomeTourSlides(): array
    {
        $raw = static::getVal('welcome_tour_slides_json');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return array_values($decoded);
            }
        }

        // Default legacy fallback slides
        return [
            [
                'title' => static::getVal('welcome_step1_title', '1. Create High-Precision Invoices in Seconds'),
                'text' => static::getVal('welcome_step1_text', 'Click "+ New Invoice" in your sidebar or ask the AI Copilot. Enter client details or use Walk-in mode for instantaneous cash and retail billing without client signup.'),
                'badge' => 'Zero Friction',
                'icon' => 'ph-receipt',
                'color' => 'red',
            ],
            [
                'title' => static::getVal('welcome_step2_title', '2. 1-Click Industry Service Presets & Custom Line Items'),
                'text' => static::getVal('welcome_step2_text', 'Access 45+ tailored presets for your business category (Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, Marketing Agency). 1-click adds pre-priced line items with matching units.'),
                'badge' => '45+ Presets',
                'icon' => 'ph-lightning',
                'color' => 'amber',
            ],
            [
                'title' => static::getVal('welcome_step3_title', '3. Private Draft Invoices & Zero Accidental Dispatch'),
                'text' => static::getVal('welcome_step3_text', 'Draft invoices are completely private. Emails are never dispatched, and PDF downloads, customer payment portals, and QR verification screens are disabled until you click "Issue & Publish".'),
                'badge' => 'Draft Safeguard',
                'icon' => 'ph-shield-check',
                'color' => 'sky',
            ],
            [
                'title' => static::getVal('welcome_step4_title', '4. Cryptographic QR Seals & Automated Reminders'),
                'text' => static::getVal('welcome_step4_text', 'Published invoices feature authentic ISO 2D QR verification codes for fraud prevention and automated milestone payment reminder schedules (45, 30, 15, 7, 3, and 2 days before due date).'),
                'badge' => 'Automated Reminders',
                'icon' => 'ph-bell-ringing',
                'color' => 'emerald',
            ],
        ];
    }

    /**
     * Save dynamic welcome tour slides
     */
    public static function saveWelcomeTourSlides(array $slides): void
    {
        $cleanSlides = [];
        foreach ($slides as $i => $s) {
            $cleanSlides[] = [
                'title' => trim($s['title'] ?? 'Tour Step ' . ($i + 1)),
                'text' => trim($s['text'] ?? ''),
                'badge' => trim($s['badge'] ?? 'Feature Spotlight'),
                'icon' => trim($s['icon'] ?? 'ph-sparkle'),
                'color' => trim($s['color'] ?? 'amber'),
            ];
        }

        static::setVal('welcome_tour_slides_json', json_encode($cleanSlides), 'general');

        // Also synchronize legacy keys for step 1 to 4 if present
        for ($k = 1; $k <= 4; $k++) {
            if (isset($cleanSlides[$k - 1])) {
                static::setVal("welcome_step{$k}_title", $cleanSlides[$k - 1]['title'], 'general');
                static::setVal("welcome_step{$k}_text", $cleanSlides[$k - 1]['text'], 'general');
            }
        }
    }
}
