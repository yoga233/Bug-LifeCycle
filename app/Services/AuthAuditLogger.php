<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthAuditLogger
{
    public static function write(
        string $event,
        ?User $user = null,
        ?string $email = null,
        array $context = [],
        ?Request $request = null,
    ): void {
        $request ??= request();

        $baseContext = [
            'event' => $event,
            'user_id' => $user?->id,
            'email' => $email ?? $user?->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => ($request && $request->hasSession()) ? $request->session()->getId() : null,
            'occurred_at' => now()->toIso8601String(),
        ];

        if ($user && method_exists($user, 'getRoleNames')) {
            $baseContext['roles'] = $user->getRoleNames()->values()->all();
        }

        $payload = array_merge($baseContext, $context);

        try {
            Log::channel(config('auth.audit_log_channel', 'auth_audit'))
                ->info('auth.'.$event, $payload);
        } catch (\Throwable) {
            Log::info('auth.'.$event, $payload);
        }
    }
}
