<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request, Application $app): JsonResponse
    {
        $status = 'ok';
        $httpStatus = 200;
        $startTime = defined('LARAVEL_START')
            ? (float) LARAVEL_START
            : (float) $request->server('REQUEST_TIME_FLOAT', microtime(true));
        $uptimeSeconds = max(0, (int) (microtime(true) - $startTime));

        $details = [
            'status' => 'ok',
            'uptime' => sprintf('%02d:%02d:%02d', intdiv($uptimeSeconds, 3600), intdiv($uptimeSeconds, 60) % 60, $uptimeSeconds % 60),
            'db' => 'ok',
            'redis' => 'ok',
        ];

        try {
            DB::connection()->select('select 1');
        } catch (Throwable $exception) {
            $status = 'error';
            $details['db'] = 'error';
            $details['error'] = $exception->getMessage();
            $httpStatus = 503;
        }

        if ($status === 'ok') {
            try {
                Redis::connection()->ping();
            } catch (Throwable $exception) {
                $status = 'error';
                $details['redis'] = 'error';
                $details['error'] = $exception->getMessage();
                $httpStatus = 503;
            }
        }

        $details['status'] = $status;

        return response()->json($details, $httpStatus);
    }
}
