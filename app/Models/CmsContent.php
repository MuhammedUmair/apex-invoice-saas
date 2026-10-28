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
     * Get Website Main Logo URL (if set, otherwise null)
     */
    public static function getWebsiteLogo(): ?string
    {
        $val = static::getVal('website_logo_url');
        return !empty($val) ? $val : null;
    }

    /**
     * Get Website Login Screen Logo URL (if set, otherwise null)
     */
    public static function getLoginLogo(): ?string
    {
        $val = static::getVal('login_logo_url');
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
