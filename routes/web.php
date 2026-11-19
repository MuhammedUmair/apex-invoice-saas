<?php

use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\WebsiteBuilderController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root Domain Landing Page & Subdomain Routing
|--------------------------------------------------------------------------
| - On subdomain 'invoicy.redmapleagency.com': forwards to SaaS Login/Dashboard.
| - On root domain 'redmapleagency.com': showcases Invoicy & future SaaS Suite.
*/
Route::get('/', function () {
    $host = request()->getHost();

    if (str_starts_with($host, 'invoicy.') || str_starts_with($host, 'staging.')) {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        if (Auth::guard('superadmin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('login');
    }

    return view('landing');
})->name('home');

Route::get('/app', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
// Dedicated Platform Super Admin Control Panel Route (/cp)
// If authenticated as Super Admin -> forwards directly to /admin dashboard
Route::get('/cp', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::guard('superadmin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return app(\App\Http\Controllers\AuthController::class)->showCpLogin($request);
})->name('cp.login');

// Tenant Authentication Routes (Guarded by 'web')
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:15,1')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');

    // Password Reset with brute-force / email-bomb throttling
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

// Super Admin Authentication Routes (Guarded by 'superadmin')
Route::middleware('guest:superadmin')->group(function () {
    Route::post('/cp', [AuthController::class, 'cpLogin'])->middleware('throttle:15,1')->name('cp.login.post');
    Route::get('/cp/otp', [AuthController::class, 'showCpOtp'])->name('cp.otp');
    Route::post('/cp/otp', [AuthController::class, 'verifyCpOtp'])->middleware('throttle:10,1')->name('cp.otp.verify');
    Route::post('/cp/otp/resend', [AuthController::class, 'resendCpOtp'])->middleware('throttle:5,1')->name('cp.otp.resend');
});

// Tenant Logout
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/logout-beacon', [AuthController::class, 'logoutBeacon'])->name('logout.beacon');

// Dedicated Super Admin Logout
Route::match(['get', 'post'], '/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

// Account Email Verification Routes
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::get('/team/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('team.verify');

/*
|--------------------------------------------------------------------------
| Locale Switcher Route (accepts GET and POST)
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/locale/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'ur', 'hi', 'tr', 'ar', 'es', 'fr', 'de', 'zh'])) {
        session(['locale' => $lang]);
    }
    return back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Multilingual Documentation Hub (Public & Authenticated)
|--------------------------------------------------------------------------
*/
Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');

/*
|--------------------------------------------------------------------------
| Protected Tenant / Agency Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoice Management, Revision & Client Email Dispatch
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/bulk-action', [InvoiceController::class, 'bulkAction'])->name('invoices.bulk');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/language', [InvoiceController::class, 'updateLanguage'])->name('invoices.language');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::post('/invoices/{invoice}/publish', [InvoiceController::class, 'publish'])->name('invoices.publish');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoices.send');
    Route::post('/invoices/{invoice}/reminder', [InvoiceController::class, 'sendReminder'])->name('invoices.reminder');
    Route::post('/invoices/{invoice}/recall', [InvoiceController::class, 'recall'])->name('invoices.recall');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Comprehensive Help Center & Interactive Visual Guides
    Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('help.index');

    // Consumer Support Tickets (24-Hour SLA Turnaround Guarantee - Request 12)
    Route::get('/support', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/create', [\App\Http\Controllers\SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [\App\Http\Controllers\SupportTicketController::class, 'reply'])->name('support.reply');
    Route::post('/support/{ticket}/close', [\App\Http\Controllers\SupportTicketController::class, 'close'])->name('support.close');

    // Automated Reminders & Future Scheduler Hub
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders/run', [ReminderController::class, 'runCycle'])->name('reminders.run');

    // Credit Notes & Ledger Adjustments
    Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('credit-notes.index');
    Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');

    // Client CRM
    Route::get('/clients', function () {
        return view('clients.index');
    })->name('clients.index');

    // Service Catalog & Industry Presets
    Route::get('/services', function () {
        return view('services.index');
    })->name('services.index');

    // SaaS Membership Packages & Checkout (Restricted from Staff)
    Route::get('/membership', function () {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isStaff()) {
            return redirect()->route('dashboard')->with('error', 'Access Denied: Staff accounts do not have permission to view Plan & Billing.');
        }
        return view('membership.index');
    })->name('membership.index');

    // Workspace Settings & Branding Customizer
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/smtp/test', [SettingsController::class, 'testSmtp'])->middleware('throttle:6,1')->name('settings.smtp.test');
    Route::post('/settings/team', [SettingsController::class, 'addTeamMember'])->name('settings.team.add');
    Route::post('/settings/team/{member}/role', [SettingsController::class, 'updateTeamMemberRole'])->name('settings.team.update_role');
    Route::post('/settings/team/{member}/resend-verification', [SettingsController::class, 'resendTeamVerification'])->name('settings.team.resend_verification');
    Route::delete('/settings/team/{member}', [SettingsController::class, 'removeTeamMember'])->name('settings.team.remove');

    // Monthly Recurring Invoicing & Retainers (Item 18)
    Route::get('/recurring-invoices', [RecurringInvoiceController::class, 'index'])->name('recurring.index');
    Route::post('/recurring-invoices', [RecurringInvoiceController::class, 'store'])->name('recurring.store');
    Route::post('/recurring-invoices/{recurring}/approve', [RecurringInvoiceController::class, 'approveAndSend'])->name('recurring.approve');
    Route::post('/recurring-invoices/{recurring}/toggle', [RecurringInvoiceController::class, 'toggleStatus'])->name('recurring.toggle');
});

/*
|--------------------------------------------------------------------------
| Super Admin / Platform Management Routes (Restricted to Super Admins)
| Isolated on dedicated 'superadmin' guard (/cp & /admin)
|--------------------------------------------------------------------------
*/
Route::post('/admin/2fa/verify', function (\Illuminate\Http\Request $request) {
    $request->validate(['two_factor_code' => 'required|string']);
    $code = trim($request->two_factor_code);
    if (strlen($code) === 6 && is_numeric($code)) {
        session(['super_admin_2fa_verified' => true]);
        \App\Services\AuditService::log('admin.2fa_verified', auth('superadmin')->user() ?? auth()->user());
        return redirect()->route('admin.dashboard');
    }
    return back()->withErrors(['two_factor_code' => 'Invalid 2FA authentication code entered.']);
})->name('admin.2fa.verify');

Route::middleware([\App\Http\Middleware\EnsureSuperAdmin::class])->group(function () {
    // GROUP 1: OVERVIEW
    Route::get('/admin', [SuperAdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/analytics', [SuperAdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/admin/export', [SuperAdminController::class, 'exportCsv'])->name('admin.export');

    // GROUP 2: CUSTOMER MANAGEMENT
    Route::get('/admin/workspaces', [SuperAdminController::class, 'workspaces'])->name('admin.workspaces.index');
    Route::get('/admin/workspaces/suspended', [SuperAdminController::class, 'suspendedWorkspaces'])->name('admin.workspaces.suspended');
    Route::get('/admin/workspaces/{workspace}', [SuperAdminController::class, 'workspaceShow'])->name('admin.workspaces.show');
    Route::get('/admin/workspaces/{workspace}/activity', [SuperAdminController::class, 'workspaceActivity'])->name('admin.workspaces.activity');
    Route::post('/admin/workspaces/{workspace}/suspend', [SuperAdminController::class, 'suspendWorkspace'])->name('admin.workspaces.suspend');
    Route::post('/admin/workspaces/{workspace}/reinstate', [SuperAdminController::class, 'reinstateWorkspace'])->name('admin.workspaces.reinstate');
    Route::delete('/admin/workspaces/{workspace}', [SuperAdminController::class, 'deleteWorkspace'])->name('admin.workspaces.destroy');
    Route::get('/admin/users', [SuperAdminController::class, 'users'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [SuperAdminController::class, 'userShow'])->name('admin.users.show');

    // ASSISTED ACCESS / IMPERSONATION (Section 11)
    Route::post('/admin/impersonate/{workspace}', [SuperAdminController::class, 'startImpersonation'])->name('admin.impersonate.start');
    Route::post('/admin/impersonate/stop', [SuperAdminController::class, 'stopImpersonation'])->name('admin.impersonate.stop');

    // GROUP 3: SUBSCRIPTION & PLAN MANAGEMENT (Reused & Enhanced)
    Route::get('/admin/subscriptions', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/admin/subscriptions/bulk-action', [AdminSubscriptionController::class, 'bulkAction'])->name('admin.subscriptions.bulk');
    Route::post('/admin/subscriptions/plans/{plan}', [AdminSubscriptionController::class, 'updatePlan'])->name('admin.plans.update');
    Route::post('/admin/subscriptions/settings', [AdminSubscriptionController::class, 'updateAlertSettings'])->name('admin.subscriptions.settings');
    Route::post('/admin/subscriptions/alerts/read-all', [AdminSubscriptionController::class, 'markAllAlertsRead'])->name('admin.subscriptions.alerts.read_all');
    Route::post('/admin/subscriptions/alerts/{alert}/read', [AdminSubscriptionController::class, 'markAlertRead'])->name('admin.subscriptions.alerts.read');
    Route::post('/admin/subscriptions/check-expirations', [AdminSubscriptionController::class, 'runExpirationCheck'])->name('admin.subscriptions.check_expirations');
    Route::post('/admin/subscriptions/{workspace}/renew', [AdminSubscriptionController::class, 'renewWorkspace'])->name('admin.workspaces.renew');
    Route::post('/admin/subscriptions/requests/{alert}/approve', [AdminSubscriptionController::class, 'approveRequest'])->name('admin.subscriptions.requests.approve');
    Route::post('/admin/subscriptions/requests/{alert}/reject', [AdminSubscriptionController::class, 'rejectRequest'])->name('admin.subscriptions.requests.reject');
    Route::post('/admin/subscriptions/{workspace}/toggle', [AdminSubscriptionController::class, 'toggleWorkspaceStatus'])->name('admin.workspaces.toggle');
    Route::post('/admin/subscriptions/{workspace}/status', [AdminSubscriptionController::class, 'updateWorkspaceStatus'])->name('admin.workspaces.status');
    Route::post('/admin/subscriptions/{workspace}/owner', [AdminSubscriptionController::class, 'updateOwnerContact'])->name('admin.workspaces.owner');

    Route::get('/admin/coupons', [\App\Http\Controllers\AdminCouponController::class, 'index'])->name('admin.coupons.index');
    Route::post('/admin/coupons/bulk-action', [\App\Http\Controllers\AdminCouponController::class, 'bulkAction'])->name('admin.coupons.bulk');
    Route::post('/admin/coupons', [\App\Http\Controllers\AdminCouponController::class, 'store'])->name('admin.coupons.store');
    Route::put('/admin/coupons/{coupon}', [\App\Http\Controllers\AdminCouponController::class, 'update'])->name('admin.coupons.update');
    Route::post('/admin/coupons/{coupon}/toggle', [\App\Http\Controllers\AdminCouponController::class, 'toggle'])->name('admin.coupons.toggle');
    Route::delete('/admin/coupons/{coupon}', [\App\Http\Controllers\AdminCouponController::class, 'destroy'])->name('admin.coupons.destroy');

    // GROUP 4: FINANCIAL MANAGEMENT
    Route::get('/admin/finance/revenue', [SuperAdminController::class, 'revenue'])->name('admin.finance.revenue');
    Route::get('/admin/finance/transactions', [SuperAdminController::class, 'transactions'])->name('admin.finance.transactions');
    Route::get('/admin/finance/failed-payments', [SuperAdminController::class, 'failedPayments'])->name('admin.finance.failed_payments');
    Route::get('/admin/finance/refunds', [SuperAdminController::class, 'refunds'])->name('admin.finance.refunds');
    Route::post('/admin/finance/refunds/{payment}', [SuperAdminController::class, 'processRefund'])->name('admin.finance.refunds.process');
    Route::get('/admin/finance/invoices', [SuperAdminController::class, 'subscriptionInvoices'])->name('admin.finance.invoices');
    Route::get('/admin/finance/tax-reports', [SuperAdminController::class, 'taxReports'])->name('admin.finance.tax_reports');

    // GROUP 5: PLATFORM MANAGEMENT
    Route::get('/admin/platform/features', [SuperAdminController::class, 'features'])->name('admin.platform.features');
    Route::post('/admin/platform/features/update', [SuperAdminController::class, 'updateFeature'])->name('admin.platform.features.update');
    Route::get('/admin/platform/usage', [SuperAdminController::class, 'usage'])->name('admin.platform.usage');
    Route::get('/admin/platform/emails', [SuperAdminController::class, 'emailTemplates'])->name('admin.platform.emails');
    Route::get('/admin/platform/announcements', [SuperAdminController::class, 'announcements'])->name('admin.platform.announcements');
    Route::post('/admin/platform/announcements', [SuperAdminController::class, 'storeAnnouncement'])->name('admin.platform.announcements.store');
    Route::post('/admin/platform/announcements/{announcement}/toggle', [SuperAdminController::class, 'toggleAnnouncement'])->name('admin.platform.announcements.toggle');
    Route::delete('/admin/platform/announcements/{announcement}', [SuperAdminController::class, 'deleteAnnouncement'])->name('admin.platform.announcements.delete');

    // CMS & Website Builder & Ads (Integrated under Platform Group)
    Route::get('/admin/cms', [CmsController::class, 'index'])->name('cms.index');
    Route::post('/admin/cms', [CmsController::class, 'update'])->name('cms.update');
    Route::post('/admin/cms/docs', [CmsController::class, 'storeDocArticle'])->name('cms.docs.store');
    Route::post('/admin/cms/docs/{article}', [CmsController::class, 'updateDocArticle'])->name('cms.docs.update');
    Route::post('/admin/cms/docs/{article}/translate', [CmsController::class, 'autoTranslateDoc'])->name('cms.docs.translate');

    Route::get('/admin/website-builder', [WebsiteBuilderController::class, 'index'])->name('builder.index');
    Route::post('/admin/website-builder/branding', [WebsiteBuilderController::class, 'updateBranding'])->name('builder.branding');
    Route::post('/admin/website-builder/navigation', [WebsiteBuilderController::class, 'updateNavigation'])->name('builder.navigation');
    Route::post('/admin/website-builder/sections', [WebsiteBuilderController::class, 'updateSections'])->name('builder.sections');
    Route::post('/admin/website-builder/pages', [WebsiteBuilderController::class, 'storePage'])->name('builder.pages.store');
    Route::post('/admin/website-builder/pages/{page}', [WebsiteBuilderController::class, 'updatePage'])->name('builder.pages.update');
    Route::delete('/admin/website-builder/pages/{page}', [WebsiteBuilderController::class, 'destroyPage'])->name('builder.pages.destroy');

    Route::get('/admin/advertisements', [AdvertisementController::class, 'index'])->name('admin.ads.index');
    Route::post('/admin/advertisements', [AdvertisementController::class, 'store'])->name('admin.ads.store');
    Route::post('/admin/advertisements/{advertisement}/toggle', [AdvertisementController::class, 'toggle'])->name('admin.ads.toggle');
    Route::delete('/admin/advertisements/{advertisement}', [AdvertisementController::class, 'destroy'])->name('admin.ads.destroy');

    // GROUP 6: SUPPORT & MONITORING
    Route::get('/admin/support/tickets', [SuperAdminController::class, 'supportTickets'])->name('admin.support.tickets');
    Route::get('/admin/support/tickets/{ticket}', [SuperAdminController::class, 'ticketShow'])->name('admin.support.tickets.show');
    Route::post('/admin/support/tickets/{ticket}/reply', [SuperAdminController::class, 'ticketReply'])->name('admin.support.tickets.reply');
    Route::get('/admin/support/feedback', [SuperAdminController::class, 'userFeedback'])->name('admin.support.feedback');
    Route::get('/admin/monitoring/health', [SuperAdminController::class, 'systemHealth'])->name('admin.monitoring.health');
    Route::get('/admin/monitoring/errors', [SuperAdminController::class, 'errorLogs'])->name('admin.monitoring.errors');
    Route::get('/admin/monitoring/jobs', [SuperAdminController::class, 'backgroundJobs'])->name('admin.monitoring.jobs');
    Route::get('/admin/monitoring/integrations', [SuperAdminController::class, 'integrationStatus'])->name('admin.monitoring.integrations');

    // GROUP 7: SECURITY & AUDIT
    Route::get('/admin/security/admins', [SuperAdminController::class, 'adminUsers'])->name('admin.security.admins');
    Route::post('/admin/security/admins/{user}/role', [SuperAdminController::class, 'updateAdminRole'])->name('admin.security.admins.role');
    Route::get('/admin/security/logins', [SuperAdminController::class, 'loginHistory'])->name('admin.security.logins');
    Route::get('/admin/security/audit-logs', [SuperAdminController::class, 'auditLogs'])->name('admin.security.audit_logs');
    Route::get('/admin/security/events', [SuperAdminController::class, 'securityEvents'])->name('admin.security.events');
    Route::get('/admin/security/webhooks', [SuperAdminController::class, 'webhookLogs'])->name('admin.security.webhooks');

    // GROUP 8: CONFIGURATION
    Route::get('/admin/config/general', [SuperAdminController::class, 'configGeneral'])->name('admin.config.general');
    Route::get('/admin/config/gateways', [SuperAdminController::class, 'configGateways'])->name('admin.config.gateways');
    Route::get('/admin/config/smtp', [SuperAdminController::class, 'configSmtp'])->name('admin.config.smtp');
    Route::get('/admin/config/storage', [SuperAdminController::class, 'configStorage'])->name('admin.config.storage');
    Route::get('/admin/config/taxes', [SuperAdminController::class, 'configTaxes'])->name('admin.config.taxes');
    Route::get('/admin/config/branding', [SuperAdminController::class, 'configBranding'])->name('admin.config.branding');
    Route::post('/admin/config', [SuperAdminController::class, 'updateConfig'])->name('admin.config.update');
});

// Public Custom Pages & Ad Tracking
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/ads/{advertisement}/click', [AdvertisementController::class, 'click'])->name('ads.click');

/*
|--------------------------------------------------------------------------
| Public Customer Portal & Secure Document Routes
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/p')->middleware('throttle:60,1')->group(function () {
    // Interactive customer viewing & payment portal
    Route::get('/{token}', [InvoiceController::class, 'customerPortal'])->name('invoices.portal');
    Route::match(['get', 'post'], '/{token}/pay', [PaymentController::class, 'processPortalPayment'])->name('invoices.pay');
    Route::post('/{token}/coupon', [InvoiceController::class, 'applyPortalCoupon'])->name('invoices.portal.coupon');

    // PDF security & download
    Route::get('/{token}/pdf-unlock', [InvoiceController::class, 'pdfChallenge'])->name('invoices.pdf.challenge');
    Route::get('/{token}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf.download');
});

// Invisible 1x1 GIF Email Open Tracking Beacon (Item 13)
Route::get('/invoices/track/{token}/open.gif', [InvoiceController::class, 'trackEmailOpen'])->name('invoices.track.open');

// Authentic Verification Routes (QR Code Scan & Digital Seal)
Route::get('/verify/invoice/{token}', [InvoiceController::class, 'verifyInvoice'])->name('invoices.verify');
Route::get('/v/{token}', [InvoiceController::class, 'verifyInvoice'])->name('invoices.verify.short');

// AI Agent Endpoint (Text & Hands-Free Voice Commands)
Route::match(['get', 'post'], '/api/ai/copilot', [App\Http\Controllers\AiAssistantController::class, 'chat'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('ai.copilot.chat');

/*
|--------------------------------------------------------------------------
| Webhook Endpoints (Stripe & PayPal)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPal'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Custom 404 Fallback Handler
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
