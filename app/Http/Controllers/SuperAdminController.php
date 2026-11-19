<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CmsContent;
use App\Models\ImpersonationLog;
use App\Models\Invoice;
use App\Models\LoginHistory;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionAlert;
use App\Models\SupportTicket;
use App\Models\SystemAnnouncement;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\UserFeedback;
use App\Models\Workspace;
use App\Services\AuditService;
use App\Services\SuperAdminMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class SuperAdminController extends Controller
{
    protected SuperAdminMetricsService $metrics;

    public function __construct(SuperAdminMetricsService $metrics)
    {
        $this->metrics = $metrics;
    }

    /*
    |--------------------------------------------------------------------------
    | OVERVIEW
    |--------------------------------------------------------------------------
    */
    public function dashboard(Request $request)
    {
        $period = $request->input('period', 'all');
        $kpis = $this->metrics->getDashboardKpis($period);
        $analytics = $this->metrics->getRevenueAnalytics();

        $recentRegistrations = Workspace::withoutGlobalScopes()
            ->with(['users', 'currentSubscription.plan'])
            ->latest()
            ->limit(6)
            ->get();

        $recentAlerts = SubscriptionAlert::with(['workspace', 'user'])
            ->recent()
            ->limit(5)
            ->get();

        $recentActivities = AuditLog::with(['user', 'workspace'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        // System health summary preview
        $healthPreview = [
            'database' => $this->checkDatabaseConnection(),
            'storage_writable' => is_writable(storage_path('app')),
            'cache' => true,
        ];

        return view('admin.dashboard', compact(
            'kpis',
            'analytics',
            'recentRegistrations',
            'recentAlerts',
            'recentActivities',
            'healthPreview',
            'period'
        ));
    }

    public function analytics(Request $request)
    {
        $period = $request->input('period', 'all');
        $kpis = $this->metrics->getDashboardKpis($period);
        $analytics = $this->metrics->getRevenueAnalytics();

        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return view('admin.analytics', compact('kpis', 'analytics', 'plans', 'period'));
    }

    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'workspaces');
        $filename = "invoicy_{$type}_export_" . now()->format('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'workspaces') {
                fputcsv($file, ['Workspace ID', 'Workspace Name', 'Slug', 'Created At', 'Plan', 'Status', 'Owner Name', 'Owner Email']);
                $workspaces = Workspace::withoutGlobalScopes()->with(['users', 'currentSubscription.plan'])->get();
                foreach ($workspaces as $w) {
                    $owner = $w->users->first();
                    $sub = $w->currentSubscription;
                    fputcsv($file, [
                        $w->id,
                        $w->name,
                        $w->slug,
                        $w->created_at->toDateTimeString(),
                        $sub?->plan?->name ?? 'Free Trial',
                        $sub?->status ?? 'trialing',
                        $owner?->name ?? 'N/A',
                        $owner?->email ?? 'N/A',
                    ]);
                }
            } elseif ($type === 'transactions') {
                fputcsv($file, ['Payment ID', 'Payment Number', 'Amount ($)', 'Currency', 'Gateway', 'Status', 'Date', 'Transaction ID']);
                $payments = Payment::withoutGlobalScopes()->latest('payment_date')->get();
                foreach ($payments as $p) {
                    fputcsv($file, [
                        $p->id,
                        $p->payment_number,
                        number_format($p->amount_cents / 100, 2),
                        $p->currency,
                        $p->gateway,
                        $p->status,
                        $p->payment_date ? $p->payment_date->toDateTimeString() : 'N/A',
                        $p->gateway_transaction_id ?? 'N/A',
                    ]);
                }
            }

            fclose($file);
        };

        AuditService::log('admin.export_csv', null, null, ['type' => $type, 'filename' => $filename]);

        return Response::stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    public function workspaces(Request $request)
    {
        $query = Workspace::withoutGlobalScopes()->with(['users', 'currentSubscription.plan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('slug', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhereHas('users', function ($uq) use ($s) {
                      $uq->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'trialing') {
                $query->whereHas('currentSubscription', fn($sq) => $sq->where('status', 'trialing'));
            } elseif ($status === 'active') {
                $query->whereHas('currentSubscription', fn($sq) => $sq->where('status', 'active'));
            } elseif ($status === 'suspended') {
                $query->whereHas('currentSubscription', fn($sq) => $sq->where('status', 'suspended'));
            } elseif ($status === 'canceled') {
                $query->whereHas('currentSubscription', fn($sq) => $sq->where('status', 'canceled'));
            }
        }

        $workspaces = $query->latest()->paginate(15)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('admin.workspaces.index', compact('workspaces', 'plans'));
    }

    public function workspaceShow(Workspace $workspace)
    {
        $workspace->load(['users', 'subscriptions.plan', 'invoices', 'clients']);
        $owner = $workspace->users->first();
        $subscription = $workspace->currentSubscription;
        $invoicesCount = $workspace->invoices()->count();
        $totalBilledCents = (int) $workspace->invoices()->sum('total_cents');
        $paymentsCount = Payment::withoutGlobalScopes()->where('workspace_id', $workspace->id)->count();
        $recentAuditLogs = AuditLog::where('workspace_id', $workspace->id)->latest('created_at')->limit(10)->get();
        $plans = Plan::where('is_active', true)->get();

        return view('admin.workspaces.show', compact(
            'workspace',
            'owner',
            'subscription',
            'invoicesCount',
            'totalBilledCents',
            'paymentsCount',
            'recentAuditLogs',
            'plans'
        ));
    }

    public function workspaceActivity(Workspace $workspace)
    {
        $activity = AuditLog::where('workspace_id', $workspace->id)
            ->with('user')
            ->latest('created_at')
            ->paginate(25);

        return view('admin.workspaces.activity', compact('workspace', 'activity'));
    }

    public function suspendedWorkspaces(Request $request)
    {
        $workspaces = Workspace::withoutGlobalScopes()
            ->whereHas('currentSubscription', function ($q) {
                $q->where('status', 'suspended');
            })
            ->with(['users', 'currentSubscription.plan'])
            ->latest()
            ->paginate(15);

        return view('admin.workspaces.suspended', compact('workspaces'));
    }

    public function suspendWorkspace(Workspace $workspace, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();
        $oldStatus = $sub ? $sub->status : 'unknown';

        if ($sub) {
            $sub->update(['status' => 'suspended']);
        } else {
            $defaultPlan = Plan::first();
            $sub = Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $defaultPlan?->id,
                'status' => 'suspended',
                'gateway' => 'manual_admin',
            ]);
        }

        AuditService::log('admin.workspace_suspended', $workspace, [
            'previous_status' => $oldStatus,
        ], [
            'status' => 'suspended',
            'reason' => $request->reason,
            'actor' => auth()->user()->email,
        ], $workspace->id);

        return back()->with('success', "Workspace '{$workspace->name}' has been suspended. Reason logged to audit.");
    }

    public function reinstateWorkspace(Workspace $workspace, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $sub = $workspace->currentSubscription ?? $workspace->subscriptions()->latest()->first();

        if ($sub) {
            $updateData = ['status' => 'active', 'canceled_at' => null];
            if (!$sub->current_period_ends_at || $sub->current_period_ends_at->isPast()) {
                $updateData['current_period_ends_at'] = now()->addMonth();
            }
            $sub->update($updateData);
        }

        AuditService::log('admin.workspace_reinstated', $workspace, [
            'previous_status' => 'suspended',
        ], [
            'status' => 'active',
            'reason' => $request->reason,
            'actor' => auth()->user()->email,
        ], $workspace->id);

        return back()->with('success', "Workspace '{$workspace->name}' reinstated to Active status.");
    }

    public function deleteWorkspace(Workspace $workspace, Request $request)
    {
        if (!auth()->user()->isSuperAdmin() || auth()->user()->getAdminRole() !== 'super_admin') {
            abort(403, 'Permission denied: Deleting a tenant workspace requires Super Admin role.');
        }

        $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $name = $workspace->name;
        $id = $workspace->id;

        DB::transaction(function () use ($workspace, $request, $name, $id) {
            Subscription::where('workspace_id', $id)->delete();
            SubscriptionAlert::where('workspace_id', $id)->delete();
            Payment::withoutGlobalScopes()->where('workspace_id', $id)->delete();
            Invoice::withoutGlobalScopes()->where('workspace_id', $id)->delete();
            User::where('workspace_id', $id)->delete();
            $workspace->delete();

            AuditService::log('admin.workspace_deleted', null, null, [
                'workspace_id' => $id,
                'name' => $name,
                'reason' => $request->reason,
                'actor' => auth()->user()->email,
            ]);
        });

        return redirect()->route('admin.workspaces.index')->with('success', "Workspace '{$name}' permanently purged from platform.");
    }

    public function users(Request $request)
    {
        $query = User::with('workspace');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            if ($request->role === 'super_admin') {
                $query->where(function ($q) {
                    $q->where('is_super_admin', true)->orWhereNotNull('admin_role');
                });
            } else {
                $query->where('role', $request->role);
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->load('workspace');
        $loginHistory = LoginHistory::where('user_id', $user->id)->latest('created_at')->limit(15)->get();
        $auditLogs = AuditLog::where('user_id', $user->id)->latest('created_at')->limit(15)->get();

        return view('admin.users.show', compact('user', 'loginHistory', 'auditLogs'));
    }

    /*
    |--------------------------------------------------------------------------
    | ASSISTED ACCESS / IMPERSONATION (Section 11)
    |--------------------------------------------------------------------------
    */
    public function startImpersonation(Workspace $workspace, Request $request)
    {
        $operator = auth()->user();

        // Must have permission
        if (!$operator->isSuperAdmin() || ($operator->getAdminRole() !== 'super_admin' && $operator->getAdminRole() !== 'support_admin')) {
            abort(403, 'Permission denied: Assisted workspace impersonation requires Support Admin or Super Admin role.');
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $targetUser = $workspace->users()->first();

        $log = ImpersonationLog::create([
            'operator_user_id' => $operator->id,
            'target_workspace_id' => $workspace->id,
            'target_user_id' => $targetUser?->id,
            'reason' => $request->reason,
            'ip_address' => $request->ip(),
            'started_at' => now(),
        ]);

        session([
            'super_admin_assisted_session' => true,
            'assisted_workspace_id' => $workspace->id,
            'assisted_workspace_name' => $workspace->name,
            'assisted_log_id' => $log->id,
            'assisted_operator_email' => $operator->email,
            'assisted_started_at' => now()->timestamp,
        ]);

        AuditService::log('admin.impersonation_started', $workspace, null, [
            'reason' => $request->reason,
            'target_workspace' => $workspace->name,
            'target_user' => $targetUser?->email,
        ], $workspace->id);

        return redirect()->route('dashboard')->with('info', "Assisted session active for {$workspace->name}. You are viewing tenant data under privileged support oversight.");
    }

    public function stopImpersonation(Request $request)
    {
        $logId = session('assisted_log_id');
        $workspaceId = session('assisted_workspace_id');

        if ($logId) {
            $log = ImpersonationLog::find($logId);
            if ($log) {
                $log->update(['ended_at' => now()]);
            }
        }

        session()->forget([
            'super_admin_assisted_session',
            'assisted_workspace_id',
            'assisted_workspace_name',
            'assisted_log_id',
            'assisted_operator_email',
            'assisted_started_at',
        ]);

        AuditService::log('admin.impersonation_ended', null, null, [
            'workspace_id' => $workspaceId,
            'actor' => auth()->user()->email,
        ], $workspaceId);

        return redirect()->route('admin.workspaces.index')->with('success', 'Assisted support session closed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | FINANCIAL MANAGEMENT
    |--------------------------------------------------------------------------
    */
    public function revenue(Request $request)
    {
        $period = $request->input('period', 'all');
        $kpis = $this->metrics->getDashboardKpis($period);
        $analytics = $this->metrics->getRevenueAnalytics();

        $recentTransactions = Payment::withoutGlobalScopes()
            ->with(['invoice', 'recorder'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        return view('admin.finance.revenue', compact('kpis', 'analytics', 'recentTransactions', 'period'));
    }

    public function transactions(Request $request)
    {
        $query = Payment::withoutGlobalScopes()->with(['invoice', 'recorder']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$s}%")
                  ->orWhere('idempotency_key', 'like', "%{$s}%");
            });
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        return view('admin.finance.transactions', compact('payments'));
    }

    public function failedPayments(Request $request)
    {
        $payments = Payment::withoutGlobalScopes()
            ->where('status', 'failed')
            ->with('invoice')
            ->latest('payment_date')
            ->paginate(20);

        return view('admin.finance.failed-payments', compact('payments'));
    }

    public function refunds(Request $request)
    {
        $refunds = Payment::withoutGlobalScopes()
            ->where('status', 'refunded')
            ->with('invoice')
            ->latest('payment_date')
            ->paginate(20);

        return view('admin.finance.refunds', compact('refunds'));
    }

    public function processRefund(Payment $payment, Request $request)
    {
        // Must have permission
        if (!auth()->user()->isSuperAdmin() || (auth()->user()->getAdminRole() !== 'super_admin' && auth()->user()->getAdminRole() !== 'finance_admin')) {
            abort(403, 'Permission denied: Refund execution requires Finance Admin or Super Admin privileges.');
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($payment->status === 'refunded') {
            return back()->with('error', 'This payment has already been marked as refunded.');
        }

        $payment->update([
            'status' => 'refunded',
            'notes' => trim(($payment->notes ?? '') . "\nRefund reason: " . $request->reason . ' by ' . auth()->user()->email),
        ]);

        AuditService::log('admin.payment_refunded', $payment, [
            'status' => 'completed',
        ], [
            'status' => 'refunded',
            'reason' => $request->reason,
            'amount_cents' => $payment->amount_cents,
        ], $payment->workspace_id);

        return back()->with('success', "Payment {$payment->payment_number} marked as refunded. Reason logged.");
    }

    public function subscriptionInvoices(Request $request)
    {
        $subscriptions = Subscription::with(['workspace', 'plan'])
            ->latest()
            ->paginate(20);

        return view('admin.finance.invoices', compact('subscriptions'));
    }

    public function taxReports(Request $request)
    {
        $totalCollectedCents = (int) Payment::withoutGlobalScopes()->where('status', 'completed')->sum('amount_cents');
        $estimatedTaxCents = (int) round($totalCollectedCents * 0.05); // 5% standard VAT reference

        $monthlyBreakdown = DB::table('payments')
            ->select(DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"), DB::raw('SUM(amount_cents) as total_cents'), DB::raw('COUNT(*) as total_count'))
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.finance.tax-reports', compact('totalCollectedCents', 'estimatedTaxCents', 'monthlyBreakdown'));
    }

    /*
    |--------------------------------------------------------------------------
    | PLATFORM MANAGEMENT
    |--------------------------------------------------------------------------
    */
    public function features()
    {
        $features = [
            'enable_signups' => CmsContent::getVal('feature_enable_signups', '1') === '1',
            'enable_paypal' => CmsContent::getVal('feature_enable_paypal', '1') === '1',
            'enable_stripe' => CmsContent::getVal('feature_enable_stripe', '1') === '1',
            'enable_recurring' => CmsContent::getVal('feature_enable_recurring', '1') === '1',
            'enable_whitelabel' => CmsContent::getVal('feature_enable_whitelabel', '1') === '1',
            'maintenance_mode' => CmsContent::getVal('feature_maintenance_mode', '0') === '1',
        ];

        return view('admin.platform.features', compact('features'));
    }

    public function updateFeature(Request $request)
    {
        $feature = $request->validate([
            'key' => 'required|string',
            'value' => 'required|in:0,1',
        ]);

        CmsContent::setVal('feature_' . $feature['key'], $feature['value'], 'platform_features');

        AuditService::log('admin.feature_toggle', null, null, [
            'feature' => $feature['key'],
            'value' => $feature['value'],
        ]);

        return back()->with('success', "Feature '{$feature['key']}' status updated.");
    }

    public function usage()
    {
        $totalWorkspaces = Workspace::withoutGlobalScopes()->count();
        $totalInvoices = Invoice::withoutGlobalScopes()->count();
        $totalUsers = User::count();
        $totalPayments = Payment::withoutGlobalScopes()->count();

        return view('admin.platform.usage', compact('totalWorkspaces', 'totalInvoices', 'totalUsers', 'totalPayments'));
    }

    public function emailTemplates()
    {
        $templates = [
            ['name' => 'Invoice Client Notification', 'slug' => 'invoice-sent', 'subject' => 'New Invoice from {{ agency_name }}'],
            ['name' => 'Automated Payment Reminder', 'slug' => 'payment-reminder', 'subject' => 'Payment Reminder: Invoice #{{ invoice_number }}'],
            ['name' => 'Trial Expiration Notice', 'slug' => 'trial-expiring', 'subject' => 'Your Invoicy Free Trial is Expiring Soon'],
            ['name' => 'Admin Upgrade Request Alert', 'slug' => 'admin-upgrade', 'subject' => 'Subscription Plan Upgrade Request'],
        ];

        return view('admin.platform.emails', compact('templates'));
    }

    public function announcements()
    {
        $announcements = SystemAnnouncement::latest()->paginate(15);
        return view('admin.platform.announcements', compact('announcements'));
    }

    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'target_role' => 'required|in:all,owners,admins,staff',
            'is_active' => 'nullable|boolean',
        ]);

        SystemAnnouncement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'target_role' => $validated['target_role'],
            'is_active' => $request->boolean('is_active', true),
            'created_by_user_id' => auth()->id(),
        ]);

        AuditService::log('admin.announcement_created', null, null, ['title' => $validated['title']]);

        return back()->with('success', 'Platform announcement created successfully.');
    }

    public function toggleAnnouncement(SystemAnnouncement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        return back()->with('success', 'Announcement status toggled.');
    }

    public function deleteAnnouncement(SystemAnnouncement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPORT & MONITORING
    |--------------------------------------------------------------------------
    */
    public function supportTickets(Request $request)
    {
        $query = SupportTicket::with(['workspace', 'user', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('admin.support.tickets', compact('tickets'));
    }

    public function ticketShow(SupportTicket $ticket)
    {
        $ticket->load(['workspace', 'user', 'assignee', 'messages.user']);
        $admins = User::where('is_super_admin', true)->orWhereNotNull('admin_role')->get();

        return view('admin.support.ticket-show', compact('ticket', 'admins'));
    }

    public function ticketReply(SupportTicket $ticket, Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:2',
            'status' => 'nullable|string',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_admin_reply' => true,
            'message' => $request->message,
        ]);

        if ($request->filled('status')) {
            $ticket->update(['status' => $request->status]);
        }

        AuditService::log('admin.ticket_reply', $ticket, null, ['status' => $ticket->status]);

        return back()->with('success', 'Reply posted successfully.');
    }

    public function userFeedback(Request $request)
    {
        $feedback = UserFeedback::with(['workspace', 'user'])->latest()->paginate(15);
        return view('admin.support.feedback', compact('feedback'));
    }

    public function systemHealth()
    {
        $dbConnected = $this->checkDatabaseConnection();
        $dbVersion = 'Unknown';
        if ($dbConnected) {
            try {
                $dbVersion = DB::select("select version() as v")[0]->v ?? 'MySQL 8 / MariaDB';
            } catch (\Throwable $e) {}
        }

        $diskFreeBytes = @disk_free_space(base_path());
        $diskTotalBytes = @disk_total_space(base_path());
        $diskFreeGb = $diskFreeBytes ? round($diskFreeBytes / 1024 / 1024 / 1024, 2) : 0;
        $diskTotalGb = $diskTotalBytes ? round($diskTotalBytes / 1024 / 1024 / 1024, 2) : 0;

        $health = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connected' => $dbConnected,
            'database_version' => $dbVersion,
            'disk_free_gb' => $diskFreeGb,
            'disk_total_gb' => $diskTotalGb,
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'mail_driver' => config('mail.default'),
            'storage_writable' => is_writable(storage_path()),
        ];

        return view('admin.monitoring.health', compact('health'));
    }

    public function errorLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logLines = [];

        if (File::exists($logPath)) {
            $file = file($logPath);
            $slice = array_slice($file, -100);
            foreach ($slice as $line) {
                // Redact secrets
                $sanitized = preg_replace('/(password|secret|key|token)=["\']?[^"\'\s]+["\']?/i', '$1=REDACTED', $line);
                $logLines[] = $sanitized;
            }
        }

        return view('admin.monitoring.errors', compact('logLines'));
    }

    public function backgroundJobs()
    {
        return view('admin.monitoring.jobs');
    }

    public function integrationStatus()
    {
        return view('admin.monitoring.integrations');
    }

    /*
    |--------------------------------------------------------------------------
    | SECURITY & AUDIT
    |--------------------------------------------------------------------------
    */
    public function adminUsers(Request $request)
    {
        $admins = User::where('is_super_admin', true)
            ->orWhereNotNull('admin_role')
            ->latest()
            ->paginate(15);

        return view('admin.security.admins', compact('admins'));
    }

    public function updateAdminRole(User $user, Request $request)
    {
        if (auth()->user()->getAdminRole() !== 'super_admin') {
            abort(403, 'Permission denied: Only a Super Admin can change administrator roles.');
        }

        $validated = $request->validate([
            'admin_role' => 'nullable|string|in:super_admin,finance_admin,support_admin,technical_admin,none',
        ]);

        $newRole = $validated['admin_role'] === 'none' ? null : $validated['admin_role'];
        $isSuper = $newRole !== null;

        $user->update([
            'admin_role' => $newRole,
            'is_super_admin' => $isSuper,
        ]);

        AuditService::log('admin.role_updated', $user, null, [
            'admin_role' => $newRole,
            'is_super_admin' => $isSuper,
        ]);

        return back()->with('success', "Role for {$user->name} updated to " . ($newRole ?: 'Tenant User'));
    }

    public function loginHistory(Request $request)
    {
        $query = LoginHistory::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $logins = $query->latest('created_at')->paginate(25)->withQueryString();

        return view('admin.security.logins', compact('logins'));
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with(['user', 'workspace']);

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('action', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$s}%"));
            });
        }

        $auditLogs = $query->latest('created_at')->paginate(25)->withQueryString();

        return view('admin.security.audit-logs', compact('auditLogs'));
    }

    public function securityEvents(Request $request)
    {
        $suspiciousLogins = LoginHistory::where('status', 'failed')
            ->latest('created_at')
            ->limit(20)
            ->get();

        $impersonations = ImpersonationLog::with(['operator', 'targetWorkspace', 'targetUser'])
            ->latest('started_at')
            ->paginate(15);

        return view('admin.security.events', compact('suspiciousLogins', 'impersonations'));
    }

    public function webhookLogs()
    {
        return view('admin.security.webhooks');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */
    public function configGeneral()
    {
        $settings = [
            'app_name' => config('app.name', 'Invoicy'),
            'app_url' => config('app.url', 'https://invoicy.redmapleagency.com'),
            'support_email' => CmsContent::getVal('support_email', 'support@redmapleagency.com'),
            'default_currency' => CmsContent::getVal('default_currency', 'USD'),
            'default_language' => CmsContent::getVal('default_language', 'en'),
            'welcome_popup_enabled' => CmsContent::getVal('welcome_popup_enabled', '1'),
            'welcome_step1_title' => CmsContent::getVal('welcome_step1_title', '1. Create High-Precision Invoices in Seconds'),
            'welcome_step1_text' => CmsContent::getVal('welcome_step1_text', 'Click "+ New Invoice" in your sidebar or ask the AI Copilot. Enter client details or use Walk-in mode for instantaneous cash and retail billing without client signup.'),
            'welcome_step2_title' => CmsContent::getVal('welcome_step2_title', '2. 1-Click Industry Service Presets & Custom Line Items'),
            'welcome_step2_text' => CmsContent::getVal('welcome_step2_text', 'Access 45+ tailored presets for your business category (Computer Shop, Transportation, Limousine Taxi, Industrial, Construction, Distributor, Marketing Agency). 1-click adds pre-priced line items with matching units.'),
            'welcome_step3_title' => CmsContent::getVal('welcome_step3_title', '3. Private Draft Invoices & Zero Accidental Dispatch'),
            'welcome_step3_text' => CmsContent::getVal('welcome_step3_text', 'Draft invoices are completely private. Emails are never dispatched, and PDF downloads, customer payment portals, and QR verification screens are disabled until you click "Issue & Publish".'),
            'welcome_step4_title' => CmsContent::getVal('welcome_step4_title', '4. Cryptographic QR Seals & Automated Reminders'),
            'welcome_step4_text' => CmsContent::getVal('welcome_step4_text', 'Published invoices feature authentic ISO 2D QR verification codes for fraud prevention and automated milestone payment reminder schedules (45, 30, 15, 7, 3, and 2 days before due date).'),
            'ai_agent_enabled' => CmsContent::getVal('ai_agent_enabled', '1'),
            'ai_provider' => CmsContent::getVal('ai_provider', 'auto'),
            'openai_api_key' => CmsContent::getVal('openai_api_key', ''),
            'gemini_api_key' => CmsContent::getVal('gemini_api_key', ''),
            'ai_model' => CmsContent::getVal('ai_model', 'gpt-4o-mini'),
            'ai_system_prompt' => CmsContent::getVal('ai_system_prompt', ''),
        ];
        return view('admin.config.general', compact('settings'));
    }

    public function configGateways()
    {
        return view('admin.config.gateways');
    }

    public function configSmtp()
    {
        return view('admin.config.smtp');
    }

    public function configStorage()
    {
        return view('admin.config.storage');
    }

    public function configTaxes()
    {
        return view('admin.config.taxes');
    }

    public function configBranding()
    {
        return view('admin.config.branding');
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string',
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $val) {
            CmsContent::setVal($key, (string) $val, $validated['group']);
        }

        AuditService::log('admin.config_updated', null, null, [
            'group' => $validated['group'],
            'actor' => auth()->user()->email,
        ]);

        return back()->with('success', 'System configuration saved successfully.');
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
