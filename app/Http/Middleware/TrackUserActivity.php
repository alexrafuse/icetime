<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! Auth::check()) {
            return;
        }

        try {
            // Round to nearest hour for activity tracking
            $activeAt = now()->startOfHour();

            // Use firstOrCreate to prevent duplicate key errors
            UserActivity::firstOrCreate([
                'user_id' => Auth::id(),
                'active_at' => $activeAt,
            ]);
        } catch (Throwable $e) {
            // Log the error but don't throw - tracking failures shouldn't affect user experience
            Log::error('Failed to track user activity', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
