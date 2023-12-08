<?php

namespace App\Providers;

use App\Models\LogSystem;
use App\Models\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        App::bind('log_system', function () {
            return new LogSystem();
        });

        App::bind('system_notification', function () {
            return new Notification();
        });
    }
}
