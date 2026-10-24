<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Reminder: Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #dc2626; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; opacity: 0.9; font-size: 13px; }
        .body { padding: 32px; }
        .details-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .row:last-child { margin-bottom: 0; border-top: 1px dashed #fca5a5; padding-top: 10px; font-weight: 700; font-size: 16px; color: #dc2626; }
        .badge { display: inline-block; padding: 4px 10px; background: #fee2e2; color: #991b1b; border-radius: 9999px; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
        .btn-group { margin-top: 28px; text-align: center; }
        .btn { display: inline-block; padding: 12px 28px; background: #dc2626; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; margin-right: 8px; }
        .btn-secondary { background: #f1f5f9; color: #334155 !important; border: 1px solid #cbd5e1; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple</h1>
            <p>{{ $invoice->workspace->name ?? 'RedMaple Agency' }}</p>
        </div>
        <div class="body">
            <span class="badge">Payment Reminder: Due in {{ $daysBefore }} Days</span>
            <h2 style="font-size: 18px; margin-top: 0; color: #0f172a;">Friendly Payment Reminder</h2>
            <p>Dear {{ $invoice->client->name }},</p>
            <p>This is a friendly reminder that invoice <strong>#{{ $invoice->invoice_number }}</strong> is due on <strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'soon' }}</strong> (in {{ $daysBefore }} days).</p>
            
            <div class="details-box">
                <div class="row">
                    <span>Invoice:</span>
                    <span><strong>{{ $invoice->invoice_number }}</strong></span>
                </div>
                <div class="row">
                    <span>Due Date:</span>
                    <span><strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Immediate' }}</strong></span>
                </div>
                <div class="row">
                    <span>Outstanding Balance:</span>
                    <span>{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569;">
                If payment has already been sent, please disregard this reminder or contact us. Otherwise, click below to settle online via card, bank transfer, or PayPal.
            </p>

            <div class="btn-group">
                <a href="{{ $portalUrl }}" class="btn">Pay Invoice Online</a>
                <a href="{{ $pdfUrl }}" class="btn btn-secondary">Download PDF</a>
            </div>
        </div>
        <div class="footer">
            <p>Invoicy by RedMaple Automated Reminders &bull; {{ $invoice->workspace->email ?? 'support@redmapleagency.com' }}</p>
        </div>
    </div>
</body>
</html>
