<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Protected Tenant / Agency Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Invoice Management
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

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
});

/*
|--------------------------------------------------------------------------
| Public Customer Portal & Secure Document Routes
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/p')->group(function () {
    // Interactive customer viewing & payment portal
    Route::get('/{token}', [InvoiceController::class, 'customerPortal'])->name('invoices.portal');
    Route::post('/{token}/pay', [PaymentController::class, 'processPortalPayment'])->name('invoices.pay');

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
