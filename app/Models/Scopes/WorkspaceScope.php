<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WorkspaceScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Enforces fail-closed multi-tenant data isolation.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Allow Super Admin routes to query across workspaces without restriction
        if (request() && (request()->is('admin') || request()->is('admin/*'))) {
            return;
        }

        // 2. Allow CLI commands (e.g. queue workers, seeders) only when running in non-testing console
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // 3. If an authenticated tenant user is present with a valid workspace, scope strictly to their workspace
        if (Auth::check() && Auth::user()->workspace_id) {
            $builder->where($model->qualifyColumn('workspace_id'), Auth::user()->workspace_id);
            return;
        }

        // 4. FAIL-CLOSED: If unauthenticated, or user has no workspace_id, block all records!
        // This guarantees that any unauthorized query returns 0 records (triggering 404 ModelNotFoundException)
        // rather than failing open and leaking other tenants' data.
        $builder->whereRaw('0 = 1');
    }
}
