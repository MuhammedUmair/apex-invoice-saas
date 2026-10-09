<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $workspace = Auth::user()->workspace;

        $totalRevenueCents = Payment::where('status', 'completed')->sum('amount_cents');
        $outstandingDueCents = Invoice::whereIn('status', ['issued', 'sent', 'partially_paid', 'overdue'])->sum('due_cents');
        $invoicesCount = Invoice::count();
        $clientsCount = Client::count();

        $recentInvoices = Invoice::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['invoice', 'client'])
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        $subscription = $workspace->currentSubscription;

        return view('dashboard', [
            'workspace' => $workspace,
            'totalRevenueCents' => $totalRevenueCents,
            'outstandingDueCents' => $outstandingDueCents,
            'invoicesCount' => $invoicesCount,
            'clientsCount' => $clientsCount,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'subscription' => $subscription,
        ]);
    }
}
