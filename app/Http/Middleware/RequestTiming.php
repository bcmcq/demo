<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestTiming
{
    /**
     * Threshold in milliseconds above which a request is considered slow.
     */
    private const SLOW_REQUEST_THRESHOLD_MS = 500;

    /**
     * Query count above which a request is flagged as query-heavy.
     */
    private const HIGH_QUERY_COUNT_THRESHOLD = 10;

    /**
     * Handle an incoming request.
     *
     * Captures timing, query count, and memory metrics. Adds performance
     * headers to the response and logs structured JSON for observability.
     * Skips all profiling when disabled via config('app.request_timing').
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.request_timing')) {
            return $next($request);
        }

        DB::enableQueryLog();
        $startTime = hrtime(true);

        $response = $next($request);

        $durationMs = (hrtime(true) - $startTime) / 1_000_000;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        DB::flushQueryLog();

        $queryCount = count($queries);
        $dbTimeMs = collect($queries)->sum('time');
        $memoryPeakMb = round(memory_get_peak_usage(true) / 1_048_576, 2);

        $response->headers->set('X-Request-Duration-Ms', round($durationMs, 2));
        $response->headers->set('X-Query-Count', $queryCount);
        $response->headers->set('X-DB-Time-Ms', round($dbTimeMs, 2));

        $this->logRequest($request, $response, $durationMs, $queryCount, $dbTimeMs, $memoryPeakMb);

        return $response;
    }

    /**
     * Log structured request metrics.
     *
     * Uses warning level for slow requests or high query counts
     * to create a natural alert system in log monitoring.
     */
    private function logRequest(
        Request $request,
        Response $response,
        float $durationMs,
        int $queryCount,
        float $dbTimeMs,
        float $memoryPeakMb,
    ): void {
        $context = [
            'method' => $request->method(),
            'uri' => $request->getPathInfo(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($durationMs, 2),
            'query_count' => $queryCount,
            'db_time_ms' => round($dbTimeMs, 2),
            'memory_peak_mb' => $memoryPeakMb,
        ];

        $isSlow = $durationMs > self::SLOW_REQUEST_THRESHOLD_MS
            || $queryCount > self::HIGH_QUERY_COUNT_THRESHOLD;

        if ($isSlow) {
            Log::warning('Slow API request', $context);
        } else {
            Log::info('API request', $context);
        }
    }
}
