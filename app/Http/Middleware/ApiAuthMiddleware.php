<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = env('CRM_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: API configuration missing.'
            ], 500);
        }

        $requestApiKey = $request->header('X-API-KEY');

        if ($requestApiKey !== $apiKey) {
            \Illuminate\Support\Facades\Log::warning('Unauthorized API access attempt', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'provided_key' => $requestApiKey ? substr($requestApiKey, 0, 4) . '...' : 'none',
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing X-API-KEY header.'
            ], 401);
        }

        return $next($request);
    }
}
