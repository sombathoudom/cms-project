<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'error' => [
                    'code' => 'ERR_UNAUTHENTICATED',
                    'message' => 'Authentication required.',
                ],
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
