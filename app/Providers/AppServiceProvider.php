<?php

namespace App\Providers;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use App\Channels\DatabaseChannel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;


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

        if (!app()->runningInConsole() || !app()->runningUnitTests()) {
            if (Schema::hasTable('settings')) {
                $ttl = getSetting('lifespan_token') ? (int) getSetting('lifespan_token') : 60;
                Config::set('jwt.ttl', (int) $ttl);
            } else {
                Config::set('jwt.ttl', 60);
            }
        }
    }
}
