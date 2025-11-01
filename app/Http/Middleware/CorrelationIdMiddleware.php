<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorrelationIdMiddleware
{
    public const HEADER = 'X-Correlation-ID';

    public function handle(Request $request, Closure $next)
    {
        $correlationId = $request->headers->get(self::HEADER);

        if (! is_string($correlationId) || trim($correlationId) === '') {
            $correlationId = (string) Str::uuid();
            $request->headers->set(self::HEADER, $correlationId);
        }

        $request->attributes->set(self::HEADER, $correlationId);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $response->headers->set(self::HEADER, $correlationId);

        return $response;
    }
}
