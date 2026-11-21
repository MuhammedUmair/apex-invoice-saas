@php
    $currentLocale = app()->getLocale();
    $isRtl = in_array($currentLocale, ['ur', 'ar']);
    $pdfLogo = \App\Models\CmsContent::getSystemLogo();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Invoice') }} - {{ $invoice->invoice_number }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 30px;
            font-size: 13px;
            line-height: 1.5;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .logo-text { font-size: 24px; font-weight: 800; color: #dc2626; }
        .inv-title { font-size: 24px; font-weight: 800; text-align: {{ $isRtl ? 'left' : 'right' }}; color: #0f172a; text-transform: uppercase; }
        .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .party-cell { width: 50%; vertical-align: top; }
        .label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .party-name { font-size: 15px; font-weight: 700; color: #0f172a; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #e2e8f0; text-align: {{ $isRtl ? 'right' : 'left' }}; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: {{ $isRtl ? 'left' : 'right' }}; }
        .text-center { text-align: center; }
        .totals-table { width: 45%; {{ $isRtl ? 'margin-right: auto; margin-left: 0;' : 'margin-left: auto; margin-right: 0;' }} border-collapse: collapse; margin-bottom: 30px; }
        .totals-table td { padding: 6px 10px; }
        .total-row { font-size: 16px; font-weight: 800; border-top: 2px solid #e2e8f0; color: #dc2626; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-due { background: #fee2e2; color: #991b1b; }
        .no-print { margin-bottom: 20px; text-align: {{ $isRtl ? 'left' : 'right' }}; }
        .trial-watermark {
            position: fixed;
            top: 36%;
            left: -15%;
            width: 130%;
            text-align: center;
            transform: rotate(-32deg);
            font-size: 44px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.20);
            border-top: 4px dashed rgba(220, 38, 38, 0.25);
            border-bottom: 4px dashed rgba(220, 38, 38, 0.25);
            padding: 20px 0;
            text-transform: uppercase;
            letter-spacing: 6px;
            z-index: 9999;
            pointer-events: none;
            user-select: none;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    @if($invoice->isTrialInvoice())
        <div class="trial-watermark">
            {{ __('TRIAL VERSION • NOT FOR OFFICIAL ACCOUNTING') }}
        </div>
    @endif
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 18px; background: #dc2626; color: #ffffff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 900 900" fill="#ffffff" style="vertical-align: middle;">
                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
            </svg>
            {{ __('Print / Save to PDF') }}
        </button>
    </div>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    @if($pdfLogo)
                        <img src="{{ $pdfLogo }}" alt="Logo" style="max-height: 36px; max-width: 150px; vertical-align: middle; margin-right: 8px;">
                    @else
                        <svg width="26" height="26" viewBox="0 0 900 900" fill="#dc2626" style="vertical-align: middle; margin-right: 6px; display: inline-block;">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    @endif
                    <span class="logo-text" style="color: #0f172a;">{{ $invoice->workspace_snapshot['name'] ?? $invoice->workspace?->name ?? 'Invoicy' }}</span>
                </div>
                <div style="color: #64748b; font-size: 12px; margin-top: 4px;">{{ $invoice->workspace_snapshot['email'] ?? $invoice->workspace?->email ?? '' }}</div>
            </td>
            <td>
                <div class="inv-title">{{ __('INVOICE') }}</div>
                <div style="text-align: {{ $isRtl ? 'left' : 'right' }}; font-weight: bold; font-size: 14px; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
                <div style="text-align: {{ $isRtl ? 'left' : 'right' }}; color: #64748b; font-size: 12px;">{{ __('Issue Date') }}: {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : date('M d, Y') }}</div>
                <div style="text-align: {{ $isRtl ? 'left' : 'right' }}; color: #64748b; font-size: 12px;">{{ __('Due Date') }}: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : date('M d, Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Parties -->
    <table class="parties-table">
        <tr>
            <td class="party-cell">
                <div class="label">{{ __('Billed By') }}:</div>
                <div class="party-name">{{ $invoice->workspace_snapshot['name'] ?? $invoice->workspace?->name ?? 'Agency' }}</div>
                <div style="color: #475569; font-size: 12px; white-space: pre-line;">{{ $invoice->workspace_snapshot['address'] ?? $invoice->workspace?->address ?? '' }}</div>
                @if(!empty($invoice->workspace_snapshot['tax_id']) || !empty($invoice->workspace?->tax_id))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ __('Tax ID') }}: {{ $invoice->workspace_snapshot['tax_id'] ?? $invoice->workspace?->tax_id }}</div>
                @endif
                @php
                    $craBn = $invoice->workspace_snapshot['cra_bn_number'] ?? $invoice->workspace?->cra_bn_number;
                    $qstNum = $invoice->workspace_snapshot['qst_number'] ?? $invoice->workspace?->qst_number;
                    $clientProv = $invoice->client_snapshot['province'] ?? $invoice->client_snapshot['state'] ?? $invoice->client?->province ?? $invoice->client?->state;
                    $clientCountry = $invoice->client_snapshot['country_code'] ?? $invoice->client?->country_code;
                    $clientCity = $invoice->client_snapshot['city'] ?? $invoice->client?->city;
                    $locParts = array_filter([$clientCity, $clientProv, $clientCountry]);
                @endphp
                @if(!empty($craBn))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;"><strong>CRA Business No:</strong> <span style="font-family: monospace;">{{ $craBn }}</span></div>
                @endif
                @if(!empty($qstNum) && ($invoice->provincial_tax_name === 'QST' || $clientProv === 'QC' || $invoice->workspace?->country_code === 'CA'))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;"><strong>Revenu Québec QST:</strong> <span style="font-family: monospace;">{{ $qstNum }}</span></div>
                @endif
            </td>
            <td class="party-cell">
                <div class="label">{{ __('Billed To') }}:</div>
                <div class="party-name">{{ $invoice->client_snapshot['company_name'] ?? $invoice->client?->company_name ?? $invoice->client?->name ?? __('Walk-in / General Customer') }}</div>
                <div style="color: #475569; font-size: 12px;">{{ __('Attn') }}: {{ $invoice->client_snapshot['name'] ?? $invoice->client?->name ?? __('Walk-in / General Customer') }}</div>
                @if(!empty($invoice->client_snapshot['address']))
                    <div style="color: #475569; font-size: 12px; white-space: pre-line;">{{ $invoice->client_snapshot['address'] }}</div>
                @endif
                @if(!empty($locParts))
                    <div style="color: #1e293b; font-weight: 600; font-size: 12px; margin-top: 2px;">{{ implode(', ', $locParts) }}</div>
                @endif
                @if(!empty($invoice->client_snapshot['tax_id']) || !empty($invoice->client?->tax_id))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ __('VAT/Tax ID') }}: {{ $invoice->client_snapshot['tax_id'] ?? $invoice->client?->tax_id }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th class="text-center" style="width: 80px;">{{ __('Qty') }}</th>
                <th class="text-right" style="width: 120px;">{{ __('Rate') }} ({{ $invoice->currency }})</th>
                <th class="text-right" style="width: 120px;">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                    </td>
                    <td class="text-center">{{ (float)$item->quantity == (int)$item->quantity ? (int)$item->quantity : $item->quantity }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->rate_cents / 100, 2) }} {{ $invoice->currency }}</td>
                    <td class="text-right"><strong>{{ number_format($item->amount_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td style="color: #64748b;">{{ __('Subtotal') }}:</td>
            <td class="text-right"><strong>{{ number_format($invoice->subtotal_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
        </tr>
        @if($invoice->discount_cents > 0)
            <tr>
                <td style="color: #16a34a;">{{ __('Discount') }}:</td>
                <td class="text-right" style="color: #16a34a;"><strong>-{{ number_format($invoice->discount_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
            </tr>
        @endif
        @if($invoice->is_dual_tax)
            <tr>
                <td style="color: #64748b;">{{ $invoice->federal_tax_name ?: 'GST' }} ({{ number_format($invoice->federal_tax_rate, 3) }}%):</td>
                <td class="text-right"><strong>+{{ number_format($invoice->federal_tax_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
            </tr>
            <tr>
                <td style="color: #64748b;">{{ $invoice->provincial_tax_name ?: 'QST/PST' }} ({{ number_format($invoice->provincial_tax_rate, 3) }}%):</td>
                <td class="text-right"><strong>+{{ number_format($invoice->provincial_tax_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
            </tr>
        @else
            <tr>
                <td style="color: #64748b;">{{ $invoice->tax_name }} ({{ number_format($invoice->tax_rate, 2) }}%):</td>
                <td class="text-right"><strong>+{{ number_format($invoice->tax_cents / 100, 2) }} {{ $invoice->currency }}</strong></td>
            </tr>
        @endif
        <tr class="total-row">
            <td>{{ __('Total') }}:</td>
            <td class="text-right">{{ number_format($invoice->total_cents / 100, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">{{ __('Amount Paid') }}:</td>
            <td class="text-right">{{ number_format($invoice->paid_cents / 100, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td style="color: #dc2626; font-weight: bold;">{{ __('Balance Due') }}:</td>
            <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ number_format($invoice->due_cents / 100, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @if($invoice->getLateSurchargeCents() > 0)
            <tr>
                <td style="color: #b91c1c;">{{ __('Late Surcharge (Overdue)') }}:</td>
                <td class="text-right" style="color: #b91c1c;"><strong>+{{ number_format($invoice->getLateSurchargeCents() / 100, 2) }} {{ $invoice->currency }}</strong></td>
            </tr>
            <tr class="total-row" style="color: #b91c1c;">
                <td>{{ __('Adjusted Total Due') }}:</td>
                <td class="text-right">{{ number_format(($invoice->due_cents + $invoice->getLateSurchargeCents()) / 100, 2) }} {{ $invoice->currency }}</td>
            </tr>
        @endif
    </table>

    <!-- Notes & Bank Info -->
    @if($invoice->notes)
        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;">
            <strong style="color: #334155;">{{ __('Notes') }}:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    @if($invoice->terms)
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;">
            <strong style="color: #334155;">{{ __('Terms & Conditions') }}:</strong><br>
            {{ $invoice->terms }}
        </div>
    @endif

    @php
        $ws = $invoice->workspace;
        $bankInst = $ws?->bank_transfer_instructions ?? ($invoice->workspace_snapshot['bank_transfer_instructions'] ?? null);
        $bankDetails = $invoice->workspace_snapshot['bank_details'] ?? ($ws?->bank_details ?? []);
    @endphp

    @if(!empty($bankInst) || (!empty($bankDetails['bank_name']) && !empty($bankDetails['account_number'])))
        <div style="margin-top: 15px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; color: #475569;">
            <strong style="color: #0f172a; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">{{ __('Remittance / Payment Instructions') }}:</strong><br>
            @if(!empty($bankInst))
                <div style="margin-top: 4px; white-space: pre-line;">{{ $bankInst }}</div>
            @endif
            @if(!empty($bankDetails['bank_name']) && !empty($bankDetails['account_number']))
                <div style="margin-top: 4px;">
                    <strong>{{ __('Bank') }}:</strong> {{ $bankDetails['bank_name'] }} |
                    <strong>{{ __('Account') }}:</strong> {{ $bankDetails['account_number'] }}
                    @if(!empty($bankDetails['routing_number'])) | <strong>{{ __('Transit/Routing') }}:</strong> {{ $bankDetails['routing_number'] }} @endif
                    @if(!empty($bankDetails['swift'])) | <strong>{{ __('SWIFT/BIC') }}:</strong> {{ $bankDetails['swift'] }} @endif
                </div>
            @endif
            <div style="margin-top: 4px; color: #b91c1c; font-size: 10px; font-weight: bold;">
                {{ __('Please include Invoice #') }}{{ $invoice->invoice_number }} {{ __('in transfer details.') }}
            </div>
        </div>
    @endif

    <!-- Authentic Verification & QR Code -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 25px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <tr>
            <td style="width: 105px; vertical-align: middle; text-align: center; padding-right: 15px;">
                <img src="{{ $invoice->qr_code_data_uri }}" alt="Verification QR Code" style="width: 90px; height: 90px; border: 1px solid #cbd5e1; padding: 4px; background: #ffffff; border-radius: 6px; display: block; margin: 0 auto;">
            </td>
            <td style="vertical-align: middle;">
                <div style="font-size: 11px; font-weight: 800; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">
                    &#128737; {{ __('OFFICIAL DIGITAL AUTHENTICITY SEAL') }}
                </div>
                <div style="font-size: 11px; color: #334155; line-height: 1.4;">
                    {{ __('Scan this QR code with any mobile camera or QR reader to verify the authenticity, issuer credentials, and uncompromised status of this commercial invoice.') }}
                </div>
                <div style="margin-top: 5px; font-size: 10px; font-family: monospace; color: #64748b;">
                    {{ __('Cryptographic Fingerprint') }}: <strong style="color: #0f172a;">{{ $invoice->cryptographic_fingerprint }}</strong> &bull; {{ __('Status') }}: <span style="font-weight: bold; color: #166534; text-transform: uppercase;">{{ $invoice->status }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 40px; padding-top: 15px; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8;">
        {{ __('Generated securely via') }} <strong>Invoicy by RedMaple</strong>
    </div>
</body>
</html>
