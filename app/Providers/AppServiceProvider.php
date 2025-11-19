<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Production optimizations
        if ($this->app->isProduction() || $this->app->environment('production')) {
            // Disable debug features
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            
            // Force HTTPS in production
            \URL::forceScheme('https');
            
            // Ensure APP_URL uses HTTPS
            if (strpos(config('app.url'), 'http://') === 0) {
                config(['app.url' => str_replace('http://', 'https://', config('app.url'))]);
            }
        }
        
        // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);
    }
}
