<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleForgotPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'forgot-password:' . $request->ip();

        // Maksimal 5 request per menit per IP
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Forgot password rate limit exceeded', [
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi dalam ' . $seconds . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60 detik

        return $next($request);
    }
} 