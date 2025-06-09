<?php
// http://finance.reltroner.local app/Providers/RouteServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Default behavior:
        Route::prefix('api')
         ->middleware('api')
         ->group(base_path('routes/api.php'));

        Route::middleware('web')
         ->group(base_path('routes/web.php'));
    }
}
