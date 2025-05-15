<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    public static function home()
    {
        if (Auth::check()) {
            switch (Auth::user()->role) {
                case 'admin':
                    return '/admin/dashboard';
                case 'head':
                    return '/head/dashboard';
                case 'observer':
                    return '/manager/dashboard';
                case 'employee':
                    return '/employee/dashboard';
                case 'intern':
                    return '/intern/dashboard';
                default:
                    return self::HOME; // Default fallback
            }
        }
        return self::HOME;
    }
}
