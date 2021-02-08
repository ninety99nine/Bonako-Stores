<?php

namespace App\Providers;

use App\Sms;
use App\User;
use App\Order;
use App\Store;
use App\Location;
use App\ShortCode;
use App\InstantCart;
use App\Policies\SmsPolicy;
use App\Policies\UserPolicy;
use App\Policies\OrderPolicy;
use App\Policies\StorePolicy;
use Laravel\Passport\Passport;
use App\Policies\ProductPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ShortCodePolicy;
use App\Policies\InstantCartPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;



class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Sms::class => SmsPolicy::class,
        User::class => UserPolicy::class,
        Order::class => OrderPolicy::class,
        Store::class => StorePolicy::class,
        Product::class => ProductPolicy::class,
        Location::class => LocationPolicy::class,
        ShortCode::class => ShortCodePolicy::class,
        InstantCart::class => InstantCartPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }

}
