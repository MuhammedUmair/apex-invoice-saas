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
     * Renew or Upgrade Workspace Subscription
     */
    public function renewWorkspace(Workspace $workspace, Request $request, SubscriptionAlertService $alertService)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:36',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $months = (int) $request->duration_months;

        $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();
        $oldPlan = $sub?->plan;

        if ($sub) {
            $sub->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'gateway' => $sub->gateway ?: 'manual_admin',
                'canceled_at' => null,
                'current_period_ends_at' => now()->addMonths($months),
            ]);
        } else {
            Subscription::create([
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

        return back()->with('success', "Workspace '{$workspace->name}' renewed on {$plan->name} for {$months} month(s)!");
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

        $owner->update([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
        ]);

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
}
