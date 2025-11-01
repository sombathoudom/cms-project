<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $action, ?Model $target = null, array $context = []): AuditLog
    {
        /** @var Request|null $request */
        $request = App::runningInConsole() ? null : request();
        $actor = auth()->user();

        $safeContext = $this->sanitizeContext($context);

        $log = AuditLog::query()->create([
            'tenant_id' => $actor?->tenant_id ?? $target?->tenant_id ?? null,
            'actor_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'payload' => $safeContext,
        ]);

        Log::info('audit.log', [
            'correlation_id' => $request?->attributes->get('X-Correlation-ID') ?? (string) Str::uuid(),
            'actor_id' => $log->actor_id,
            'action' => $log->action,
            'target_type' => $log->target_type,
            'target_id' => $log->target_id,
        ]);

        return $log;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function sanitizeContext(array $context): array
    {
        $redactedKeys = ['password', 'password_confirmation', 'token', 'remember_token'];

        return collect($context)
            ->reject(fn ($value) => $value instanceof Authenticatable)
            ->map(function ($value) use ($redactedKeys) {
                if (is_array($value)) {
                    return Arr::map($value, fn ($nestedValue, $key) => in_array($key, $redactedKeys, true) ? 'REDACTED' : $nestedValue);
                }

                return $value;
            })
            ->toArray();
    }
}
