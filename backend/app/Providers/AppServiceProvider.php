<?php

namespace App\Providers;

use App\Http\ApiCors;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Schema::defaultStringLength(191);
        Passport::routes();
        foreach (glob(app_path() . '/Helper/*.php') as $filename) {
            require_once($filename);
        }

        // Runs for successful responses, preflight, and exceptions rendered
        // after the middleware stack (throttle 429, not found, server errors).
        $this->app['events']->listen(RequestHandled::class, function (RequestHandled $event) {
            ApiCors::apply($event->request, $event->response);
        });
    }

}
