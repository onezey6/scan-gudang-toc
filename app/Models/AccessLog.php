<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'event_type',
        'ip_address',
        'device_browser',
        'user_agent',
        'description',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $eventType,
        string $description,
        ?User $user = null,
        ?Request $request = null,
        ?string $attemptedLogin = null
    ): self {
        $req = $request ?? request();
        $ua = (string) ($req?->userAgent() ?? 'Unknown');

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? ($attemptedLogin ?: 'Tamu / Tidak Dikenal'),
            'user_email' => $user?->email ?? $attemptedLogin,
            'user_role' => $user?->role ?? '-',
            'event_type' => $eventType,
            'ip_address' => $req?->ip() ?? '127.0.0.1',
            'device_browser' => self::parseUserAgent($ua),
            'user_agent' => $ua,
            'description' => $description,
            'accessed_at' => now(),
        ]);
    }

    public static function parseUserAgent(string $ua): string
    {
        $browser = 'Browser';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Microsoft Edge';
        } elseif (str_contains($ua, 'Chrome/')) {
            $browser = 'Google Chrome';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Mozilla Firefox';
        } elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) {
            $browser = 'Apple Safari';
        }

        $os = 'OS';
        if (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android Mobile';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS Mobile';
        } elseif (str_contains($ua, 'Macintosh')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        }

        return "{$browser} • {$os}";
    }
}
