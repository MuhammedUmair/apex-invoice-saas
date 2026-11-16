<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'workspace_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'is_super_admin',
        'admin_role',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']) || $this->isSuperAdmin();
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->email_verified_at);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isSuperAdmin(): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (!empty($this->admin_role) && in_array($this->admin_role, ['super_admin', 'finance_admin', 'support_admin', 'technical_admin'])) {
            return true;
        }

        $superAdminEmails = [
            'superadmin@redmapleagency.com',
            'superadmin@redmaplestudio.com',
            'admin@redmapleagency.com',
            'admin@redmaplestudio.com',
        ];

        return in_array(strtolower($this->email), array_map('strtolower', $superAdminEmails));
    }

    public function getAdminRole(): ?string
    {
        if (!empty($this->admin_role)) {
            return $this->admin_role;
        }

        if ($this->isSuperAdmin()) {
            return 'super_admin';
        }

        return null;
    }

    public function getAdminRoleLabel(): string
    {
        return match ($this->getAdminRole()) {
            'super_admin' => 'Super Admin',
            'finance_admin' => 'Finance Admin',
            'support_admin' => 'Support Admin',
            'technical_admin' => 'Technical Admin',
            default => 'Tenant User',
        };
    }

    public function hasAdminPermission(string $permission): bool
    {
        $role = $this->getAdminRole();
        if (!$role) {
            return false;
        }

        if ($role === 'super_admin') {
            return true;
        }

        return match ($permission) {
            'finance', 'revenue', 'transactions', 'refunds', 'tax_reports' => in_array($role, ['finance_admin']),
            'subscriptions', 'plans', 'coupons' => in_array($role, ['finance_admin']),
            'support', 'tickets', 'feedback', 'workspaces_view' => in_array($role, ['support_admin', 'technical_admin']),
            'impersonation' => in_array($role, ['support_admin']),
            'monitoring', 'health', 'errors', 'jobs', 'integrations', 'webhooks', 'announcements' => in_array($role, ['technical_admin']),
            'config' => in_array($role, ['technical_admin']),
            default => false,
        };
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function isTrial(): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        return $this->workspace ? $this->workspace->isTrial() : true;
    }
}
