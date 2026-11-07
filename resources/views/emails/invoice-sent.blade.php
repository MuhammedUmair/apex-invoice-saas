@php
    $locale = $locale ?? app()->getLocale();
    $isRtl = $isRtl ?? in_array($locale, ['ur', 'ar']);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Invoice') }} #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; {{ $isRtl ? 'direction: rtl; text-align: right;' : 'direction: ltr; text-align: left;' }} }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #dc2626; color: #ffffff; padding: 24px 32px; {{ $isRtl ? 'text-align: right;' : 'text-align: left;' }} }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; opacity: 0.9; font-size: 13px; }
        .body { padding: 32px; {{ $isRtl ? 'text-align: right;' : 'text-align: left;' }} }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .row:last-child { margin-bottom: 0; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-weight: 700; font-size: 16px; color: #dc2626; }
        .btn-group { margin-top: 28px; text-align: center; }
        .btn { display: inline-block; padding: 12px 28px; background: #dc2626; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; margin: 4px; }
        .btn-secondary { background: #f1f5f9; color: #334155 !important; border: 1px solid #cbd5e1; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple</h1>
            <p>{{ $invoice->workspace?->name ?? $invoice->workspace_snapshot['name'] ?? 'RedMaple Agency' }}</p>
        </div>
        <div class="body">
            <h2 style="font-size: 18px; margin-top: 0; color: #0f172a;">{{ __('New Invoice Issued') }}</h2>
            <p>{{ __('Dear') }} {{ $invoice->client?->name ?? $invoice->client_snapshot['name'] ?? __('Walk-in / General Customer') }},</p>
            <p>{{ __('A new invoice has been generated for your account. Please review the summary below:') }}</p>
            
            <div class="details-box">
                <div class="row">
                    <span>{{ __('Invoice Number') }}:</span>
                    <span><strong>{{ $invoice->invoice_number }}</strong></span>
                </div>
                <div class="row">
                    <span>{{ __('Issue Date') }}:</span>
                    <span>{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : date('M d, Y') }}</span>
                </div>
                <div class="row">
                    <span>{{ __('Payment Due Date') }}:</span>
                    <span><strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : __('Due on receipt') }}</strong></span>
                </div>
                <div class="row">
                    <span>{{ __('Total Amount Due') }}:</span>
                    <span>{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>

            @if($invoice->notes)
                <p style="font-size: 13px; color: #475569; background: #fef2f2; {{ $isRtl ? 'border-right: 3px solid #dc2626; border-left: none; border-radius: 8px 0 0 8px;' : 'border-left: 3px solid #dc2626; border-right: none; border-radius: 0 8px 8px 0;' }} padding: 10px 14px;">
                    <strong>{{ __('Notes') }}:</strong> {{ $invoice->notes }}
                </p>
            @endif

            <div class="btn-group">
                <a href="{{ $portalUrl }}" class="btn">{{ __('View & Pay Online') }}</a>
                <a href="{{ $pdfUrl }}" class="btn btn-secondary">{{ __('Download PDF') }}</a>
            </div>

            @if($invoice->isTrialInvoice())
                <div style="margin-top: 24px; padding: 12px; background: #fffbeb; border: 1px dashed #f59e0b; border-radius: 8px; font-size: 11px; color: #92400e; line-height: 1.4;">
                    <strong>🛡️ {{ __('Verification & Security Notice') }}:</strong> {{ __('This invoice was dispatched from an evaluation Free Trial account on Invoicy SaaS. Please verify sender authenticity and remittance bank details independently before submitting funds.') }}
                </div>
            @endif
        </div>
        <div class="footer">
            <p>{{ __('Sent securely via Invoicy by RedMaple') }} &bull; {{ $invoice->workspace->email ?? 'support@redmapleagency.com' }}</p>
        </div>
    </div>
    <!-- 1x1 Transparent Email Open Tracking Beacon -->
    <img src="{{ route('invoices.track.open', $invoice->public_secure_token) }}" width="1" height="1" alt="" style="display:none; width:1px; height:1px; border:0;" />
</body>
</html>
