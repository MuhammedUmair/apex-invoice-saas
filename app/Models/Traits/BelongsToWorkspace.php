<?php

namespace App\Models\Traits;

use App\Models\Scopes\WorkspaceScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

trait BelongsToWorkspace
{
    /**
     * Boot the BelongsToWorkspace trait.
     */
    protected static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope());

        static::creating(function ($model) {
            // Automatically assign authenticated user's workspace if not explicitly provided
            if (empty($model->workspace_id) && Auth::check() && Auth::user()->workspace_id) {
                $model->workspace_id = Auth::user()->workspace_id;
            }

            // Non-null multi-tenant integrity check: workspace_id must NEVER be null
            if (empty($model->workspace_id)) {
                throw new InvalidArgumentException(
                    "Multi-tenant integrity violation: Cannot save [" . get_class($model) . "] without a non-null workspace_id."
                );
            }
        });

        // Anti-IDOR Guard: Block cross-tenant update attempts
        static::updating(function ($model) {
            if (request() && (request()->is('admin') || request()->is('admin/*'))) {
                return;
            }

            if (Auth::check() && Auth::user()->workspace_id) {
                if (!empty($model->workspace_id) && $model->workspace_id !== Auth::user()->workspace_id) {
                    abort(403, 'Security Violation: Cross-tenant data tampering is strictly prohibited.');
                }
            }
        });

        // Anti-IDOR Guard: Block cross-tenant deletion attempts
        static::deleting(function ($model) {
            if (request() && (request()->is('admin') || request()->is('admin/*'))) {
                return;
            }

            if (Auth::check() && Auth::user()->workspace_id) {
                if (!empty($model->workspace_id) && $model->workspace_id !== Auth::user()->workspace_id) {
                    abort(403, 'Security Violation: Cross-tenant record deletion is strictly prohibited.');
                }
            }
        });
    }

    /**
     * Workspace relationship.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
