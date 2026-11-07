<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Team Member Invitation | Invoicy by RedMaple</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); }
        .header { background: #0f172a; color: #ffffff; padding: 28px 32px; border-bottom: 3px solid #dc2626; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; color: #f8fafc; }
        .header p { margin: 4px 0 0; color: #f87171; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .body { padding: 36px 32px; }
        .badge { display: inline-block; padding: 4px 10px; background: #e0e7ff; color: #3730a3; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin: 20px 0; }
        .details-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .details-row:last-child { border-bottom: none; }
        .details-label { color: #64748b; font-weight: 500; }
        .details-value { color: #0f172a; font-weight: 700; }
        .cta-button { display: inline-block; background: #dc2626; color: #ffffff !important; padding: 14px 28px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 14px; text-align: center; margin: 16px 0; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); }
        .meta { font-size: 12px; color: #64748b; line-height: 1.6; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple</h1>
            <p>Team Member Invitation &bull; Workspace Access</p>
        </div>
        <div class="body">
            <span class="badge">Team Invitation</span>
            <h2 style="font-size: 20px; margin: 0 0 12px; color: #0f172a; font-weight: 800;">
                Welcome to {{ $workspace->name }}!
            </h2>
            <p style="font-size: 14px; color: #334155; line-height: 1.6; margin: 0 0 16px;">
                Hello <strong>{{ $user->name }}</strong>,
            </p>
            <p style="font-size: 14px; color: #334155; line-height: 1.6; margin: 0 0 20px;">
                <strong>{{ $inviter->name }}</strong> has invited you to join the <strong>{{ $workspace->name }}</strong> agency workspace on Invoicy as a <strong>{{ ucfirst($user->role) }}</strong> team member.
            </p>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ $verificationUrl }}" class="cta-button" target="_blank">
                    Verify Email &amp; Activate Account &rarr;
                </a>
                <div style="font-size: 11px; color: #64748b; margin-top: 8px;">
                    This activation link is valid for 7 days.
                </div>
            </div>

            <div class="details-box">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 8px; letter-spacing: 0.5px;">
                    Your Account Details
                </div>
                <div class="details-row">
                    <span class="details-label">Agency Workspace:</span>
                    <span class="details-value">{{ $workspace->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Login Email:</span>
                    <span class="details-value">{{ $user->email }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Role Privileges:</span>
                    <span class="details-value">{{ ucfirst($user->role) }}</span>
                </div>
                @if(!empty($initialPassword))
                <div class="details-row">
                    <span class="details-label">Temporary Password:</span>
                    <span class="details-value" style="font-family: monospace; color: #b91c1c;">{{ $initialPassword }}</span>
                </div>
                @endif
            </div>

            <p class="meta">
                <strong>Important:</strong> Email verification is required before you can access the agency workspace. If you did not expect this invitation, you may ignore this email.
            </p>
        </div>
        <div class="footer">
            Invoicy by RedMaple &bull; Automated Workspace Management &bull; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
