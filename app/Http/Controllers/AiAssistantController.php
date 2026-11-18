<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    /**
     * Process AI Copilot text and voice inquiries.
     */
    public function chat(Request $request)
    {
        $query = trim($request->input('message', ''));
        if (empty($query)) {
            return response()->json(['reply' => 'Please ask a question or give a command!']);
        }

        $user = Auth::user();
        $workspace = $user?->workspace;
        $q = strtolower($query);

        // 1. Navigation Commands
        if (str_contains($q, 'create invoice') || str_contains($q, 'new invoice')) {
            return response()->json([
                'reply' => 'Opening the invoice builder so you can create a new invoice.',
                'action' => ['type' => 'navigate', 'url' => route('invoices.create')],
                'suggestions' => ['How do I add line items?', 'How does the review screen work?']
            ]);
        }

        if (str_contains($q, 'go to invoice') || str_contains($q, 'view invoice') || str_contains($q, 'show invoice') || str_contains($q, 'list invoice')) {
            return response()->json([
                'reply' => 'Taking you to your invoices list.',
                'action' => ['type' => 'navigate', 'url' => route('invoices.index')],
                'suggestions' => ['How to recall an invoice?', 'How do I download a PDF?']
            ]);
        }

        if (str_contains($q, 'setting') || str_contains($q, 'smtp') || str_contains($q, 'email config')) {
            return response()->json([
                'reply' => 'Opening Agency Settings. Here you can configure your custom Outgoing SMTP, business branding, and tax rules.',
                'action' => ['type' => 'navigate', 'url' => route('settings.index')],
                'suggestions' => ['How to test SMTP?', 'How to set up Canadian Red Maple logo?']
            ]);
        }

        if (str_contains($q, 'client') || str_contains($q, 'customer')) {
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

        if (str_contains($q, 'help') || str_contains($q, 'guide') || str_contains($q, 'manual')) {
            return response()->json([
                'reply' => 'Opening the comprehensive Invoicy Help & Knowledge Center.',
                'action' => ['type' => 'navigate', 'url' => route('help.index')],
                'suggestions' => ['Show invoice recall guide', 'Explain QR verification']
            ]);
        }

        if (str_contains($q, 'billing') || str_contains($q, 'plan') || str_contains($q, 'upgrade') || str_contains($q, 'coupon')) {
            return response()->json([
                'reply' => 'Navigating to Plan & Billing. You can upgrade, apply coupons, and track request status here.',
                'action' => ['type' => 'navigate', 'url' => route('membership.index')],
                'suggestions' => ['Apply FREE100 coupon', 'Request Pro Business upgrade']
            ]);
        }

        // 2. Explanations & Knowledge Engine
        if (str_contains($q, 'recall') || str_contains($q, 'pull back') || str_contains($q, 'retract') || str_contains($q, 'mistake')) {
            return response()->json([
                'reply' => "To recall an invoice that had mistakes, open the invoice details page and click 'Recall / Pull Back'. This immediately revokes the client's public link and PDF token, and reverts the invoice back to Draft mode so you can correct it safely.",
                'suggestions' => ['Go to invoices', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'qr') || str_contains($q, 'verify') || str_contains($q, 'authentic') || str_contains($q, 'seal')) {
            return response()->json([
                'reply' => "Every published invoice includes an authentic 2D QR code and cryptographic fingerprint seal. Scanning the QR code with any camera directs the client or auditor to the official Invoicy Verification Screen, proving document authenticity and payment status.",
                'suggestions' => ['View invoice PDF', 'Open Help Center']
            ]);
        }

        if (str_contains($q, 'smtp test') || str_contains($q, 'test smtp') || str_contains($q, 'smtp failed')) {
            return response()->json([
                'reply' => "You can test your SMTP in Agency Settings -> Outgoing Email (SMTP). Click 'Send Live Test Email'. Invoicy sends a branded Canadian Red Maple test email with full diagnostics to confirm your server is working.",
                'suggestions' => ['Go to settings', 'How to configure port 465 SSL?']
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
                'reply' => "Invoicy automatically detects when your client opens an invoice (via email tracking beacon or customer portal). You receive an immediate email notification and activity alert recording the exact timestamp.",
                'suggestions' => ['Go to invoices', 'Open Help Center']
            ]);
        }

        // Default response
        return response()->json([
            'reply' => "I am your Invoicy AI Copilot. You can give me voice or text commands like 'Create new invoice', 'Go to settings', 'How do I recall an invoice?', or 'How does QR verification work?'. How may I assist you?",
            'suggestions' => ['Create new invoice', 'How do I recall an invoice?', 'Explain QR code verification', 'Go to settings']
        ]);
    }
}
