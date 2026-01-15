<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'last_activity',
        'is_active',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a device fingerprint from user agent and IP
     */
    public static function generateFingerprint($userAgent, $ip)
    {
        return hash('sha256', $userAgent . $ip);
    }

    /**
     * Check if this is a new device for the user
     */
    public static function isNewDevice($userId, $fingerprint)
    {
        return !self::where('user_id', $userId)
            ->where('device_fingerprint', $fingerprint)
            ->exists();
    }
}
