<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoicy Control Panel - One-Time Login Code</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); }
        .header { background: #0f172a; color: #ffffff; padding: 28px 32px; border-bottom: 2px solid #f59e0b; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; color: #f8fafc; }
        .header p { margin: 4px 0 0; color: #f59e0b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .body { padding: 36px 32px; }
        .otp-box { background: #fef3c7; border: 2px dashed #f59e0b; border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center; }
        .otp-code { font-family: 'Courier New', Courier, monospace; font-size: 38px; font-weight: 900; letter-spacing: 8px; color: #78350f; }
        .meta { font-size: 12px; color: #64748b; line-height: 1.6; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8; }
        .badge { display: inline-block; padding: 4px 10px; background: #fee2e2; color: #991b1b; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple</h1>
            <p>Super Admin Control Panel &bull; Two-Factor Authentication</p>
        </div>
        <div class="body">
            <span class="badge">Security Verification</span>
            <h2 style="font-size: 18px; margin: 0 0 12px; color: #0f172a;">One-Time Login Code</h2>
            <p style="font-size: 14px; color: #334155; line-height: 1.5; margin: 0 0 16px;">
                Hello <strong>{{ $user->name ?? 'Super Administrator' }}</strong>,
            </p>
            <p style="font-size: 14px; color: #334155; line-height: 1.5; margin: 0;">
                A sign-in request to the Invoicy Control Panel was initiated for your administrator account. Use the following 6-character alphanumeric security passcode to complete authentication:
            </p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div style="font-size: 11px; color: #92400e; font-weight: 600; margin-top: 8px;">
                    Valid for 10 minutes &bull; Do not share with anyone
                </div>
            </div>

            <p class="meta">
                <strong>Important:</strong> If you did not initiate this login request, your root account password may be compromised. Please sign in immediately and change your password or contact platform support at <em>support@redmapleagency.com</em>.
            </p>
        </div>
        <div class="footer">
            Invoicy Platform Administration &bull; Automated Security Service &bull; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
