<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $filterPlacement = $request->query('placement', 'all');

        $query = Advertisement::query();
        if ($filterPlacement !== 'all') {
            $query->where('placement', $filterPlacement);
        }

        $ads = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        $stats = [
            'total' => Advertisement::count(),
            'active' => Advertisement::where('is_active', true)->count(),
            'impressions' => Advertisement::sum('impressions_count'),
            'clicks' => Advertisement::sum('clicks_count'),
        ];

        return view('ads.index', compact('ads', 'stats', 'filterPlacement'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:advertisements,id',
            'title' => 'required|string|max:255',
            'placement' => 'required|string|in:invoice_sidebar,app_sidebar,landing_page,client_portal,all',
            'type' => 'required|string|in:image,html,text',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'image_path' => 'nullable|string|max:500',
            'destination_url' => 'nullable|string|max:500',
            'alt_text' => 'nullable|string|max:255',
            'open_in_new_tab' => 'nullable|boolean',
            'html_code' => 'nullable|string',
            'headline' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
            'badge_text' => 'nullable|string|max:100',
            'cta_text' => 'nullable|string|max:100',
            'theme_color' => 'required|string|in:brand,slate,purple,emerald,blue,amber',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $ad = !empty($validated['id']) ? Advertisement::findOrFail($validated['id']) : new Advertisement();

        // Handle Image Upload if type is image
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $realMime = $file->getMimeType();

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/svg'];
            if (!in_array($realMime, $allowedMimes)) {
                return back()->withErrors(['image_file' => 'Invalid image format. Allowed formats: PNG, JPG, WEBP, SVG.']);
            }

            // High Security SVG Sanitization
            if ($ext === 'svg' || str_contains($realMime, 'svg')) {
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
                    return back()->withErrors(['image_file' => 'Malicious executable code detected in uploaded SVG file. Upload rejected.']);
                }
                file_put_contents($file->getRealPath(), $cleanedSvg);
            }

            $safeTitle = Str::slug($validated['title']);
            $filename = 'ad_' . $safeTitle . '_' . time() . '.' . $ext;
            $destinationDir = public_path('images/uploads/ads');
            if (!file_exists($destinationDir)) {
                @mkdir($destinationDir, 0775, true);
            }
            $file->move($destinationDir, $filename);
            $ad->image_path = asset('images/uploads/ads/' . $filename);
        } elseif (!empty($validated['image_path'])) {
            $ad->image_path = $validated['image_path'];
        }

        $ad->title = $validated['title'];
        $ad->placement = $validated['placement'];
        $ad->type = $validated['type'];
        $ad->destination_url = $validated['destination_url'] ?? null;
        $ad->alt_text = $validated['alt_text'] ?? null;
        $ad->open_in_new_tab = $request->boolean('open_in_new_tab', true);
        $ad->html_code = $validated['html_code'] ?? null;
        $ad->headline = $validated['headline'] ?? null;
        $ad->body_text = $validated['body_text'] ?? null;
        $ad->badge_text = $validated['badge_text'] ?? 'SPONSORED PROMO';
        $ad->cta_text = $validated['cta_text'] ?? 'Learn More';
        $ad->theme_color = $validated['theme_color'] ?? 'brand';
        $ad->is_active = $request->boolean('is_active', true);
        $ad->sort_order = $validated['sort_order'] ?? 0;

        $ad->save();

        $action = !empty($validated['id']) ? 'updated' : 'created';
        return redirect()->route('admin.ads.index')->with('success', "Advertisement '{$ad->title}' {$action} successfully!");
    }

    public function toggle(Advertisement $advertisement)
    {
        $advertisement->is_active = !$advertisement->is_active;
        $advertisement->save();

        $status = $advertisement->is_active ? 'activated' : 'paused';
        return back()->with('success', "Advertisement '{$advertisement->title}' {$status}!");
    }

    public function destroy(Advertisement $advertisement)
    {
        $title = $advertisement->title;
        $advertisement->delete();

        return redirect()->route('admin.ads.index')->with('success', "Advertisement '{$title}' removed successfully!");
    }

    public function click(Advertisement $advertisement)
    {
        $advertisement->increment('clicks_count');

        $destination = $advertisement->destination_url ?: route('home');
        return redirect()->away($destination);
    }
}
