<?php

namespace App\Http\Controllers;

use App\Models\CmsContent;
use App\Models\DocArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    public function index()
    {
        $cms = CmsContent::all()->pluck('content', 'key')->toArray();
        $docArticles = DocArticle::orderBy('sort_order', 'asc')->get();

        return view('cms.index', compact('cms', 'docArticles'));
    }

    public function update(Request $request)
    {
        $fields = $request->except(['_token']);

        foreach ($fields as $key => $val) {
            if ($val !== null) {
                CmsContent::updateOrCreate(
                    ['key' => $key],
                    ['content' => $val, 'section' => 'website']
                );
            }
        }

        return back()->with('success', 'Marketing website content updated successfully!');
    }

    public function storeDocArticle(Request $request)
    {
        $data = $request->validate([
            'title_en' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content_en' => 'required|string',
        ]);

        $slug = Str::slug($data['title_en']) . '-' . Str::random(4);

        $article = DocArticle::create([
            'slug' => $slug,
            'category' => $data['category'],
            'title_en' => $data['title_en'],
            'content_en' => $data['content_en'],
            'is_published' => true,
            'sort_order' => (DocArticle::max('sort_order') ?? 0) + 1,
        ]);

        // Automatically trigger initial machine translation
        $this->translateArticle($article);

        return back()->with('success', "Documentation article '{$article->title_en}' created and auto-translated!");
    }

    public function updateDocArticle(DocArticle $article, Request $request)
    {
        $data = $request->validate([
            'title_en' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content_en' => 'required|string',
            'title_ur' => 'nullable|string',
            'content_ur' => 'nullable|string',
            'title_ar' => 'nullable|string',
            'content_ar' => 'nullable|string',
            'title_hi' => 'nullable|string',
            'content_hi' => 'nullable|string',
            'title_tr' => 'nullable|string',
            'content_tr' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $article->update($data);

        return back()->with('success', "Documentation article updated and reviewed versions saved!");
    }

    public function autoTranslateDoc(DocArticle $article)
    {
        $this->translateArticle($article);
        return back()->with('success', "Article '{$article->title_en}' successfully re-translated into Urdu, Arabic, Hindi, and Turkish!");
    }

    /**
     * Engine to auto-translate content into all supported languages
     */
    protected function translateArticle(DocArticle $article): void
    {
        $targets = ['ur', 'ar', 'hi', 'tr'];

        foreach ($targets as $lang) {
            $tTitle = $this->fetchTranslation($article->title_en, 'en', $lang);
            $tContent = $this->fetchTranslation($article->content_en, 'en', $lang);

            $article->{"title_{$lang}"} = $tTitle;
            $article->{"content_{$lang}"} = $tContent;
        }

        $article->save();
    }

    protected function fetchTranslation(string $text, string $source, string $target): string
    {
        if (empty(trim($text))) {
            return '';
        }

        try {
            // Free public translate endpoint with zero API key dependencies
            $response = Http::timeout(6)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $source,
                'tl' => $target,
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body[0]) && is_array($body[0])) {
                    $translated = '';
                    foreach ($body[0] as $segment) {
                        $translated .= $segment[0] ?? '';
                    }
                    if (!empty($translated)) {
                        return $translated;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Translation fallback triggered for [{$target}]: " . $e->getMessage());
        }

        return $text; // Fallback to original text if service offline
    }
}
