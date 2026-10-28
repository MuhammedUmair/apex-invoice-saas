<?php

namespace App\Http\Controllers;

use App\Models\CmsContent;
use App\Models\WebsitePage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebsiteBuilderController extends Controller
{
    public function index()
    {
        $cms = CmsContent::all()->pluck('content', 'key')->toArray();
        $navItems = CmsContent::getAllNavItemsForAdmin();
        $footerNavItems = CmsContent::getAllFooterNavItemsForAdmin();
        $pages = WebsitePage::orderBy('sort_order', 'asc')->get();

        return view('builder.index', compact('cms', 'navItems', 'footerNavItems', 'pages'));
    }

    /**
     * Update Unified System Brand Logo, Favicon, and Branding
     */
    public function updateBranding(Request $request)
    {
        $request->validate([
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'logo_url' => 'nullable|string|max:500',
            'website_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'website_logo_url' => 'nullable|string|max:500',
            'login_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'login_logo_url' => 'nullable|string|max:500',
            'favicon_file' => 'nullable|file|mimes:ico,png,jpg,jpeg,webp,svg|max:2048',
            'favicon_url' => 'nullable|string|max:500',
            'website_brand_name' => 'nullable|string|max:100',
            'website_brand_sub' => 'nullable|string|max:100',
            'login_title' => 'nullable|string|max:150',
            'login_subtitle' => 'nullable|string|max:255',
            'footer_copyright' => 'nullable|string|max:255',
        ]);

        $uploadDir = public_path('images/uploads/branding');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        // 1. Unified Single Brand Logo Upload (Changes Everywhere: Web, App, Login, Portal, PDFs)
        $uploadedLogoUrl = null;
        $logoFile = $request->file('logo_file') ?? $request->file('website_logo_file') ?? $request->file('login_logo_file');

        if ($request->has('remove_logo') || $request->has('remove_website_logo') || $request->has('remove_login_logo')) {
            CmsContent::setVal('system_logo_url', null, 'branding');
            CmsContent::setVal('website_logo_url', null, 'branding');
            CmsContent::setVal('login_logo_url', null, 'branding');
            if (auth()->check() && auth()->user()->workspace) {
                auth()->user()->workspace->update(['logo_url' => null]);
            }
        } elseif ($logoFile) {
            $ext = strtolower($logoFile->getClientOriginalExtension());
            $this->sanitizeSvgIfNeeded($logoFile, $ext);
            $filename = 'system_logo_' . time() . '.' . $ext;
            $logoFile->move($uploadDir, $filename);
            $uploadedLogoUrl = asset('images/uploads/branding/' . $filename);
        } elseif ($request->filled('logo_url')) {
            $uploadedLogoUrl = $request->input('logo_url');
        } elseif ($request->filled('website_logo_url')) {
            $uploadedLogoUrl = $request->input('website_logo_url');
        } elseif ($request->filled('login_logo_url')) {
            $uploadedLogoUrl = $request->input('login_logo_url');
        }

        if ($uploadedLogoUrl) {
            CmsContent::setVal('system_logo_url', $uploadedLogoUrl, 'branding');
            CmsContent::setVal('website_logo_url', $uploadedLogoUrl, 'branding');
            CmsContent::setVal('login_logo_url', $uploadedLogoUrl, 'branding');
            if (auth()->check() && auth()->user()->workspace) {
                auth()->user()->workspace->update(['logo_url' => $uploadedLogoUrl]);
            }
        }

        // 3. Website Favicon
        if ($request->has('remove_favicon')) {
            CmsContent::setVal('favicon_url', null, 'branding');
        } elseif ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $this->sanitizeSvgIfNeeded($file, $ext);
            $filename = 'favicon_' . time() . '.' . $ext;
            $file->move($uploadDir, $filename);
            CmsContent::setVal('favicon_url', asset('images/uploads/branding/' . $filename), 'branding');
        } elseif ($request->filled('favicon_url')) {
            CmsContent::setVal('favicon_url', $request->input('favicon_url'), 'branding');
        }

        // 4. Brand Texts
        if ($request->filled('website_brand_name')) {
            CmsContent::setVal('website_brand_name', $request->input('website_brand_name'), 'branding');
        }
        if ($request->filled('website_brand_sub')) {
            CmsContent::setVal('website_brand_sub', $request->input('website_brand_sub'), 'branding');
        }
        if ($request->filled('login_title')) {
            CmsContent::setVal('login_title', $request->input('login_title'), 'branding');
        }
        if ($request->filled('login_subtitle')) {
            CmsContent::setVal('login_subtitle', $request->input('login_subtitle'), 'branding');
        }
        if ($request->filled('footer_copyright')) {
            CmsContent::setVal('footer_copyright', $request->input('footer_copyright'), 'branding');
        }

        return back()->with('success', 'Website and login screen branding updated successfully!');
    }

    /**
     * Update Website Header or Footer Navigation Items
     */
    public function updateNavigation(Request $request)
    {
        $navType = $request->input('nav_type', 'header');

        if ($request->input('action') === 'reset_footer' || ($navType === 'footer' && $request->input('action') === 'reset')) {
            CmsContent::setVal('footer_navigation_items', null, 'navigation');
            return back()->with('success', 'Footer navigation reset to default links.');
        }

        if ($request->input('action') === 'reset') {
            CmsContent::setVal('navigation_items', null, 'navigation');
            return back()->with('success', 'Header navigation reset to default links.');
        }

        if ($navType === 'footer' || $request->has('footer_nav_items')) {
            $items = $request->input('footer_nav_items', []);
            $cleanItems = [];

            foreach ($items as $item) {
                if (!empty($item['label']) && !empty($item['url'])) {
                    $cleanItems[] = [
                        'label' => trim($item['label']),
                        'url' => trim($item['url']),
                        'target' => $item['target'] ?? '_self',
                        'active' => isset($item['active']) ? (bool)$item['active'] : true,
                    ];
                }
            }

            CmsContent::setVal('footer_navigation_items', json_encode($cleanItems), 'navigation');
            return back()->with('success', 'Website footer navigation menu updated successfully!');
        }

        $items = $request->input('nav_items', []);
        $cleanItems = [];

        foreach ($items as $item) {
            if (!empty($item['label']) && !empty($item['url'])) {
                $cleanItems[] = [
                    'label' => trim($item['label']),
                    'url' => trim($item['url']),
                    'target' => $item['target'] ?? '_self',
                    'active' => isset($item['active']) ? (bool)$item['active'] : true,
                ];
            }
        }

        CmsContent::setVal('navigation_items', json_encode($cleanItems), 'navigation');
        return back()->with('success', 'Website header navigation menu updated successfully!');
    }

    /**
     * Update Landing Page Marketing Sections
     */
    public function updateSections(Request $request)
    {
        $fields = $request->except(['_token']);

        foreach ($fields as $key => $val) {
            if ($val !== null) {
                CmsContent::setVal($key, $val, 'website_sections');
            }
        }

        return back()->with('success', 'Website landing page sections updated successfully!');
    }

    /**
     * Create Custom Website Page
     */
    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:website_pages,slug',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        // Avoid collision
        if (WebsitePage::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        $page = WebsitePage::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'meta_description' => $validated['meta_description'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'show_in_nav' => $request->boolean('show_in_nav', false),
            'show_in_footer' => $request->boolean('show_in_footer', false),
            'sort_order' => $validated['sort_order'] ?? ((WebsitePage::max('sort_order') ?? 0) + 1),
        ]);

        return back()->with('success', "Website page '{$page->title}' published at /p/{$page->slug}!");
    }

    /**
     * Update Custom Website Page
     */
    public function updatePage(WebsitePage $page, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:website_pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['show_in_nav'] = $request->boolean('show_in_nav', false);
        $validated['show_in_footer'] = $request->boolean('show_in_footer', false);

        $page->update($validated);

        return back()->with('success', "Website page '{$page->title}' updated successfully!");
    }

    /**
     * Delete Custom Website Page
     */
    public function destroyPage(WebsitePage $page)
    {
        $title = $page->title;
        $page->delete();

        return back()->with('success', "Page '{$title}' removed.");
    }

    /**
     * High Security SVG Sanitization Helper
     */
    protected function sanitizeSvgIfNeeded($file, string $ext): void
    {
        if ($ext === 'svg' || str_contains($file->getMimeType(), 'svg')) {
            $svgContent = file_get_contents($file->getRealPath());
            $dangerousPatterns = [
                '/<script\b[^>]*>(.*?)<\/script>/is',
                '/\bon\w+\s*=\s*["\'][^"\']*["\']/i',
                '/\bon\w+\s*=\s*[^>\s]+/i',
                '/href\s*=\s*["\']\s*javascript:[^"\']*["\']/i',
                '/<foreignObject\b[^>]*>(.*?)<\/foreignObject>/is',
            ];
            $cleanedSvg = preg_replace($dangerousPatterns, '', $svgContent);
            if (preg_match('/<script|javascript:|data:text\/html/i', $cleanedSvg)) {
                abort(400, 'Malicious executable code detected in uploaded SVG file.');
            }
            file_put_contents($file->getRealPath(), $cleanedSvg);
        }
    }
}
