<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

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
        Relation::morphMap([
            'user' => 'App\User',
            'order' => 'App\Order',
            'store' => 'App\Store',
            'location' => 'App\Location',
            'instant_cart' => 'App\InstantCart',
            'subscription' => 'App\Subscription',
        ]);

        /*
            To Help With Our HATEOAS (HAL) API - I include the following as suggested by Laravel
            ------------------------------------------------------------------------------------
            If you would like to disable the wrapping of the outer-most resource, you may use the
            "withoutWrapping" method on the base resource class. Typically, you should call this
            method from your AppServiceProvider or another service provider that is loaded on
            every request to your application:

            Reference: https://laravel.com/docs/5.7/eloquent-resources#concept-overview

        */
        JsonResource::withoutWrapping();
    }
}
