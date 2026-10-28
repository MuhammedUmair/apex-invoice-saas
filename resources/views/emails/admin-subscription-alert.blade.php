<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $alert->title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0f172a; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; opacity: 0.8; font-size: 13px; }
        .body { padding: 32px; }
        .badge { display: inline-block; padding: 5px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; }
        .badge-signup { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-upgrade { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-downgrade { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-trial_expired { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-default { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; }
        .row:last-child { margin-bottom: 0; }
        .row-label { color: #64748b; font-weight: 500; }
        .row-val { color: #0f172a; font-weight: 700; text-align: right; }
        .btn { display: inline-block; padding: 12px 24px; background: #dc2626; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 13px; shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoicy by RedMaple &bull; Super Admin Alert</h1>
            <p>Platform Subscription & Tenant Lifecycle Event</p>
        </div>
        <div class="body">
            @php
                $badgeClass = match($alert->type) {
                    'signup' => 'badge-signup',
                    'upgrade' => 'badge-upgrade',
                    'downgrade' => 'badge-downgrade',
                    'trial_expired' => 'badge-trial_expired',
                    default => 'badge-default',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">Event: {{ strtoupper(str_replace('_', ' ', $alert->type)) }}</span>
            <h2 style="font-size: 18px; margin-top: 0; color: #0f172a;">{{ $alert->title }}</h2>
            <p style="font-size: 14px; line-height: 1.5; color: #475569;">{{ $alert->message }}</p>
            
            <div class="details-box">
                <div class="row">
                    <span class="row-label">Agency / Workspace:</span>
                    <span class="row-val">{{ $workspace->name ?? ($metadata['workspace_name'] ?? 'N/A') }}</span>
                </div>
                <div class="row">
                    <span class="row-label">Tenant Slug:</span>
                    <span class="row-val font-mono">{{ $workspace->slug ?? ($metadata['workspace_slug'] ?? 'N/A') }}</span>
                </div>
                @if($user)
                    <div class="row">
                        <span class="row-label">Owner Name:</span>
                        <span class="row-val">{{ $user->name }}</span>
                    </div>
                    <div class="row">
                        <span class="row-label">Owner Email:</span>
                        <span class="row-val">{{ $user->email }}</span>
                    </div>
                @endif
                @if(isset($metadata['plan_name']))
                    <div class="row">
                        <span class="row-label">Active Plan:</span>
                        <span class="row-val">{{ $metadata['plan_name'] }}</span>
                    </div>
                @endif
                @if(isset($metadata['old_plan_name']))
                    <div class="row">
                        <span class="row-label">Previous Plan:</span>
                        <span class="row-val">{{ $metadata['old_plan_name'] }}</span>
                    </div>
                @endif
                @if(isset($metadata['price_formatted']))
                    <div class="row">
                        <span class="row-label">Plan Price:</span>
                        <span class="row-val" style="color: #047857;">{{ $metadata['price_formatted'] }}</span>
                    </div>
                @endif
                @if(isset($metadata['gateway']))
                    <div class="row">
                        <span class="row-label">Payment Gateway:</span>
                        <span class="row-val">{{ strtoupper($metadata['gateway']) }}</span>
                    </div>
                @endif
                @if(isset($metadata['trial_ends_at']))
                    <div class="row">
                        <span class="row-label">Trial Expiration:</span>
                        <span class="row-val">{{ $metadata['trial_ends_at'] }}</span>
                    </div>
                @endif
                <div class="row">
                    <span class="row-label">Timestamp:</span>
                    <span class="row-val">{{ $alert->created_at->format('M d, Y • h:i A') }} (UTC)</span>
                </div>
            </div>

            <div style="margin-top: 24px; text-align: center;">
                <a href="{{ $adminUrl }}" class="btn">Open Super Admin Subscriptions Panel &rarr;</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} RedMaple Invoicy Suite &bull; System Automated Notifications</p>
            <p>You received this alert because your email is configured as the Super Admin recipient for subscription notifications.</p>
        </div>
    </div>
</body>
</html>
