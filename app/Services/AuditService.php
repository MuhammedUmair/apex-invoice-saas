<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Record an immutable audit log entry.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $workspaceId = null,
        ?int $userId = null
    ): AuditLog {
        $user = Auth::user();
        $finalUserId = $userId ?? ($user ? $user->id : null);
        $finalWorkspaceId = $workspaceId ?? ($user ? $user->workspace_id : ($auditable && isset($auditable->workspace_id) ? $auditable->workspace_id : null));

        return AuditLog::create([
            'workspace_id' => $finalWorkspaceId,
            'user_id' => $finalUserId,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? (string) $auditable->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? 'System', 0, 500),
            'created_at' => now(),
        ]);
    }
}
