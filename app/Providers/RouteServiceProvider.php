<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiting for login attempts - 5 per minute per IP + email
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower($request->input('email', ''));
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by($email),
            ];
        });

        // Strict rate limiting for registration - 3 per minute per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Very strict for password reset - 3 per minute per IP + email
        RateLimiter::for('password-reset', function (Request $request) {
            $email = strtolower($request->input('email', ''));
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perMinute(3)->by('reset:' . $email),
            ];
        });

        // Strict rate limiting for phone check - prevents enumeration
        RateLimiter::for('phone-check', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
