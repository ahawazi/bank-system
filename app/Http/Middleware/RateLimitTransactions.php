<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->user()->id;
        $key = "transactions:rate_limit:{$userId}";
        $limit = 2;
        $timeFrame = 5;

        $transactions = Cache::get($key, 0);

        if ($transactions >= $limit) {
            return response()->json(['message' => 'Rate limit exceeded. Only 2 transactions allowed per 5 seconds.'], 429);
        }

        Cache::put($key, $transactions + 1, $timeFrame);

        return $next($request);
    }
}
