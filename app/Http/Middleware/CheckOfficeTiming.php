<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOfficeTiming
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('office_closed') || $request->routeIs('logout') || $request->routeIs('login') || $request->is('/')) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        $now = \Carbon\Carbon::now('Asia/Kolkata');
        $currentDay = $now->format('l');
        $currentTime = $now->format('H:i:s');

        $timing = \App\Models\OfficeTiming::where('day_of_week', $currentDay)->first();

        if ($timing) {
            if (!$timing->is_working_day) {
                return redirect()->route('office_closed');
            }

            if ($timing->start_time && $timing->end_time) {
                if ($currentTime < $timing->start_time || $currentTime > $timing->end_time) {
                    return redirect()->route('office_closed');
                }
            }
        }

        return $next($request);
    }
}
