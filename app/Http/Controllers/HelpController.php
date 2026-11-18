<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    /**
     * Display the comprehensive interactive Help & Documentation Center.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $user?->workspace;
        $activeTab = $request->query('section', 'overview');

        return view('help.index', compact('user', 'workspace', 'activeTab'));
    }
}
