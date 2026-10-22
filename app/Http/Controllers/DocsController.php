<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', session('docs_lang', 'en'));
        if (!in_array($lang, ['en', 'ur', 'ar', 'hi', 'tr'])) {
            $lang = 'en';
        }
        session(['docs_lang' => $lang]);

        $section = $request->get('section', 'getting-started');

        return view('docs.index', [
            'currentLang' => $lang,
            'currentSection' => $section,
            'languages' => [
                'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸', 'dir' => 'ltr'],
                'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'flag' => '🇵🇰', 'dir' => 'rtl'],
                'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇦🇪', 'dir' => 'rtl'],
                'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳', 'dir' => 'ltr'],
                'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷', 'dir' => 'ltr'],
            ],
        ]);
    }
}
