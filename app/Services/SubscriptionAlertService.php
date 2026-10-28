<?php

namespace App\Services;

use App\Mail\AdminSubscriptionAlertMailable;
use App\Models\CmsContent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAlert;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionAlertService
{
    /**
     * Get configured admin recipient email address
     */
    public function getAdminEmail(): string
    {
        $email = CmsContent::getVal('admin_alert_email');
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return config('mail.from.address', 'billing@redmapleagency.com');
    }

    /**
     * Check if specific alert type is enabled
     */
    public function isAlertEnabled(string $type): bool
    {
        $key = match ($type) {
            'signup' => 'alert_on_signup',
            'upgrade' => 'alert_on_upgrade',
            'downgrade' => 'alert_on_downgrade',
            'trial_expired' => 'alert_on_trial_expire',
            default => null,
        };

        if (!$key) {
            return true;
        }

        return CmsContent::getVal($key, '1') === '1';
    }

    /**
     * Notify Admin of New User / Tenant Signup
     */
    public function notifySignup(User $user, Workspace $workspace, Plan $plan, ?Subscription $sub = null): ?SubscriptionAlert
    {
        try {
            $alert = SubscriptionAlert::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'type' => 'signup',
                'title' => "New User Signup: {$user->name} ({$workspace->name})",
                'message' => "{$user->name} ({$user->email}) has registered tenant workspace '{$workspace->name}' on the {$plan->name} package.",
                'metadata' => [
                    'workspace_name' => $workspace->name,
                    'workspace_slug' => $workspace->slug,
                    'owner_name' => $user->name,
                    'owner_email' => $user->email,
                    'plan_name' => $plan->name,
                    'price_formatted' => $plan->formatted_price,
                    'trial_ends_at' => $sub?->trial_ends_at?->format('M d, Y') ?? 'N/A',
                ],
                'is_read' => false,
                'email_sent' => false,
            ]);

            if ($this->isAlertEnabled('signup')) {
                $this->dispatchEmail($alert);
            }

            return $alert;
        } catch (\Throwable $e) {
            Log::error("Failed to record subscription signup alert: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify Admin of Plan Upgrade or Downgrade
     */
    public function notifyPlanChange(Workspace $workspace, ?Plan $oldPlan, Plan $newPlan, ?string $gateway = null, int $paidCents = 0): ?SubscriptionAlert
    {
        try {
            $owner = $workspace->users()->first();
            $isUpgrade = !$oldPlan || ($newPlan->price_cents >= $oldPlan->price_cents);
            $type = $isUpgrade ? 'upgrade' : 'downgrade';

            $title = $isUpgrade
                ? "Plan Upgraded: {$workspace->name} to {$newPlan->name}"
                : "Plan Downgraded: {$workspace->name} to {$newPlan->name}";

            $message = $isUpgrade
                ? "{$workspace->name} has upgraded from " . ($oldPlan->name ?? 'Free Trial') . " to {$newPlan->name}."
                : "{$workspace->name} has downgraded from {$oldPlan->name} to {$newPlan->name}.";

            $alert = SubscriptionAlert::create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner?->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'metadata' => [
                    'workspace_name' => $workspace->name,
                    'workspace_slug' => $workspace->slug,
                    'owner_name' => $owner?->name ?? 'N/A',
                    'owner_email' => $owner?->email ?? 'N/A',
                    'old_plan_name' => $oldPlan?->name ?? 'None',
                    'plan_name' => $newPlan->name,
                    'price_formatted' => $newPlan->formatted_price,
                    'gateway' => $gateway ?? 'card',
                    'paid_cents' => $paidCents,
                ],
                'is_read' => false,
                'email_sent' => false,
            ]);

            if ($this->isAlertEnabled($type)) {
                $this->dispatchEmail($alert);
            }

            return $alert;
        } catch (\Throwable $e) {
            Log::error("Failed to record subscription plan change alert: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify Admin of Manual Upgrade / Wire Transfer Request
     */
    public function notifyUpgradeRequest(Workspace $workspace, Plan $requestedPlan, ?string $notes = null, int $finalPriceCents = 0): ?SubscriptionAlert
    {
        try {
            $owner = $workspace->users()->first();
            $currentSub = $workspace->currentSubscription;
            $currentPlan = $currentSub?->plan;

            $alert = SubscriptionAlert::create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner?->id,
                'type' => 'upgrade_request',
                'title' => "Upgrade Request: {$workspace->name} requested {$requestedPlan->name}",
                'message' => "{$owner?->name} ({$owner?->email}) from '{$workspace->name}' has submitted an upgrade request for {$requestedPlan->name} (" . ($finalPriceCents > 0 ? '$' . number_format($finalPriceCents / 100, 2) . '/mo' : 'Free') . "). Wire transfer / bank payment verification required.",
                'metadata' => [
                    'workspace_name' => $workspace->name,
                    'workspace_slug' => $workspace->slug,
                    'owner_name' => $owner?->name ?? 'N/A',
                    'owner_email' => $owner?->email ?? 'N/A',
                    'current_plan' => $currentPlan?->name ?? 'None',
                    'requested_plan_id' => $requestedPlan->id,
                    'requested_plan_name' => $requestedPlan->name,
                    'price_formatted' => '$' . number_format($finalPriceCents / 100, 2),
                    'notes' => $notes ?: 'No specific notes provided.',
                ],
                'is_read' => false,
                'email_sent' => false,
            ]);

            $this->dispatchEmail($alert);

            return $alert;
        } catch (\Throwable $e) {
            Log::error("Failed to record subscription upgrade request alert: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify Admin of Trial Expiration
     */
    public function notifyTrialExpired(Workspace $workspace, Subscription $sub): ?SubscriptionAlert
    {
        try {
            // Check if already notified recently
            $existing = SubscriptionAlert::where('workspace_id', $workspace->id)
                ->where('type', 'trial_expired')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($existing) {
                return null;
            }

            $owner = $workspace->users()->first();
            $alert = SubscriptionAlert::create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner?->id,
                'type' => 'trial_expired',
                'title' => "Trial Expired: {$workspace->name} free trial ended",
                'message' => "The 14-day free trial period for workspace '{$workspace->name}' has concluded. Invoicing is now locked pending upgrade.",
                'metadata' => [
                    'workspace_name' => $workspace->name,
                    'workspace_slug' => $workspace->slug,
                    'owner_name' => $owner?->name ?? 'N/A',
                    'owner_email' => $owner?->email ?? 'N/A',
                    'trial_ends_at' => $sub->trial_ends_at?->format('M d, Y') ?? now()->format('M d, Y'),
                ],
                'is_read' => false,
                'email_sent' => false,
            ]);

            if ($this->isAlertEnabled('trial_expired')) {
                $this->dispatchEmail($alert);
            }

            return $alert;
        } catch (\Throwable $e) {
            Log::error("Failed to record trial expiration alert: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dispatch Alert Email to Super Admin
     */
    protected function dispatchEmail(SubscriptionAlert $alert): void
    {
        try {
            $to = $this->getAdminEmail();
            Mail::to($to)->send(new AdminSubscriptionAlertMailable($alert));
            $alert->update(['email_sent' => true]);
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch admin subscription alert email: " . $e->getMessage());
        }
    }

    /**
     * Scan and mark expired trials and trigger alerts
     */
    public function checkExpiredTrials(): int
    {
        $expiredSubs = Subscription::with(['workspace.users', 'plan'])
            ->where('status', 'trialing')
            ->where('trial_ends_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredSubs as $sub) {
            $sub->update(['status' => 'expired']);
            if ($sub->workspace) {
                $this->notifyTrialExpired($sub->workspace, $sub);
            }
            $count++;
        }

        return $count;
    }
}
