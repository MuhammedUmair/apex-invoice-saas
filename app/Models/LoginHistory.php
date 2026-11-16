<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'device',
        'status',
        'failure_reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $email, ?User $user, string $status = 'successful', ?string $failureReason = null, ?string $ip = null, ?string $userAgent = null): self
    {
        $ip = $ip ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();
        
        $device = 'Desktop';
        if ($userAgent) {
            if (preg_match('/(iPhone|Android|Mobile)/i', $userAgent)) {
                $device = 'Mobile';
            } elseif (preg_match('/(iPad|Tablet)/i', $userAgent)) {
                $device = 'Tablet';
            }
        }

        return self::create([
            'user_id' => $user?->id,
            'email' => strtolower(trim($email)),
            'ip_address' => $ip,
            'user_agent' => substr($userAgent ?? '', 0, 500),
            'device' => $device,
            'status' => $status,
            'failure_reason' => $failureReason,
            'created_at' => now(),
        ]);
    }
}
