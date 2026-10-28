<?php

use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingsController;
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

    if (str_starts_with($host, 'invoicy.')) {
        if (Auth::check()) {
            return redirect()->route('dashboard');
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
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');

    // Password Reset with brute-force / email-bomb throttling
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

// Logout accepts both GET and POST to prevent 405 Method Not Allowed
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

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
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoices.send');
    Route::post('/invoices/{invoice}/reminder', [InvoiceController::class, 'sendReminder'])->name('invoices.reminder');

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

    // SaaS Membership Packages & Checkout
    Route::get('/membership', function () {
        return view('membership.index');
    })->name('membership.index');

    // Workspace Settings & Branding Customizer
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/smtp/test', [SettingsController::class, 'testSmtp'])->middleware('throttle:6,1')->name('settings.smtp.test');

    // Monthly Recurring Invoicing & Retainers (Item 18)
    Route::get('/recurring-invoices', [RecurringInvoiceController::class, 'index'])->name('recurring.index');
    Route::post('/recurring-invoices', [RecurringInvoiceController::class, 'store'])->name('recurring.store');
    Route::post('/recurring-invoices/{recurring}/approve', [RecurringInvoiceController::class, 'approveAndSend'])->name('recurring.approve');
    Route::post('/recurring-invoices/{recurring}/toggle', [RecurringInvoiceController::class, 'toggleStatus'])->name('recurring.toggle');

    // Website & Docs CMS with Auto-Translate (Items 12 & 13)
    Route::get('/admin/cms', [CmsController::class, 'index'])->name('cms.index');
    Route::post('/admin/cms', [CmsController::class, 'update'])->name('cms.update');
    Route::post('/admin/cms/docs', [CmsController::class, 'storeDocArticle'])->name('cms.docs.store');
    Route::post('/admin/cms/docs/{article}', [CmsController::class, 'updateDocArticle'])->name('cms.docs.update');
    Route::post('/admin/cms/docs/{article}/translate', [CmsController::class, 'autoTranslateDoc'])->name('cms.docs.translate');

    // Super Admin Subscriptions & Plans Manager (Item 14)
    Route::get('/admin/subscriptions', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/admin/subscriptions/plans/{plan}', [AdminSubscriptionController::class, 'updatePlan'])->name('admin.plans.update');
    Route::post('/admin/subscriptions/{workspace}/renew', [AdminSubscriptionController::class, 'renewWorkspace'])->name('admin.workspaces.renew');
    Route::post('/admin/subscriptions/{workspace}/toggle', [AdminSubscriptionController::class, 'toggleWorkspaceStatus'])->name('admin.workspaces.toggle');

    // Promotional Coupons & Offers Designer (Items 4 & 5)
    Route::get('/admin/coupons', [\App\Http\Controllers\AdminCouponController::class, 'index'])->name('admin.coupons.index');
    Route::post('/admin/coupons', [\App\Http\Controllers\AdminCouponController::class, 'store'])->name('admin.coupons.store');
    Route::post('/admin/coupons/{coupon}/toggle', [\App\Http\Controllers\AdminCouponController::class, 'toggle'])->name('admin.coupons.toggle');
    Route::delete('/admin/coupons/{coupon}', [\App\Http\Controllers\AdminCouponController::class, 'destroy'])->name('admin.coupons.destroy');
});

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

/*
|--------------------------------------------------------------------------
| Webhook Endpoints (Stripe & PayPal)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPal'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
