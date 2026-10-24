<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendor Alert: Payment Reminder Dispatched</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0f172a; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; opacity: 0.8; font-size: 13px; }
        .body { padding: 32px; }
        .badge { display: inline-block; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 9999px; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .row:last-child { margin-bottom: 0; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-weight: 700; font-size: 16px; color: #dc2626; }
        .btn { display: inline-block; padding: 12px 24px; background: #0f172a; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple &bull; Vendor Alert</h1>
            <p>{{ $invoice->workspace->name ?? 'RedMaple Agency' }}</p>
        </div>
        <div class="body">
            <span class="badge">Automatic Milestone: {{ $daysBefore }} Days Before Due</span>
            <h2 style="font-size: 18px; margin-top: 0; color: #0f172a;">Customer Reminder Dispatched</h2>
            <p>An automated payment reminder has been emailed to your client: <strong>{{ $invoice->client->name }}</strong> ({{ $invoice->client->email }}).</p>
            
            <div class="details-box">
                <div class="row">
                    <span>Invoice:</span>
                    <span><strong>{{ $invoice->invoice_number }}</strong></span>
                </div>
                <div class="row">
                    <span>Client / Company:</span>
                    <span>{{ $invoice->client->company_name ?: $invoice->client->name }}</span>
                </div>
                <div class="row">
                    <span>Due Date:</span>
                    <span>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }} (in {{ $daysBefore }} days)</span>
                </div>
                <div class="row">
                    <span>Balance Due:</span>
                    <span>{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <a href="{{ $adminUrl }}" class="btn">View Invoice in Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>Invoicy by RedMaple Automated Milestone Tracking</p>
        </div>
    </div>
</body>
</html>
