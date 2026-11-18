<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Viewed Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 28px 32px; text-align: center; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; padding: 5px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
        .body { padding: 32px; }
        .headline { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
        .intro { font-size: 14px; color: #475569; line-height: 1.6; margin: 0 0 24px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px dashed #e2e8f0; }
        .row:last-child { border-bottom: none; }
        .label { color: #64748b; font-weight: 600; }
        .value { color: #0f172a; font-weight: 700; }
        .btn { display: inline-block; background: #dc2626; color: #ffffff; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 10px; text-decoration: none; text-align: center; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 32px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 28px; margin-bottom: 8px;">👁️</div>
            <h1 style="margin: 0; font-size: 22px; font-weight: 900;">Invoice Engagement Alert</h1>
            <p style="margin: 6px 0 0; opacity: 0.9; font-size: 13px;">Invoicy Real-Time Document Tracking</p>
        </div>

        <div class="body">
            <span class="badge">Live Notification</span>
            <div class="headline">Your client just opened Invoice #{{ $invoice->invoice_number }}</div>
            <p class="intro">
                Great news! <strong>{{ $clientName }}</strong> @if($clientCompany) ({{ $clientCompany }}) @endif has just opened and viewed your invoice through the {{ $channel === 'email' ? 'email delivery link' : 'customer web portal' }}.
            </p>

            <div class="box">
                <div class="row">
                    <span class="label">Invoice Number</span>
                    <span class="value">#{{ $invoice->invoice_number }}</span>
                </div>
                <div class="row">
                    <span class="label">Invoice Amount</span>
                    <span class="value" style="color: #dc2626;">{{ number_format($invoice->total_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="row">
                    <span class="label">Recipient Client</span>
                    <span class="value">{{ $clientName }}</span>
                </div>
                <div class="row">
                    <span class="label">Time Viewed</span>
                    <span class="value">{{ $timestamp }}</span>
                </div>
                <div class="row">
                    <span class="label">Access Method</span>
                    <span class="value" style="text-transform: capitalize;">{{ $channel }} Portal</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ $invoiceUrl }}" class="btn" style="color: #ffffff;">View Invoice in Invoicy</a>
            </div>
        </div>

        <div class="footer">
            Automated engagement alert from <strong>Invoicy by RedMaple</strong> &bull; Commercial Invoicing Suite
        </div>
    </div>
</body>
</html>
