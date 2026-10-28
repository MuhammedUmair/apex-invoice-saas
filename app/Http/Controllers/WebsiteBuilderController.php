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
        $pages = WebsitePage::orderBy('sort_order', 'asc')->get();

        return view('builder.index', compact('cms', 'navItems', 'pages'));
    }

    /**
     * Update Website Main Logo and Login Screen Logo
     */
    public function updateBranding(Request $request)
    {
        $request->validate([
            'website_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'website_logo_url' => 'nullable|string|max:500',
            'login_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'login_logo_url' => 'nullable|string|max:500',
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

        // 1. Website Main Logo
        if ($request->has('remove_website_logo')) {
            CmsContent::setVal('website_logo_url', null, 'branding');
        } elseif ($request->hasFile('website_logo_file')) {
            $file = $request->file('website_logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $this->sanitizeSvgIfNeeded($file, $ext);
            $filename = 'website_logo_' . time() . '.' . $ext;
            $file->move($uploadDir, $filename);
            CmsContent::setVal('website_logo_url', asset('images/uploads/branding/' . $filename), 'branding');
        } elseif ($request->filled('website_logo_url')) {
            CmsContent::setVal('website_logo_url', $request->input('website_logo_url'), 'branding');
        }

        // 2. Login Screen Logo
        if ($request->has('remove_login_logo')) {
            CmsContent::setVal('login_logo_url', null, 'branding');
        } elseif ($request->hasFile('login_logo_file')) {
            $file = $request->file('login_logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $this->sanitizeSvgIfNeeded($file, $ext);
            $filename = 'login_logo_' . time() . '.' . $ext;
            $file->move($uploadDir, $filename);
            CmsContent::setVal('login_logo_url', asset('images/uploads/branding/' . $filename), 'branding');
        } elseif ($request->filled('login_logo_url')) {
            CmsContent::setVal('login_logo_url', $request->input('login_logo_url'), 'branding');
        }

        // 3. Brand Texts
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
     * Update Website Header Navigation Items
     */
    public function updateNavigation(Request $request)
    {
        if ($request->input('action') === 'reset') {
            CmsContent::setVal('navigation_items', null, 'navigation');
            return back()->with('success', 'Navigation reset to default links.');
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
        return back()->with('success', 'Website navigation menu updated successfully!');
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
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['show_in_nav'] = $request->boolean('show_in_nav', false);

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
