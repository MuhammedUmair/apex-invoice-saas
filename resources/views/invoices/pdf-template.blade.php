<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
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
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .logo-text { font-size: 24px; font-weight: 800; color: #2563eb; }
        .inv-title { font-size: 24px; font-weight: 800; text-align: right; color: #0f172a; text-transform: uppercase; }
        .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .party-cell { width: 50%; vertical-align: top; }
        .label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .party-name { font-size: 15px; font-weight: 700; color: #0f172a; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-table { width: 45%; margin-left: auto; border-collapse: collapse; margin-bottom: 30px; }
        .totals-table td { padding: 6px 10px; }
        .total-row { font-size: 16px; font-weight: 800; border-top: 2px solid #e2e8f0; color: #2563eb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-due { background: #fee2e2; color: #991b1b; }
        .no-print { margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #dc2626; color: #ffffff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 900 900" fill="#ffffff" style="vertical-align: middle;">
                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
            </svg>
            Print / Save to PDF
        </button>
    </div>

    <!-- Header -->
    @php
        $pdfLogo = \App\Models\CmsContent::getSystemLogo();
    @endphp
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
                    <span class="logo-text" style="color: #0f172a;">{{ $invoice->workspace_snapshot['name'] ?? $invoice->workspace->name }}</span>
                </div>
                <div style="color: #64748b; font-size: 12px; margin-top: 4px;">{{ $invoice->workspace_snapshot['email'] ?? $invoice->workspace->email }}</div>
            </td>
            <td>
                <div class="inv-title">INVOICE</div>
                <div style="text-align: right; font-weight: bold; font-size: 14px; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
                <div style="text-align: right; color: #64748b; font-size: 12px;">Issue Date: {{ $invoice->issue_date->format('M d, Y') }}</div>
                <div style="text-align: right; color: #64748b; font-size: 12px;">Due Date: {{ $invoice->due_date->format('M d, Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Parties -->
    <table class="parties-table">
        <tr>
            <td class="party-cell">
                <div class="label">Billed By:</div>
                <div class="party-name">{{ $invoice->workspace_snapshot['name'] ?? $invoice->workspace->name }}</div>
                <div style="color: #475569; font-size: 12px; white-space: pre-line;">{{ $invoice->workspace_snapshot['address'] ?? $invoice->workspace->address }}</div>
                @if(!empty($invoice->workspace_snapshot['tax_id']))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">Tax ID: {{ $invoice->workspace_snapshot['tax_id'] }}</div>
                @endif
            </td>
            <td class="party-cell">
                <div class="label">Billed To:</div>
                <div class="party-name">{{ $invoice->client_snapshot['company_name'] ?? $invoice->client->company_name ?? $invoice->client->name }}</div>
                <div style="color: #475569; font-size: 12px;">Attn: {{ $invoice->client_snapshot['name'] ?? $invoice->client->name }}</div>
                <div style="color: #475569; font-size: 12px; white-space: pre-line;">{{ $invoice->client_snapshot['address'] ?? $invoice->client->address }}</div>
                @if(!empty($invoice->client_snapshot['tax_id']))
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">Tax/VAT ID: {{ $invoice->client_snapshot['tax_id'] }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center" style="width: 80px;">Qty</th>
                <th class="text-right" style="width: 100px;">Rate ({{ $invoice->currency }})</th>
                <th class="text-right" style="width: 110px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                    </td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td class="text-right">{{ $item->formatted_rate }}</td>
                    <td class="text-right"><strong>{{ $item->formatted_amount }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td style="color: #64748b;">Subtotal:</td>
            <td class="text-right"><strong>{{ $invoice->formatted_subtotal }}</strong></td>
        </tr>
        @if($invoice->discount_cents > 0)
            <tr>
                <td style="color: #16a34a;">Discount:</td>
                <td class="text-right" style="color: #16a34a;"><strong>-${{ number_format($invoice->discount_cents / 100, 2) }}</strong></td>
            </tr>
        @endif
        <tr>
            <td style="color: #64748b;">{{ $invoice->tax_name }} ({{ number_format($invoice->tax_rate, 2) }}%):</td>
            <td class="text-right"><strong>+{{ $invoice->formatted_tax }}</strong></td>
        </tr>
        <tr class="total-row">
            <td>Total:</td>
            <td class="text-right">{{ $invoice->formatted_total }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">Amount Paid:</td>
            <td class="text-right">${{ number_format($invoice->paid_cents / 100, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #dc2626; font-weight: bold;">Balance Due:</td>
            <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ $invoice->formatted_due }}</td>
        </tr>
    </table>

    <!-- Notes & Bank Info -->
    @if($invoice->terms)
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;">
            <strong style="color: #334155;">Terms & Conditions:</strong><br>
            {{ $invoice->terms }}
        </div>
    @endif

    <div style="text-align: center; margin-top: 40px; padding-top: 15px; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8;">
        Generated securely via <strong>RedMaple Invoice Generator</strong>
    </div>
</body>
</html>
