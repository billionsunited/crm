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

        // Allow unauthenticated users to proceed so they can reach the login page
        if (!auth()->check()) {
            return $next($request);
        }

        // Allow admins to bypass the time check
        if (auth()->user()->isAdmin()) {
            return $next($request);
        }

        $now = \Carbon\Carbon::now('Asia/Kolkata');
        $currentDay = $now->format('l');
        $currentTime = $now->format('H:i:s');

        $timing = \App\Models\OfficeTiming::where('day_of_week', $currentDay)->first();

        if ($timing) {
            // Check if today is marked as a non-working day
            if (empty($timing->is_working_day) || $timing->is_working_day == 0 || $timing->is_working_day == false) {
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
