<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction Notice: Invoice #{{ $invoice->invoice_number }} Recalled</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #b45309 0%, #78350f 100%); color: #ffffff; padding: 28px 32px; text-align: center; }
        .badge { display: inline-block; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 5px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
        .body { padding: 32px; }
        .headline { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
        .intro { font-size: 14px; color: #475569; line-height: 1.6; margin: 0 0 24px; }
        .box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px dashed #fde68a; }
        .row:last-child { border-bottom: none; }
        .label { color: #92400e; font-weight: 600; }
        .value { color: #451a03; font-weight: 700; }
        .notice-box { background: #f8fafc; border-left: 4px solid #f59e0b; padding: 14px; font-size: 13px; color: #475569; border-radius: 4px; margin-bottom: 20px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 32px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 28px; margin-bottom: 8px;">↩️</div>
            <h1 style="margin: 0; font-size: 22px; font-weight: 900;">Document Correction Notice</h1>
            <p style="margin: 6px 0 0; opacity: 0.9; font-size: 13px;">{{ $workspace?->name ?? 'Invoicy by RedMaple' }}</p>
        </div>

        <div class="body">
            <span class="badge">Invoice Retracted</span>
            <div class="headline">Notice: Invoice #{{ $invoice->invoice_number }} Recalled</div>
            <p class="intro">
                Please be advised that <strong>{{ $workspace?->name ?? 'the billing agency' }}</strong> has recalled and pulled back <strong>Invoice #{{ $invoice->invoice_number }}</strong> to make adjustments and corrections.
            </p>

            <div class="notice-box">
                <strong>Reason for Recall:</strong><br>
                {{ $reason }}
            </div>

            <div class="box">
                <div class="row">
                    <span class="label">Invoice Reference</span>
                    <span class="value">#{{ $invoice->invoice_number }}</span>
                </div>
                <div class="row">
                    <span class="label">Recalled By</span>
                    <span class="value">{{ $workspace?->name }}</span>
                </div>
                <div class="row">
                    <span class="label">Previous Online Link</span>
                    <span class="value" style="color: #dc2626;">Invalidated / Revoked</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #64748b; line-height: 1.6;">
                Any previous links to this invoice have been deactivated. An updated and corrected invoice will be issued to your email shortly. We apologize for any inconvenience.
            </p>
        </div>

        <div class="footer">
            Sent on behalf of {{ $workspace?->name ?? 'Invoicy' }} via <strong>Invoicy by RedMaple</strong>
        </div>
    </div>
</body>
</html>
