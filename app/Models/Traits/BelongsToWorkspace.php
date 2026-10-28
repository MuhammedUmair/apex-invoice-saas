<?php

namespace App\Models\Traits;

use App\Models\Scopes\WorkspaceScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToWorkspace
{
    /**
     * Boot the BelongsToWorkspace trait.
     */
    protected static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope());

        static::creating(function ($model) {
            if (empty($model->workspace_id) && Auth::hasUser() && Auth::user()->workspace_id) {
                $model->workspace_id = Auth::user()->workspace_id;
            }
        });

        // Anti-IDOR Guard: Block cross-tenant update attempts
        static::updating(function ($model) {
            if (request()->is('admin') || request()->is('admin/*')) {
                return;
            }

            if (Auth::hasUser() && Auth::user()->workspace_id) {
                if (!empty($model->workspace_id) && $model->workspace_id !== Auth::user()->workspace_id) {
                    abort(403, 'Security Violation: Cross-tenant data tampering is strictly prohibited.');
                }
            }
        });

        // Anti-IDOR Guard: Block cross-tenant deletion attempts
        static::deleting(function ($model) {
            if (request()->is('admin') || request()->is('admin/*')) {
                return;
            }

            if (Auth::hasUser() && Auth::user()->workspace_id) {
                if (!empty($model->workspace_id) && $model->workspace_id !== Auth::user()->workspace_id) {
                    abort(403, 'Security Violation: Cross-tenant record deletion is strictly prohibited.');
                }
            }
        });
    }

    /**
     * Get the workspace that owns the model.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
