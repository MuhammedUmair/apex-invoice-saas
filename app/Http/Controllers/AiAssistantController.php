<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    /**
     * Process AI Agent text and voice inquiries.
     */
    public function chat(Request $request)
    {
        $query = trim($request->input('message', ''));
        if (empty($query)) {
            return response()->json(['reply' => 'Please ask a question or give a command!']);
        }

        // Support both web and superadmin guards
        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $q = strtolower($query);

        // 1. Navigation Commands
        if (str_contains($q, 'create invoice') || str_contains($q, 'new invoice') || str_contains($q, 'make invoice') || str_contains($q, 'issue invoice')) {
            return response()->json([
                'reply' => 'Opening the invoice builder for you to create and publish a new invoice.',
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'suggestions' => ['How do I add line items?', 'How does the review screen work?']
            ]);
        }

        if (str_contains($q, 'go to invoice') || str_contains($q, 'view invoice') || str_contains($q, 'show invoice') || str_contains($q, 'list invoice') || $q === 'invoices') {
            return response()->json([
                'reply' => 'Taking you to your invoices dashboard.',
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
                'suggestions' => ['How to recall an invoice?', 'How do I download a PDF?']
            ]);
        }

        if (str_contains($q, 'setting') || str_contains($q, 'smtp') || str_contains($q, 'email config') || str_contains($q, 'mail server')) {
            return response()->json([
                'reply' => 'Opening Agency Settings. Here you can configure your custom Outgoing SMTP, Canadian branding, and tax rules.',
                'action' => ['type' => 'navigate', 'url' => route('settings.index')],
                'suggestions' => ['How to test SMTP?', 'How to set up logo?']
            ]);
        }

        if (str_contains($q, 'client') || str_contains($q, 'customer') || str_contains($q, 'crm')) {
            return response()->json([
                'reply' => 'Navigating to your Client CRM ledger.',
                'action' => ['type' => 'navigate', 'url' => route('clients.index')],
                'suggestions' => ['Add new client', 'Set client tax currency']
            ]);
        }

        if (str_contains($q, 'reminder') || str_contains($q, 'schedule')) {
            return response()->json([
                'reply' => 'Taking you to Automated Reminders & Scheduler.',
                'action' => ['type' => 'navigate', 'url' => route('reminders.index')],
                'suggestions' => ['Run reminder cycle now', 'What are reminder milestones?']
            ]);
        }

        if (str_contains($q, 'help') || str_contains($q, 'guide') || str_contains($q, 'manual') || str_contains($q, 'docs')) {
            return response()->json([
                'reply' => 'Opening the comprehensive Invoicy Help & Knowledge Center.',
                'action' => ['type' => 'navigate', 'url' => route('help.index')],
                'suggestions' => ['Show invoice recall guide', 'Explain QR verification']
            ]);
        }

        if (str_contains($q, 'billing') || str_contains($q, 'plan') || str_contains($q, 'upgrade') || str_contains($q, 'subscription')) {
            $url = $isSuperAdmin ? route('cp.dashboard') : route('membership.index');
            return response()->json([
                'reply' => 'Navigating to Plan & Billing where you can manage subscriptions, apply coupons, and track upgrade requests.',
                'action' => ['type' => 'navigate', 'url' => $url],
                'suggestions' => ['Apply FREE100 coupon', 'Request Pro Business upgrade']
            ]);
        }

        if (str_contains($q, 'super admin') || str_contains($q, 'superadmin') || str_contains($q, 'control panel') || $q === 'cp') {
            return response()->json([
                'reply' => 'Taking you to the Super Admin Control Centre (/cp).',
                'action' => ['type' => 'navigate', 'url' => route('cp.dashboard')],
                'suggestions' => ['System metrics', 'Manage coupons']
            ]);
        }

        // 2. Explanations & Knowledge Engine
        if (str_contains($q, 'who are you') || str_contains($q, 'what can you do') || str_contains($q, 'hello') || str_contains($q, 'hi') || str_contains($q, 'hey')) {
            return response()->json([
                'reply' => "Hello! I am your AI Agent. I can help you create invoices, navigate the system, manage clients, recall invoices, configure custom SMTP, and answer financial questions. You can type or speak hands-free!",
                'suggestions' => ['Create new invoice', 'How do I recall an invoice?', 'Explain QR code verification', 'Go to settings']
            ]);
        }

        if (str_contains($q, 'recall') || str_contains($q, 'pull back') || str_contains($q, 'retract') || str_contains($q, 'mistake') || str_contains($q, 'wrong invoice')) {
            return response()->json([
                'reply' => "To recall an invoice that had mistakes, open the invoice details page and click 'Recall / Pull Back'. This immediately revokes the client's public link and PDF token, and reverts the invoice back to Draft mode so you can correct it safely. You can also notify the client via an automated retraction email.",
                'suggestions' => ['Go to invoices', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'qr') || str_contains($q, 'verify') || str_contains($q, 'authentic') || str_contains($q, 'seal') || str_contains($q, 'scan')) {
            return response()->json([
                'reply' => "Every published invoice includes an authentic 2D QR code and cryptographic fingerprint seal powered by ISO/IEC 18004 standards. Scanning the QR code with any camera directs the client or auditor to the official Invoicy Verification Screen, proving document authenticity and payment status.",
                'suggestions' => ['View invoice PDF', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'smtp test') || str_contains($q, 'test smtp') || str_contains($q, 'smtp') || str_contains($q, 'email fail')) {
            return response()->json([
                'reply' => "You can configure your authenticated SMTP server in Agency Settings -> Outgoing Email (SMTP). Recommended settings: Port 465 with SSL. Click 'Send Live Test Email' to verify connection with a branded Canadian Red Maple diagnostic test email.",
                'suggestions' => ['Go to settings', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'coupon') || str_contains($q, 'promo') || str_contains($q, 'free100') || str_contains($q, 'discount')) {
            return response()->json([
                'reply' => "You can apply promotional coupons such as FREE100 during registration or inside Plan & Billing to instantly unlock full service or billing discounts. Coupons can also be added directly on customer invoice portals.",
                'suggestions' => ['Go to Plan & Billing', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'review') || str_contains($q, 'pre-dispatch') || str_contains($q, 'publish')) {
            return response()->json([
                'reply' => "When you click 'Issue & Publish Invoice' in the builder, Invoicy displays a Pre-Dispatch Review Screen. This lets you inspect the recipient, line items, taxes, and email dispatch preferences before anything is sent.",
                'suggestions' => ['Create new invoice', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'open') || str_contains($q, 'view alert') || str_contains($q, 'tracking')) {
            return response()->json([
                'reply' => "Invoicy automatically tracks when your client opens an invoice (via email tracking beacon or customer portal). You receive an immediate email notification and activity alert recording the exact timestamp.",
                'suggestions' => ['Go to invoices', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'draft') || str_contains($q, 'empty')) {
            return response()->json([
                'reply' => "Saved draft invoices and invoices without line items have their public customer portals and PDF downloads strictly locked. This prevents clients from viewing unfinished drafts until you officially Issue and Publish them.",
                'suggestions' => ['Go to invoices', 'Create new invoice']
            ]);
        }

        // Default response
        return response()->json([
            'reply' => "I understand you are asking about '{$query}'. You can give me voice or text commands like 'Create new invoice', 'Go to settings', 'How do I recall an invoice?', or 'How does QR verification work?'. How may I assist you?",
            'suggestions' => ['Create new invoice', 'How do I recall an invoice?', 'Explain QR code verification', 'Go to settings']
        ]);
    }
}
