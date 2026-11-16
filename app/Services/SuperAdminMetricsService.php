<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAlert;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminMetricsService
{
    /**
     * Compute all 12 core platform KPIs.
     */
    public function getDashboardKpis(?string $period = 'all'): array
    {
        $dateRange = $this->parsePeriod($period);
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // 1. Total Workspaces
        $totalWorkspacesQuery = Workspace::withoutGlobalScopes();
        if ($start && $end) {
            $totalWorkspacesQuery->whereBetween('created_at', [$start, $end]);
        }
        $totalWorkspaces = $totalWorkspacesQuery->count();

        // 2. Active Workspaces
        $activeWorkspaces = Subscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('current_period_ends_at')
                  ->orWhere('current_period_ends_at', '>', now());
            })
            ->distinct('workspace_id')
            ->count('workspace_id');

        // 3. Trial Workspaces
        $trialWorkspaces = Subscription::where('status', 'trialing')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '>', now());
            })
            ->distinct('workspace_id')
            ->count('workspace_id');

        // 4. Suspended Workspaces
        $suspendedWorkspaces = Subscription::where('status', 'suspended')
            ->distinct('workspace_id')
            ->count('workspace_id');

        // 5. Total Distinct Users across platform
        $totalUsersQuery = User::query();
        if ($start && $end) {
            $totalUsersQuery->whereBetween('created_at', [$start, $end]);
        }
        $totalUsers = $totalUsersQuery->distinct('email')->count('email');

        // 6. MRR (Monthly Recurring Revenue in integer cents)
        // Normalized monthly value of active recurring subscriptions
        $activeSubs = Subscription::where('status', 'active')
            ->with('plan')
            ->get();

        $mrrCents = 0;
        foreach ($activeSubs as $sub) {
            if (!$sub->plan || $sub->plan->price_cents <= 0) {
                continue;
            }
            if ($sub->plan->billing_interval === 'year') {
                $mrrCents += (int) round($sub->plan->price_cents / 12);
            } else {
                $mrrCents += (int) $sub->plan->price_cents;
            }
        }

        // 7. ARR (Annual Recurring Revenue in integer cents)
        $arrCents = $mrrCents * 12;

        // 8. Total Invoices (issued across all tenants)
        $invoicesQuery = Invoice::withoutGlobalScopes();
        if ($start && $end) {
            $invoicesQuery->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()]);
        }
        $totalInvoices = $invoicesQuery->count();

        // 9. Successful Payments (Count & Amount in Cents)
        $successfulPaymentsQuery = Payment::withoutGlobalScopes()->where('status', 'completed');
        if ($start && $end) {
            $successfulPaymentsQuery->whereBetween('payment_date', [$start, $end]);
        }
        $successfulPaymentsCount = $successfulPaymentsQuery->count();
        $successfulPaymentsAmountCents = (int) $successfulPaymentsQuery->sum('amount_cents');

        // 10. Failed Payments (Count & Amount in Cents)
        $failedPaymentsQuery = Payment::withoutGlobalScopes()->where('status', 'failed');
        if ($start && $end) {
            $failedPaymentsQuery->whereBetween('payment_date', [$start, $end]);
        }
        $failedPaymentsCount = $failedPaymentsQuery->count();
        $failedPaymentsAmountCents = (int) $failedPaymentsQuery->sum('amount_cents');

        // 11. Pending Upgrade Requests
        $pendingUpgradeRequests = SubscriptionAlert::where('type', 'upgrade_request')
            ->where('is_read', false)
            ->count();

        // 12. Churn Rate (Canceled subscriptions / total ever subscribed * 100)
        $canceledSubs = Subscription::where('status', 'canceled')->count();
        $totalSubscribedBase = Subscription::count();
        $churnRate = $totalSubscribedBase > 0 ? round(($canceledSubs / $totalSubscribedBase) * 100, 1) : 0.0;

        return [
            'period' => $period,
            'total_workspaces' => $totalWorkspaces,
            'active_workspaces' => $activeWorkspaces,
            'trial_workspaces' => $trialWorkspaces,
            'suspended_workspaces' => $suspendedWorkspaces,
            'total_users' => $totalUsers,
            'mrr_cents' => $mrrCents,
            'mrr_dollars' => round($mrrCents / 100, 2),
            'arr_cents' => $arrCents,
            'arr_dollars' => round($arrCents / 100, 2),
            'total_invoices' => $totalInvoices,
            'successful_payments_count' => $successfulPaymentsCount,
            'successful_payments_amount_cents' => $successfulPaymentsAmountCents,
            'successful_payments_amount_dollars' => round($successfulPaymentsAmountCents / 100, 2),
            'failed_payments_count' => $failedPaymentsCount,
            'failed_payments_amount_cents' => $failedPaymentsAmountCents,
            'failed_payments_amount_dollars' => round($failedPaymentsAmountCents / 100, 2),
            'pending_upgrade_requests' => $pendingUpgradeRequests,
            'churn_rate' => $churnRate,
        ];
    }

    /**
     * Parse date range string into Carbon instances.
     */
    public function parsePeriod(?string $period): array
    {
        $now = now();
        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Today',
            ],
            'last_7_days' => [
                'start' => $now->copy()->subDays(7)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Last 7 Days',
            ],
            'last_30_days' => [
                'start' => $now->copy()->subDays(30)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Last 30 Days',
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => 'This Month',
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
                'label' => 'Last Month',
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'label' => 'This Year',
            ],
            default => [
                'start' => null,
                'end' => null,
                'label' => 'All Time',
            ],
        };
    }

    /**
     * Get revenue and subscription trends for charts and reports.
     */
    public function getRevenueAnalytics(): array
    {
        // Monthly revenue by plan for the last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $months[] = [
                'key' => $monthDate->format('Y-m'),
                'label' => $monthDate->format('M Y'),
                'start' => $monthDate->copy()->startOfMonth(),
                'end' => $monthDate->copy()->endOfMonth(),
            ];
        }

        $monthlyTrend = [];
        foreach ($months as $m) {
            $paidCents = (int) Payment::withoutGlobalScopes()
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$m['start'], $m['end']])
                ->sum('amount_cents');

            $registrations = Workspace::withoutGlobalScopes()
                ->whereBetween('created_at', [$m['start'], $m['end']])
                ->count();

            $monthlyTrend[] = [
                'month' => $m['label'],
                'revenue_dollars' => round($paidCents / 100, 2),
                'registrations' => $registrations,
            ];
        }

        // Subscriptions distribution by plan
        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $planDistribution = [];
        foreach ($plans as $plan) {
            $planDistribution[] = [
                'name' => $plan->name,
                'code' => $plan->code,
                'price' => $plan->formatted_price,
                'active_count' => $plan->subscriptions_count,
            ];
        }

        return [
            'monthly_trend' => $monthlyTrend,
            'plan_distribution' => $planDistribution,
        ];
    }
}
