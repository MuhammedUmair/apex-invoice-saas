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
    }

    /**
     * Get the workspace that owns the model.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
