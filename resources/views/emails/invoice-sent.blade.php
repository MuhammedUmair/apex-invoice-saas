<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #dc2626; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; opacity: 0.9; font-size: 13px; }
        .body { padding: 32px; }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .row:last-child { margin-bottom: 0; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-weight: 700; font-size: 16px; color: #dc2626; }
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
            <h2 style="font-size: 18px; margin-top: 0; color: #0f172a;">New Invoice Issued</h2>
            <p>Dear {{ $invoice->client->name }},</p>
            <p>A new invoice has been generated for your account. Please review the summary below:</p>
            
            <div class="details-box">
                <div class="row">
                    <span>Invoice Number:</span>
                    <span><strong>{{ $invoice->invoice_number }}</strong></span>
                </div>
                <div class="row">
                    <span>Issue Date:</span>
                    <span>{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : date('M d, Y') }}</span>
                </div>
                <div class="row">
                    <span>Payment Due Date:</span>
                    <span><strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</strong></span>
                </div>
                <div class="row">
                    <span>Total Amount Due:</span>
                    <span>{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>

            @if($invoice->notes)
                <p style="font-size: 13px; color: #475569; background: #fef2f2; border-left: 3px solid #dc2626; padding: 10px 14px; border-radius: 0 8px 8px 0;">
                    <strong>Note:</strong> {{ $invoice->notes }}
                </p>
            @endif

            <div class="btn-group">
                <a href="{{ $portalUrl }}" class="btn">View & Pay Online</a>
                <a href="{{ $pdfUrl }}" class="btn btn-secondary">Download PDF</a>
            </div>
        </div>
        <div class="footer">
            <p>Sent securely via Invoicy by RedMaple &bull; {{ $invoice->workspace->email ?? 'support@redmapleagency.com' }}</p>
        </div>
    </div>
</body>
</html>
