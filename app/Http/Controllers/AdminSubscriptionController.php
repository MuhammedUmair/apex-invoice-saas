<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function index()
    {
        $workspaces = Workspace::with(['users', 'subscriptions.plan'])
            ->latest()
            ->paginate(15);

        $plans = Plan::all();

        return view('admin.subscriptions.index', compact('workspaces', 'plans'));
    }

    public function updatePlan(Plan $plan, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_monthly_cents' => 'required|integer|min:0',
            'price_yearly_cents' => 'required|integer|min:0',
            'invoice_limit_monthly' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $plan->update($data);

        return back()->with('success', "Plan '{$plan->name}' successfully updated!");
    }

    public function renewWorkspace(Workspace $workspace, Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:36',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $months = (int) $request->duration_months;

        // Find or create subscription
        $sub = $workspace->subscriptions()->first();
        if ($sub) {
            $sub->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_period_ends_at' => now()->addMonths($months),
            ]);
        } else {
            Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $plan->id,
                'gateway' => 'manual_admin',
                'status' => 'active',
                'current_period_ends_at' => now()->addMonths($months),
            ]);
        }

        return back()->with('success', "Workspace '{$workspace->name}' renewed on {$plan->name} for {$months} month(s)!");
    }

    public function toggleWorkspaceStatus(Workspace $workspace)
    {
        $sub = $workspace->subscriptions()->first();
        if ($sub) {
            $newStatus = $sub->status === 'active' ? 'suspended' : 'active';
            $sub->update(['status' => $newStatus]);
            return back()->with('success', "Workspace status updated to {$newStatus}.");
        }

        return back()->with('error', "No subscription record found for workspace.");
    }
}
