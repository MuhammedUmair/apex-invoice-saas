<?php

namespace App\Http\Controllers;

use App\Models\WebsitePage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = WebsitePage::where('slug', $slug)->firstOrFail();

        if (!$page->is_published && !auth()->check()) {
            abort(404);
        }

        return view('pages.show', compact('page'));
    }
}
