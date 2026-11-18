<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoicy by RedMaple - Live SMTP Test Successful</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            padding: 28px 32px;
            text-align: center;
        }
        .logo-wrap {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0;
            opacity: 0.95;
            font-size: 13px;
            font-weight: 500;
        }
        .body {
            padding: 32px;
        }
        .status-badge {
            display: inline-block;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .headline {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px;
        }
        .intro {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .details-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .label {
            color: #64748b;
            font-weight: 600;
        }
        .value {
            color: #0f172a;
            font-weight: 700;
            font-family: 'JetBrains Mono', Consolas, monospace;
        }
        .action-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.5;
        }
        .footer {
            padding: 20px 32px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-wrap">
                <!-- Red Maple Leaf Vector -->
                <svg width="40" height="40" viewBox="0 0 900 900" fill="#ffffff" style="display:block; margin:0 auto;">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            </div>
            <h1>Invoicy by RedMaple</h1>
            <p>{{ $workspaceName ?? 'Agency Outgoing Mail Server Test' }}</p>
        </div>

        <!-- Body -->
        <div class="body">
            <div style="text-align:center;">
                <span class="status-badge">&#10003; Live Connection Verified</span>
            </div>

            <h2 class="headline" style="text-align:center;">Live SMTP Connection Operating with 100% Success</h2>
            <p class="intro" style="text-align:center;">
                This test confirmation verifies that your agency's private outgoing mail server is properly connected, authenticated, and ready to dispatch commercial invoices and payment reminders to clients.
            </p>

            <div class="details-box">
                <div class="row">
                    <span class="label">SMTP Mail Host:</span>
                    <span class="value">{{ $smtpHost ?? 'smtp.yourdomain.com' }}</span>
                </div>
                <div class="row">
                    <span class="label">Port & Encryption:</span>
                    <span class="value">{{ $smtpPort ?? '465' }} / {{ strtoupper($smtpEncryption ?? 'SSL') }}</span>
                </div>
                <div class="row">
                    <span class="label">Authenticated User:</span>
                    <span class="value">{{ $smtpUsername ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span class="label">From Address:</span>
                    <span class="value">{{ $fromAddress ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span class="label">From Display Name:</span>
                    <span class="value" style="font-family: inherit;">{{ $fromName ?? 'Invoicy by RedMaple' }}</span>
                </div>
                <div class="row">
                    <span class="label">Test Timestamp:</span>
                    <span class="value" style="font-family: inherit;">{{ now()->format('M d, Y • h:i:s A T') }}</span>
                </div>
            </div>

            <div class="action-card">
                <strong>Next Steps:</strong> When your team issues invoices, clients will receive emails originating directly from this authenticated mailbox with SPF, DKIM, and deliverability protection.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Sent via <strong>Invoicy by RedMaple</strong> Invoicing Studio<br>
                Commercial Multi-Tenant Billing Platform &bull; Toronto, Canada
            </p>
        </div>
    </div>
</body>
</html>
