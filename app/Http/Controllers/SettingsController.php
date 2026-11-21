<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            return redirect()->route('dashboard')->with('error', 'Access Denied: Staff accounts do not have permission to access Agency Settings.');
        }

        $workspace = $user->workspace;
        $teamMembers = $workspace ? $workspace->users()->orderBy('created_at', 'asc')->get() : collect([$user]);
        $plan = $workspace?->currentSubscription?->plan;
        $seatLimit = $plan ? (int)$plan->team_seats : 3;
        if ($seatLimit < 3) {
            $seatLimit = 3;
        }

        return view('settings.index', [
            'workspace' => $workspace,
            'user' => $user,
            'teamMembers' => $teamMembers,
            'seatLimit' => $seatLimit,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            return redirect()->route('dashboard')->with('error', 'Access Denied: Staff accounts do not have permission to modify Agency Settings.');
        }

        $workspace = $user->workspace;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:100',
            'cra_bn_number' => 'nullable|string|max:30',
            'qst_number' => 'nullable|string|max:30',
            'small_supplier_exempt' => 'nullable|boolean',
            'collect_qst' => 'nullable|boolean',
            'collect_pst' => 'nullable|boolean',
            'country_code' => 'required|string|size:2',
            'province' => 'nullable|string|max:50',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|max:10',
            'address' => 'nullable|string',
            'brand_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'logo_url' => 'nullable|url|max:500',
            'remove_logo' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:100',
            'swift' => 'nullable|string|max:50',
            'bank_transfer_instructions' => 'nullable|string|max:3000',
            'enable_paypal' => 'nullable|boolean',
            'enable_bank_transfer' => 'nullable|boolean',
            'stripe_publishable_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'paypal_client_id' => 'nullable|string',
            'paypal_client_secret' => 'nullable|string',
            'paypal_webhook_id' => 'nullable|string',
            'is_whitelabel_enabled' => 'nullable|boolean',
        ]);

        $workspace->name = $validated['name'];
        $workspace->email = $validated['email'] ?? null;
        $workspace->phone = $validated['phone'] ?? null;
        $workspace->website = $validated['website'] ?? null;
        $workspace->tax_id = $validated['tax_id'] ?? null;
        $workspace->country_code = $validated['country_code'];
        $workspace->currency = $validated['currency'];
        $workspace->language = $validated['language'];
        $workspace->address = $validated['address'] ?? null;
        $workspace->brand_color = $validated['brand_color'];
        $workspace->secondary_color = $validated['secondary_color'];
        $workspace->is_whitelabel_enabled = (bool)($request->has('is_whitelabel_enabled'));

        // Strict Regional Tax Isolation: Scoped to Canadian workspaces only
        if (strtoupper($validated['country_code']) === 'CA') {
            $workspace->province = $request->input('province') ? strtoupper(trim($request->input('province'))) : 'ON';
            $workspace->small_supplier_exempt = (bool) $request->has('small_supplier_exempt');
            $workspace->collect_qst = (bool) $request->has('collect_qst');
            $workspace->collect_pst = (bool) $request->has('collect_pst');

            $details = \App\Services\TaxCalculationService::getCanadianProvinceDetails(
                $workspace->province,
                $workspace->collect_qst,
                $workspace->collect_pst
            );
            $workspace->default_tax_rate = $workspace->small_supplier_exempt
                ? 0.000
                : (float) ($request->input('default_tax_rate') ?? $details['total_rate']);
            $workspace->cra_bn_number = $request->input('cra_bn_number') ?: null;
            $workspace->qst_number = $request->input('qst_number') ?: null;
        } else {
            // Non-Canadian workspaces are strictly isolated from Canadian multi-tax logic
            $workspace->province = null;
            $workspace->default_tax_rate = (float) ($request->input('default_tax_rate') ?? 0.00);
            $workspace->cra_bn_number = null;
            $workspace->qst_number = null;
            $workspace->small_supplier_exempt = false;
            $workspace->collect_qst = false;
            $workspace->collect_pst = false;
        }

        // Handle Custom Logo Upload or Removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $workspace->logo_url = null;
        } elseif ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = strtolower($file->getClientOriginalExtension());
            $realMime = $file->getMimeType();

            // Validate against allowed mime types
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/svg'];
            if (!in_array($realMime, $allowedMimes)) {
                return back()->withErrors(['logo' => 'Invalid image format. Allowed formats: PNG, JPG, WEBP, SVG.']);
            }

            // High Security SVG Sanitization
            if ($ext === 'svg' || str_contains($realMime, 'svg')) {
                $svgContent = file_get_contents($file->getRealPath());
                $dangerousPatterns = [
                    '/<script\b[^>]*>(.*?)<\/script>/is',
                    '/\bon\w+\s*=\s*["\'][^"\']*["\']/i',
                    '/\bon\w+\s*=\s*[^>\s]+/i',
                    '/href\s*=\s*["\']\s*javascript:[^"\']*["\']/i',
                    '/<foreignObject\b[^>]*>(.*?)<\/foreignObject>/is',
                    '/<animate\b[^>]*>/is',
                    '/<set\b[^>]*>/is',
                ];
                $cleanedSvg = preg_replace($dangerousPatterns, '', $svgContent);
                if (preg_match('/<script|javascript:|data:text\/html/i', $cleanedSvg)) {
                    return back()->withErrors(['logo' => 'Malicious executable code detected in uploaded SVG file. Upload rejected.']);
                }
                file_put_contents($file->getRealPath(), $cleanedSvg);
            }

            $safeSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $workspace->slug);
            $filename = 'logo_' . $safeSlug . '_' . time() . '.' . $ext;
            $destinationPath = public_path('images/uploads');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0775, true);
            }
            $file->move($destinationPath, $filename);
            $workspace->logo_url = asset('images/uploads/' . $filename);
        } elseif (!empty($validated['logo_url'])) {
            $workspace->logo_url = $validated['logo_url'];
        }

        // Save bank details array and transfer instructions
        $workspace->bank_details = [
            'bank_name' => $validated['bank_name'] ?? '',
            'account_number' => $validated['account_number'] ?? '',
            'routing_number' => $validated['routing_number'] ?? '',
            'swift' => $validated['swift'] ?? '',
        ];
        $workspace->bank_transfer_instructions = $validated['bank_transfer_instructions'] ?? null;
        $workspace->enable_paypal = (bool) $request->has('enable_paypal');
        $workspace->enable_bank_transfer = (bool) $request->has('enable_bank_transfer');

        // Save Gateway keys if provided (allowed for active paid plans or Super Admin, locked in trial)
        if ($user->isSuperAdmin() || !$workspace->isTrial()) {
            if (!empty($validated['stripe_publishable_key']) || !empty($validated['stripe_secret_key'])) {
                $workspace->stripe_keys_encrypted = [
                    'publishable_key' => $validated['stripe_publishable_key'] ?? '',
                    'secret_key' => $validated['stripe_secret_key'] ?? '',
                    'webhook_secret' => $validated['stripe_webhook_secret'] ?? '',
                ];
            }

            if (!empty($validated['paypal_client_id']) || !empty($validated['paypal_client_secret'])) {
                $workspace->paypal_keys_encrypted = [
                    'client_id' => $validated['paypal_client_id'] ?? '',
                    'client_secret' => $validated['paypal_client_secret'] ?? '',
                    'webhook_id' => $validated['paypal_webhook_id'] ?? '',
                ];
            }
        }

        // Save Custom SMTP settings (locked in trial mode, mandatory for active paid subscribed agencies)
        if ($user->isSuperAdmin() || !$workspace->isTrial()) {
            if ($request->has('smtp_host')) {
                $existingSmtp = $workspace->smtp_settings_encrypted ?? [];
                $password = $request->input('smtp_password');
                if (empty($password) && !empty($existingSmtp['password'])) {
                    $password = $existingSmtp['password'];
                }

                // For subscribed agencies, SMTP setup is strictly mandatory
                if (!$workspace->isTrial() && !$user->isSuperAdmin()) {
                    $request->validate([
                        'smtp_host' => 'required|string|max:255',
                        'smtp_port' => 'required|integer|min:1|max:65535',
                        'smtp_username' => 'required|string|max:255',
                        'smtp_encryption' => 'required|string|in:ssl,tls',
                        'smtp_from_address' => 'required|email|max:255',
                        'smtp_from_name' => 'required|string|max:255',
                    ]);

                    if (empty($password)) {
                        return back()->withErrors(['smtp_password' => 'SMTP Mailbox Password is mandatory for subscribed agencies.'])->withInput();
                    }
                }

                $workspace->smtp_settings_encrypted = [
                    'host' => trim($request->input('smtp_host', 'smtp.hostinger.com')),
                    'port' => (int) $request->input('smtp_port', 465),
                    'username' => trim($request->input('smtp_username', '')),
                    'password' => $password ?? '',
                    'encryption' => $request->input('smtp_encryption', 'ssl'),
                    'from_address' => trim($request->input('smtp_from_address', '')),
                    'from_name' => trim($request->input('smtp_from_name', '')),
                ];
            }
        }

        $workspace->save();

        return redirect()->route('settings.index')->with('success', 'Workspace settings and logo updated successfully.');
    }

    /**
     * Update Owner / User Personal Profile (Name & Contact Email)
     * Hardened against Account-Takeover (ATO): Requires password confirmation,
     * dispatches security alert to previous email, and logs audit trail (Item 9).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
        ]);

        $oldEmail = $user->email;
        $newEmail = strtolower(trim($validated['email']));

        if ($newEmail !== strtolower($oldEmail)) {
            // Require current password verification to prevent unauthorized account takeover
            if (empty($request->current_password) || !\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Security Verification Required: You must verify your current account password to change your login email address.',
                ]);
            }

            // Dispatch alert to previous email address
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Security Alert: Your Invoicy workspace account email has been updated from {$oldEmail} to {$newEmail}.\n\nIf you did not authorize this change, please contact support@redmapleagency.com immediately.",
                    function ($message) use ($oldEmail) {
                        $message->to($oldEmail)->subject('Security Alert: Invoicy Account Email Address Changed');
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to dispatch email change alert to old address: " . $e->getMessage());
            }

            $user->email = $newEmail;
            $user->email_verified_at = null; // Invalidate verification for new email

            \App\Services\AuditService::log('user.email_changed', $user, ['email' => $oldEmail], ['email' => $newEmail]);
        }

        $user->name = $validated['name'];
        $user->save();

        \App\Services\AuditService::log('user.profile_updated', $user, null, ['name' => $user->name, 'email' => $user->email]);

        return redirect()->route('settings.index')->with('profile_success', 'Your owner name and contact email have been updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return back()->with('password_success', 'Your password has been securely updated!');
    }

    public function testSmtp(Request $request)
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            return back()->with('smtp_error', 'Access Denied: Staff accounts cannot test SMTP configuration.');
        }

        $workspace = $user->workspace;
        if (!$user->isSuperAdmin() && $workspace->isTrial()) {
            return back()->with('smtp_error', 'SMTP Live Testing is disabled during the Free Trial evaluation period. Please upgrade to a paid agency subscription plan to connect and test your private domain SMTP server.');
        }

        $request->validate([
            'test_email' => 'required|email',
        ]);

        $targetEmail = $request->input('test_email');

        try {
            if ($workspace && !empty($workspace->smtp_settings_encrypted)) {
                $smtp = $workspace->smtp_settings_encrypted;
                if (!empty($smtp['host']) && !empty($smtp['username'])) {
                    config([
                        'mail.mailers.smtp.host' => $smtp['host'],
                        'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                        'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                        'mail.mailers.smtp.username' => $smtp['username'],
                        'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                        'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                        'mail.from.name' => $smtp['from_name'] ?? ($workspace->name ?: 'Invoicy by RedMaple'),
                    ]);
                }
            }

            $smtpData = [
                'workspaceName' => $workspace?->name ?: 'RedMaple Agency',
                'smtpHost' => config('mail.mailers.smtp.host'),
                'smtpPort' => config('mail.mailers.smtp.port'),
                'smtpEncryption' => config('mail.mailers.smtp.encryption'),
                'smtpUsername' => config('mail.mailers.smtp.username'),
                'fromAddress' => config('mail.from.address'),
                'fromName' => config('mail.from.name'),
            ];

            \Illuminate\Support\Facades\Mail::send('emails.smtp-test', $smtpData, function ($message) use ($targetEmail) {
                $message->to($targetEmail)->subject('Invoicy by RedMaple - Live SMTP Test Successful');
            });

            return back()->with('smtp_success', "Test email successfully delivered to {$targetEmail}!");
        } catch (\Throwable $e) {
            return back()->with('smtp_error', "SMTP Delivery Failed: " . $e->getMessage());
        }
    }

    /**
     * Add a new team member to the workspace within the plan's seat limit.
     */
    public function addTeamMember(Request $request)
    {
        $currentUser = Auth::user();
        $workspace = $currentUser->workspace;

        if (!$currentUser->isAdmin()) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Unauthorized: Only workspace owners and admins can add team members.');
        }

        $plan = $workspace?->currentSubscription?->plan;
        $seatLimit = $plan ? (int)$plan->team_seats : 3;
        // Ensure trial and starter workspaces allow at least 3 seats so team functionality can be tested
        if ($seatLimit < 3) {
            $seatLimit = 3;
        }

        $currentSeats = $workspace ? $workspace->users()->count() : 0;

        if ($currentSeats >= $seatLimit) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', "Workspace seat limit reached ({$currentSeats} of {$seatLimit} seats). Please upgrade your subscription plan to add more team members.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|in:admin,staff',
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers(),
            ],
        ]);

        $newUser = \App\Models\User::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_super_admin' => false,
            'email_verified_at' => null, // Requires email verification activation
        ]);

        // Generate 7-day signed email verification URL
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'team.verify',
            now()->addDays(7),
            [
                'id' => $newUser->id,
                'hash' => sha1($newUser->getEmailForVerification()),
            ]
        );

        // Dispatch official branded invitation & verification email
        try {
            \Illuminate\Support\Facades\Mail::to($newUser->email)->send(new \App\Mail\TeamMemberInvitationMailable(
                $newUser,
                $workspace,
                $currentUser,
                $verificationUrl,
                $validated['password']
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Team invitation email dispatch failed: ' . $e->getMessage());
        }

        \App\Services\AuditService::log('team.member_added', $newUser, null, [
            'name' => $newUser->name,
            'email' => $newUser->email,
            'role' => $newUser->role,
        ]);

        return redirect()->to(route('settings.index') . '#team')
            ->with('team_success', "Team member '{$newUser->name}' ({$newUser->email}) invited successfully! An email verification and activation link has been dispatched to their inbox.");
    }

    /**
     * Resend verification email to a pending team member.
     */
    public function resendTeamVerification($memberId)
    {
        $currentUser = Auth::user();
        $workspace = $currentUser->workspace;

        if (!$currentUser->isAdmin()) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Unauthorized: Only workspace owners and admins can manage team members.');
        }

        $member = \App\Models\User::where('workspace_id', $workspace->id)->where('id', $memberId)->firstOrFail();

        if ($member->isEmailVerified()) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', "Team member '{$member->name}' has already verified their email address.");
        }

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'team.verify',
            now()->addDays(7),
            [
                'id' => $member->id,
                'hash' => sha1($member->getEmailForVerification()),
            ]
        );

        try {
            \Illuminate\Support\Facades\Mail::to($member->email)->send(new \App\Mail\TeamMemberInvitationMailable(
                $member,
                $workspace,
                $currentUser,
                $verificationUrl,
                null
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Resend team verification failed: ' . $e->getMessage());
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Could not send verification email. Please check your outgoing SMTP settings.');
        }

        \App\Services\AuditService::log('team.verification_resent', $currentUser, [
            'member_id' => $member->id,
            'email' => $member->email,
        ]);

        return redirect()->to(route('settings.index') . '#team')
            ->with('team_success', "Fresh activation and verification email dispatched to {$member->email}.");
    }

    /**
     * Remove a team member from the workspace.
     */
    public function removeTeamMember($memberId)
    {
        $currentUser = Auth::user();
        $workspace = $currentUser->workspace;

        if (!$currentUser->isAdmin()) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Unauthorized: Only workspace owners and admins can manage team members.');
        }

        $member = \App\Models\User::where('workspace_id', $workspace->id)->where('id', $memberId)->firstOrFail();

        if ($member->role === 'owner') {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Security Violation: The workspace primary owner account cannot be deleted.');
        }

        if ($member->id === $currentUser->id) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'You cannot remove your own account.');
        }

        $memberName = $member->name;
        $memberEmail = $member->email;
        $memberRole = $member->role;

        $member->delete();

        \App\Services\AuditService::log('team.member_removed', $currentUser, [
            'name' => $memberName,
            'email' => $memberEmail,
            'role' => $memberRole,
        ], null);

        return redirect()->to(route('settings.index') . '#team')
            ->with('team_success', "Team member '{$memberName}' has been removed from your workspace.");
    }

    /**
     * Update team member role privileges (admin vs staff).
     */
    public function updateTeamMemberRole(Request $request, $memberId)
    {
        $currentUser = Auth::user();
        $workspace = $currentUser->workspace;

        if (!$currentUser->isAdmin()) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Unauthorized: Only workspace owners and admins can manage team member roles.');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,staff',
        ]);

        $member = \App\Models\User::where('workspace_id', $workspace->id)->where('id', $memberId)->firstOrFail();

        if ($member->role === 'owner') {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Security Violation: Workspace primary owner role cannot be changed.');
        }

        if ($member->is_super_admin) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'Security Violation: Platform Super Admin role cannot be modified.');
        }

        if ($member->id === $currentUser->id) {
            return redirect()->to(route('settings.index') . '#team')
                ->with('team_error', 'You cannot change your own role.');
        }

        $oldRole = $member->role;
        $member->role = $validated['role'];
        $member->save();

        \App\Services\AuditService::log('team.role_updated', $currentUser, [
            'member_id' => $member->id,
            'member_email' => $member->email,
            'old_role' => $oldRole,
            'new_role' => $member->role,
        ]);

        return redirect()->to(route('settings.index') . '#team')
            ->with('team_success', "Role privileges for '{$member->name}' updated to " . ucfirst($member->role) . " successfully.");
    }
}
