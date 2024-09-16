<?php

namespace App\Providers;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use App\Channels\DatabaseChannel;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
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
        Notification::extend('database', function ($app) {
            return new DatabaseChannel();
        });


        $ttl = getSetting('lifespan_token') ? (int)getSetting('lifespan_token') : 60;
        Config::set('jwt.ttl', (int) $ttl);
    }
}
