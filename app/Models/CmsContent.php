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
        return ($item && !empty($item->content)) ? $item->content : $default;
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
}
