<?php

namespace App\Http\Middleware;

use App\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Track activity after the response is sent
        if (Auth::check()) {
            // Round to nearest hour for activity tracking
            $activeAt = now()->startOfHour();

            // Use firstOrCreate to prevent duplicate key errors
            UserActivity::firstOrCreate([
                'user_id' => Auth::id(),
                'active_at' => $activeAt,
            ]);
        }

        return $next($request);
    }
}
