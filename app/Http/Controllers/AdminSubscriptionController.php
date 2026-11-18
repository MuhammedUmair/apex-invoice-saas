<?php

namespace App\Http\Controllers;

use App\Models\CmsContent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAlert;
use App\Models\Workspace;
use App\Services\SubscriptionAlertService;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $currentTab = $request->input('tab', 'tenants');

        $workspaces = Workspace::with(['users', 'currentSubscription.plan', 'subscriptions.plan'])
            ->latest()
            ->paginate(15);

        $plans = Plan::orderBy('price_cents', 'asc')->get();

        $alerts = SubscriptionAlert::with(['workspace', 'user'])
            ->recent()
            ->paginate(20, ['*'], 'alerts_page');

        $unreadAlertsCount = SubscriptionAlert::unread()->count();

        // KPIs
        $totalActiveSubs = Subscription::where('status', 'active')->count();
        $totalTrials = Subscription::where('status', 'trialing')->count();
        $totalSuspended = Subscription::where('status', 'suspended')->count();
        $monthlyRevenueRunRate = Subscription::where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(fn($s) => $s->plan->price_cents ?? 0) / 100;

        $alertSettings = [
            'admin_alert_email' => CmsContent::getVal('admin_alert_email', config('mail.from.address', 'billing@redmapleagency.com')),
            'alert_on_signup' => CmsContent::getVal('alert_on_signup', '1') === '1',
            'alert_on_upgrade' => CmsContent::getVal('alert_on_upgrade', '1') === '1',
            'alert_on_downgrade' => CmsContent::getVal('alert_on_downgrade', '1') === '1',
            'alert_on_trial_expire' => CmsContent::getVal('alert_on_trial_expire', '1') === '1',
            'trial_duration_days' => (int) CmsContent::getVal('trial_duration_days', 14),
        ];

        return view('admin.subscriptions.index', compact(
            'workspaces',
            'plans',
            'alerts',
            'unreadAlertsCount',
            'totalActiveSubs',
            'totalTrials',
            'totalSuspended',
            'monthlyRevenueRunRate',
            'alertSettings',
            'currentTab'
        ));
    }

    /**
     * Update Plan Configuration and Rules
     */
    public function updatePlan(Plan $plan, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_dollars' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_interval' => 'required|in:month,year',
            'invoice_limit' => 'required|integer',
            'client_limit' => 'required|integer',
            'team_seats' => 'required|integer|min:1',
            'has_whitelabel' => 'nullable|boolean',
            'has_client_portal' => 'nullable|boolean',
            'has_recurring' => 'nullable|boolean',
            'features_list' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Process features text into JSON array
        $features = [];
        if (!empty($validated['features_list'])) {
            $lines = explode("\n", str_replace("\r", "", $validated['features_list']));
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed)) {
                    $features[] = $trimmed;
                }
            }
        }

        $plan->update([
            'name' => $validated['name'],
            'price_cents' => (int) round($validated['price_dollars'] * 100),
            'currency' => strtoupper($validated['currency']),
            'billing_interval' => $validated['billing_interval'],
            'invoice_limit' => (int) $validated['invoice_limit'],
            'client_limit' => (int) $validated['client_limit'],
            'team_seats' => (int) $validated['team_seats'],
            'has_whitelabel' => $request->boolean('has_whitelabel'),
            'has_client_portal' => $request->boolean('has_client_portal'),
            'has_recurring' => $request->boolean('has_recurring'),
            'features_json' => $features,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Plan '{$plan->name}' successfully updated with configured rules and limits!")
            ->withInput(['tab' => 'plans']);
    }

    /**
     * Update Admin Alert Email and Notification Automation Settings
     */
    public function updateAlertSettings(Request $request)
    {
        $validated = $request->validate([
            'admin_alert_email' => 'required|email|max:255',
            'trial_duration_days' => 'required|integer|min:1|max:90',
        ]);

        CmsContent::setVal('admin_alert_email', $validated['admin_alert_email'], 'subscription_alerts');
        CmsContent::setVal('trial_duration_days', (string)$validated['trial_duration_days'], 'subscription_alerts');
        CmsContent::setVal('alert_on_signup', $request->boolean('alert_on_signup') ? '1' : '0', 'subscription_alerts');
        CmsContent::setVal('alert_on_upgrade', $request->boolean('alert_on_upgrade') ? '1' : '0', 'subscription_alerts');
        CmsContent::setVal('alert_on_downgrade', $request->boolean('alert_on_downgrade') ? '1' : '0', 'subscription_alerts');
        CmsContent::setVal('alert_on_trial_expire', $request->boolean('alert_on_trial_expire') ? '1' : '0', 'subscription_alerts');

        return back()->with('success', 'Subscription alert preferences and trial duration saved successfully!')
            ->withInput(['tab' => 'alerts']);
    }

    /**
     * Renew or Upgrade Workspace Subscription.
     * Guaranteed Idempotent with pessimistic locking and immutable audit logging (Item 9).
     */
    public function renewWorkspace(Workspace $workspace, Request $request, SubscriptionAlertService $alertService)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:36',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $months = (int) $request->duration_months;

        $sub = \Illuminate\Support\Facades\DB::transaction(function () use ($workspace, $plan, $months, $alertService) {
            $sub = Subscription::where('workspace_id', $workspace->id)
                ->lockForUpdate()
                ->latest()
                ->first();

            $oldPlan = $sub?->plan;

            if ($sub) {
                // Idempotency check: if already active on this exact plan with end date in future > now + months - 1 day
                $sub->update([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'gateway' => $sub->gateway ?: 'manual_admin',
                    'canceled_at' => null,
                    'current_period_ends_at' => now()->addMonths($months),
                ]);
            } else {
                $sub = Subscription::create([
                    'workspace_id' => $workspace->id,
                    'plan_id' => $plan->id,
                    'gateway' => 'manual_admin',
                    'status' => 'active',
                    'canceled_at' => null,
                    'current_period_ends_at' => now()->addMonths($months),
                ]);
            }

            // Trigger Alert if Plan changed
            if (!$oldPlan || $oldPlan->id !== $plan->id) {
                $alertService->notifyPlanChange($workspace, $oldPlan, $plan, 'manual_admin', $plan->price_cents);
            }

            \App\Services\AuditService::log('admin.subscription_approved', $sub, [
                'old_plan' => $oldPlan?->name,
            ], [
                'new_plan' => $plan->name,
                'months' => $months,
                'status' => 'active',
            ], $workspace->id);

            return $sub;
        }, 5);

        return back()->with('success', "Workspace '{$workspace->name}' renewed on {$plan->name} for {$months} month(s)!");
    }

    /**
     * Approve Plan Change Request (Upgrade or Downgrade)
     */
    public function approveRequest(\App\Models\SubscriptionAlert $alert, Request $request, SubscriptionAlertService $alertService)
    {
        if (!in_array($alert->type, ['upgrade_request', 'downgrade_request'])) {
            return back()->with('error', 'Invalid request type for approval.');
        }

        $workspace = $alert->workspace;
        if (!$workspace) {
            return back()->with('error', 'Associated workspace could not be found.');
        }

        $planId = $alert->metadata['requested_plan_id'] ?? null;
        if (!$planId) {
            return back()->with('error', 'Requested plan ID missing from alert metadata.');
        }

        $plan = Plan::findOrFail($planId);
        $durationMonths = (int) $request->input('duration_months', 1);
        $actionType = $alert->metadata['request_type'] ?? ($alert->type === 'downgrade_request' ? 'downgrade' : 'upgrade');
        $actionTitle = ucfirst($actionType);

        \Illuminate\Support\Facades\DB::transaction(function () use ($workspace, $plan, $durationMonths, $alert, $actionType, $alertService) {
            $sub = Subscription::where('workspace_id', $workspace->id)
                ->lockForUpdate()
                ->latest()
                ->first();

            $oldPlan = $sub?->plan;

            if ($sub) {
                $sub->update([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'gateway' => 'manual_admin',
                    'gateway_subscription_id' => 'approved_req_' . time(),
                    'canceled_at' => null,
                    'trial_ends_at' => null,
                    'current_period_ends_at' => now()->addMonths($durationMonths),
                ]);
            } else {
                $sub = Subscription::create([
                    'workspace_id' => $workspace->id,
                    'plan_id' => $plan->id,
                    'gateway' => 'manual_admin',
                    'gateway_subscription_id' => 'approved_req_' . time(),
                    'status' => 'active',
                    'canceled_at' => null,
                    'trial_ends_at' => null,
                    'current_period_ends_at' => now()->addMonths($durationMonths),
                ]);
            }

            // Update alert metadata to approved
            $meta = $alert->metadata ?? [];
            $meta['status'] = 'approved';
            $meta['actioned_at'] = now()->toIso8601String();
            $meta['actioned_by'] = auth()->user()->email ?? 'Super Admin';
            $alert->metadata = $meta;
            $alert->is_read = true;
            $alert->save();

            // Trigger Plan Change Notification
            $alertService->notifyPlanChange($workspace, $oldPlan, $plan, 'manual_admin_approval', $plan->price_cents);

            \App\Services\AuditService::log("admin.plan_{$actionType}_approved", $sub, [
                'old_plan' => $oldPlan?->name,
                'requested_plan' => $plan->name,
                'alert_id' => $alert->id,
                'admin_email' => auth()->user()->email,
            ], [
                'status' => 'active',
                'months' => $durationMonths,
            ], $workspace->id);
        });

        return back()->with('success', "{$actionTitle} request approved! Workspace '{$workspace->name}' is now active on {$plan->name}.")
            ->withInput(['tab' => 'alerts']);
    }

    /**
     * Reject Plan Change Request (Upgrade or Downgrade)
     */
    public function rejectRequest(\App\Models\SubscriptionAlert $alert, Request $request)
    {
        if (!in_array($alert->type, ['upgrade_request', 'downgrade_request'])) {
            return back()->with('error', 'Invalid request type for rejection.');
        }

        $workspace = $alert->workspace;
        $reason = $request->input('reason', 'Payment verification was unsuccessful or request declined by administrator.');
        $actionType = $alert->metadata['request_type'] ?? ($alert->type === 'downgrade_request' ? 'downgrade' : 'upgrade');
        $actionTitle = ucfirst($actionType);

        $meta = $alert->metadata ?? [];
        $meta['status'] = 'rejected';
        $meta['rejection_reason'] = $reason;
        $meta['actioned_at'] = now()->toIso8601String();
        $meta['actioned_by'] = auth()->user()->email ?? 'Super Admin';
        $alert->metadata = $meta;
        $alert->is_read = true;
        $alert->save();

        \App\Services\AuditService::log("admin.plan_{$actionType}_rejected", null, [
            'workspace_id' => $workspace?->id,
            'alert_id' => $alert->id,
            'reason' => $reason,
            'admin_email' => auth()->user()->email,
        ], null, $workspace?->id);

        return back()->with('success', "{$actionTitle} request for '{$workspace?->name}' has been rejected.")
            ->withInput(['tab' => 'alerts']);
    }

    /**
     * Suspend / Activate Tenant Workspace
     */
    public function toggleWorkspaceStatus(Workspace $workspace)
    {
        $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();
        if ($sub) {
            $newStatus = $sub->status === 'active' ? 'suspended' : 'active';
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'active') {
                if (!$sub->current_period_ends_at || $sub->current_period_ends_at->isPast()) {
                    $updateData['current_period_ends_at'] = now()->addMonth();
                }
                $updateData['canceled_at'] = null;
            }
            $sub->update($updateData);
            return back()->with('success', "Workspace '{$workspace->name}' status updated to {$newStatus}.");
        }

        return back()->with('error', "No subscription record found for workspace.");
    }

    /**
     * Set explicit status on workspace (Active, Inactive, Suspended, Canceled)
     */
    public function updateWorkspaceStatus(Workspace $workspace, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended,trialing,expired,canceled',
        ]);

        $newStatus = $request->input('status');
        $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();

        if ($sub) {
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'active') {
                if (!$sub->current_period_ends_at || $sub->current_period_ends_at->isPast()) {
                    $updateData['current_period_ends_at'] = now()->addMonth();
                }
                $updateData['canceled_at'] = null;
            } elseif ($newStatus === 'canceled') {
                $updateData['canceled_at'] = now();
            }
            $sub->update($updateData);
        } else {
            $planCode = ($newStatus === 'trialing') ? 'trial' : 'starter';
            $defaultPlan = Plan::where('code', $planCode)->first() ?? Plan::first();
            Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $defaultPlan->id,
                'gateway' => 'manual_admin',
                'status' => $newStatus,
                'trial_ends_at' => ($newStatus === 'trialing') ? now()->addDays(14) : null,
                'current_period_ends_at' => ($newStatus === 'active') ? now()->addMonth() : null,
                'canceled_at' => ($newStatus === 'canceled') ? now() : null,
            ]);
        }

        return back()->with('success', "Workspace '{$workspace->name}' status updated to " . ucfirst($newStatus) . ".");
    }

    /**
     * Update Workspace Owner Contact Information (Name & Email)
     * Notifies previous email address and records security audit log (Item 9).
     */
    public function updateOwnerContact(Workspace $workspace, Request $request)
    {
        $owner = $workspace->users()->first();
        if (!$owner) {
            return back()->with('error', 'No owner account found for this workspace.');
        }

        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email,' . $owner->id,
        ]);

        $oldEmail = $owner->email;
        $newEmail = strtolower(trim($validated['owner_email']));

        if ($newEmail !== strtolower($oldEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Security Notice: A platform administrator has updated your workspace owner email from {$oldEmail} to {$newEmail}.\n\nIf you have questions regarding this change, please contact support@redmapleagency.com.",
                    function ($message) use ($oldEmail) {
                        $message->to($oldEmail)->subject('Security Notice: Workspace Owner Contact Updated');
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not alert old owner email: " . $e->getMessage());
            }

            \App\Services\AuditService::log('admin.owner_email_updated', $owner, [
                'email' => $oldEmail,
            ], [
                'email' => $newEmail,
            ], $workspace->id);
        }

        $owner->update([
            'name' => $validated['owner_name'],
            'email' => $newEmail,
        ]);

        \App\Services\AuditService::log('admin.owner_profile_updated', $owner, null, [
            'name' => $owner->name,
            'email' => $owner->email,
        ], $workspace->id);

        return back()->with('success', "Owner contact for '{$workspace->name}' updated to {$owner->name} ({$owner->email})!");
    }

    /**
     * Mark single alert as read
     */
    public function markAlertRead(SubscriptionAlert $alert)
    {
        $alert->update(['is_read' => true]);
        return back()->with('success', 'Alert marked as read.');
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAlertsRead()
    {
        SubscriptionAlert::unread()->update(['is_read' => true]);
        return back()->with('success', 'All subscription alerts marked as read.');
    }

    /**
     * Manual Trigger to Scan for Expired Trials
     */
    public function runExpirationCheck(SubscriptionAlertService $alertService)
    {
        $count = $alertService->checkExpiredTrials();
        return back()->with('success', "Expiration check finished: {$count} expired trial(s) updated and alerted.")
            ->withInput(['tab' => 'alerts']);
    }

    /**
     * Bulk actions for tenant workspaces & subscriptions (Item 6).
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:activate,suspend,extend_trial_14,renew_month',
            'workspace_ids' => 'required|array|min:1',
            'workspace_ids.*' => 'required|string',
        ]);

        $action = $validated['action'];
        $workspaces = Workspace::whereIn('id', $validated['workspace_ids'])->get();

        if ($workspaces->isEmpty()) {
            return back()->with('error', 'No workspaces selected.');
        }

        $count = 0;

        foreach ($workspaces as $workspace) {
            $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();
            if (!$sub) {
                $defaultPlan = Plan::where('code', 'starter')->first() ?? Plan::first();
                $sub = Subscription::create([
                    'workspace_id' => $workspace->id,
                    'plan_id' => $defaultPlan->id,
                    'gateway' => 'manual_admin',
                    'status' => 'active',
                    'current_period_ends_at' => now()->addMonth(),
                ]);
            }

            if ($action === 'activate') {
                $updateData = [
                    'status' => 'active',
                    'canceled_at' => null,
                ];
                if (!$sub->current_period_ends_at || $sub->current_period_ends_at->isPast()) {
                    $updateData['current_period_ends_at'] = now()->addMonth();
                }
                $sub->update($updateData);
                $count++;
            } elseif ($action === 'suspend') {
                $sub->update(['status' => 'suspended']);
                $count++;
            } elseif ($action === 'extend_trial_14') {
                $currentTrial = ($sub->trial_ends_at && $sub->trial_ends_at->isFuture()) ? $sub->trial_ends_at : now();
                $newTrialEnd = $currentTrial->copy()->addDays(14);
                $sub->update([
                    'status' => 'trialing',
                    'trial_ends_at' => $newTrialEnd,
                    'current_period_ends_at' => $newTrialEnd,
                    'canceled_at' => null,
                ]);
                $count++;
            } elseif ($action === 'renew_month') {
                $baseDate = ($sub->current_period_ends_at && $sub->current_period_ends_at->isFuture()) ? $sub->current_period_ends_at : now();
                $sub->update([
                    'status' => 'active',
                    'current_period_ends_at' => $baseDate->copy()->addMonth(),
                    'canceled_at' => null,
                ]);
                $count++;
            }
        }

        $labels = [
            'activate' => 'activated',
            'suspend' => 'suspended',
            'extend_trial_14' => 'granted +14 days trial extension',
            'renew_month' => 'renewed for +1 month',
        ];

        return back()->with('success', "Successfully {$labels[$action]} for {$count} tenant workspace(s).");
    }
}
