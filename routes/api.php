<?php

use App\Subscription;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;


/**
 *  When accessing the API routes from POSTMAN we are able to receive a response, however when we
 *  try to run the same requests from our Flutter Progressive Web App we get a CORS error:
 *
 *  "No 'Access-Control-Allow-Origin' header is present on the requested resource"
 *
 *  I tried using "fruitcake/laravel-cors" from "https://github.com/fruitcake/laravel-cors",
 *  since i usually get CORS related issues fixed with this package, but for some reason it
 *  did not work even after trying many solutions/approaches.
 *
 *  I resorted setting the headers manually below, whic fixed the issue. I got this solution
 *  from a stackoverflow discussion.
 *
 *  Refer to: https://stackoverflow.com/questions/67251985/laravel-production-cors-no-access-control-allow-origin-header
 */
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Accept, Content-Type, X-Auth-Token, Origin, Authorization');

Route::get('/notify', function(Request $request){

    return config('cors');

    //  Login using the given user account
    $user = auth()->loginUsingId(\App\User::find(1)->id);

    //  Set the user auth instance
    auth('api')->setUser($user);

    return \App\Order::find(3)->sendResourcePaymentRequest([
        'percentage_rate' => 50,
        'send_customer_sms' => false,
        'payer_mobile_number' => 72882239,
    ], $user)->convertToApiFormat();

});

Route::get('/pay', function(Request $request){

    //  Login using the given user account
    $user = auth()->loginUsingId(\App\User::find(1)->id);

    //  Set the user auth instance
    auth('api')->setUser($user);

    return \App\Order::find(3)->payResource([
        'transaction_id' => 5,
        'payment_method_id' => 1,
    ], $user);

});

Route::get('/order', function(Request $request){

    //  Login using the given user account
    $user = auth()->loginUsingId(\App\User::find(1)->id);

    //  Set the user auth instance
    auth('api')->setUser($user);

    return \App\Order::find(3)->convertToApiFormat();

});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/reset', function(Request $request){

    /******************************
     *  CLEAR CACHE               *
     *****************************/

    \Illuminate\Support\Facades\Artisan::call('cache:clear');

    /******************************
     *  TRUNCATE TABLES           *
     *****************************/

    Schema::disableForeignKeyConstraints(); //  Disable Foreign key checks

    DB::table('addresses')->truncate();
    DB::table('adverts')->truncate();
    DB::table('carts')->truncate();
    DB::table('coupon_allocations')->truncate();
    DB::table('coupon_lines')->truncate();
    DB::table('coupons')->truncate();
    DB::table('customers')->truncate();
    DB::table('delivery_lines')->truncate();
    DB::table('favourites')->truncate();
    DB::table('instant_carts')->truncate();
    DB::table('item_lines')->truncate();
    DB::table('location_order')->truncate();
    DB::table('location_payment_methods')->truncate();
    DB::table('location_ratings')->truncate();
    DB::table('location_user')->truncate();
    DB::table('locations')->truncate();
    DB::table('mobile_verifications')->truncate();
    DB::table('model_has_permissions')->truncate();
    DB::table('model_has_roles')->truncate();
    DB::table('orders')->truncate();
    DB::table('permissions')->truncate();
    DB::table('popular_stores')->truncate();
    DB::table('product_allocations')->truncate();
    DB::table('products')->truncate();
    DB::table('reports')->truncate();
    DB::table('role_has_permissions')->truncate();
    DB::table('roles')->truncate();
    DB::table('short_codes')->truncate();
    DB::table('sms')->truncate();
    DB::table('stores')->truncate();
    DB::table('subscriptions')->truncate();
    DB::table('transactions')->truncate();
    DB::table('users')->truncate();
    DB::table('variables')->truncate();

    Schema::enableForeignKeyConstraints(); //  Enable on Foreign key checks

    if($request->input('create-resources') == 'true'){

        //  Create a user account
        $merchant_user = \App\User::create([
            'first_name' => 'Julian',
            'last_name' => 'Tabona',
            'account_type' => 'basic',
            'mobile_number' => '26772882239',
            'accepted_terms_and_conditions' => true,
            'mobile_number_verified_at' => \Carbon\Carbon::now(),
            'password' => \Illuminate\Support\Facades\Hash::make('QWEasd'),
            'firebase_device_tokens' => [
                'fxOBPbwQTG2-nuiBopeNZP:APA91bE84NSXOslH_jSa45bAPrvYYPUA4t2Qm8PGpeXj1rqt00NEkZ6n7kDz5dO-I93-oMK-qyEhw5i8rRT9OJXDfyRKNWB0lJ9tt12yswNd7ZuNr9qLtd4WPI1j--8buoRGiSpdbYaX',
                'djaCaRGp3kf6mtdTA627Rb:APA91bEjvEV-zBn7ykgS5hDZSoRTHCiKe_Bn_nta4J4vAegRQ_Q0NtQE93_Pe2GDDkSNv0y-DHGzxfjMSBtTSB0gEsPKDBrcndaTDnRltD_-40hmPvRFL9ey7zG8nE6quTAKN39l_rvz'
            ]
        ]);

        //  Create a user account
        $customer_user = \App\User::create([
            'first_name' => 'Bonolo',
            'last_name' => 'Sesiane',
            'account_type' => 'basic',
            'mobile_number' => '26777479083',
            'accepted_terms_and_conditions' => true,
            'mobile_number_verified_at' => \Carbon\Carbon::now(),
            'password' => \Illuminate\Support\Facades\Hash::make('QWEasd'),
        ]);

        //  Login using the given user account
        $merchant_user = auth()->loginUsingId($merchant_user->id);

        //  Set the user auth instance
        auth('api')->setUser($merchant_user);

        /******************************
         *  CREATE A NEW STORE        *
         *****************************/
        $store = (new \App\Store())->createResource([
            'name' => 'Veggie Store',
            'online' => true,
            'hex_color' => '2D8CF0',
            'location' => [
                'online' => true,
                'call_to_action' => 'Buy veggies',
            ],
            'allow_sending_merchant_sms' => true,
            'offline_message' => 'Sorry, we are currently offline',
        ], $merchant_user);

        /******************************
         *  SUBSCRIBE TO NEW STORE    *
         *****************************/
        $store_subscription = $store->generateResourceSubscription([
            'subscription_plan_id' => 3,
            'payment_method_id' => 1
        ], $merchant_user);

        /***********************************************
         *  INVITE USER TO THE STORE (AS TEAM MEMBER)  *
         ***********************************************/
        $merchant_store_subscription = $store->locations()->first()->assignUserAsTeamMember([
            'mobile_numbers' => ['26777479083'],
            //  Grant a few permissions
            'permissions' => ['manage-coupons', 'manage-products', 'manage-customers']
        ]);

        /*********************************************
         *  SUBSCRIBE TO NEW STORE (INVITED USER)    *
         *********************************************/
        $customer_store_subscription = $store->generateResourceSubscription([
            'subscription_plan_id' => 3,
            'payment_method_id' => 1
        ], $customer_user);

        /******************************
         *  CREATE A NEW PRODUCT      *
         *****************************/
        $product = (new \App\Product())->createResource([
            'active' => true,
            'name' => 'Product 1',
            'product_type_id' => 1,
            'description' => 'This is Product 1',
            'unit_regular_price' => '100',
            'location_id' => 1
        ], $merchant_user);

        $product = (new \App\Product())->createResource([
            'active' => true,
            'name' => 'Product 2',
            'product_type_id' => 1,
            'description' => 'This is Product 2',
            'unit_regular_price' => '200',
            'unit_sale_price' => '150',     //  Add sale
            'location_id' => 1
        ], $merchant_user);

        /******************************
         *  CREATE A NEW COUPON       *
         *****************************/
        $product = (new \App\Coupon())->createResource([
            'active' => true,
            'name' => '10% Off',
            'description' => 'Discount by 10%',
            'discount_rate_type' => 'percentage',
            'percentage_rate' => '10',
            'apply_discount' => true,

            //  Activation type
            'activation_type' => 0, //  Use code

            //  Activation rules
            'allow_discount_on_new_customer' => true,

            'location_id' => 1
        ], $merchant_user);

        /******************************
         *  CREATE A NEW CART         *
         *****************************/

        $cart = (new \App\Cart())->createResource([
            'items' => [
                [
                    'id' => 1,
                    'quantity' => 2
                ],
                [
                    'id' => 2,
                    'quantity' => 4
                ]
            ],
            'location_id' => 1
        ], null, $merchant_user);

        $cart_2 = (new \App\Cart())->createResource([
            'items' => [
                [
                    'id' => 1,
                    'quantity' => 3
                ],
                [
                    'id' => 2,
                    'quantity' => 5
                ]
            ],
            'coupons' => [
                [
                    'id' => 1
                ]
            ],
            'location_id' => 1
        ], null, $merchant_user);

        $cart_3 = (new \App\Cart())->createResource([
            'items' => [
                [
                    'id' => 1,
                    'quantity' => 1
                ],
                [
                    'id' => 2,
                    'quantity' => 3
                ]
            ],
            'coupons' => [
                [
                    'id' => 1
                ]
            ],
            'location_id' => 1
        ], null, $customer_user);

        /**********************************
         *  CREATE A NEW ORDER FROM CART  *
         *********************************/

        $order = (new \App\Order())->createResource([
            'cart_id' => $cart->id
        ], $merchant_user);

        $order_2 = (new \App\Order())->createResource([
            'cart_id' => $cart_2->id
        ], $merchant_user);

        $order_3 = (new \App\Order())->createResource([
            'cart_id' => $cart_3->id
        ], $customer_user);

        /**********************************
         *  CREATE A NEW INSTANT CART     *
         *********************************/

        $instant_cart = (new \App\InstantCart())->createResource([
            'active' => true,
            'name' => 'Summer Combo Deal',
            'description' => 'Get your fruit combo deal',
            'items' => [
                [
                    'id' => 1,
                    'quantity' => 2
                ],
                [
                    'id' => 2,
                    'quantity' => 4
                ]
            ],
            'coupons' => [
                [
                    'id' => 1
                ]
            ],
            'allow_free_delivery' => true,
            'allow_stock_management' => true,
            'stock_quantity' => 10,
            'location_id' => 1,
        ], $merchant_user);

        /*************************************
         *  SUBSCRIBE TO NEW INSTANT CART    *
         ************************************/
        $instant_cart_subscription = $instant_cart->generateResourceSubscription([
            'subscription_plan_id' => 3,
            'payment_method_id' => 1
        ], $merchant_user);

        return $instant_cart_subscription;

    }

    return 'DATABASE RESET';

});


Route::get('/send-sms', function(){

    $order = \App\Order::latest()->first();

    return $order->customer->user->mobile_number;

    return $order->sendDeliveryConfirmationCodeSms(auth()->user());
});

//  API Home
Route::get('/', 'Api\HomeController@home')->name('api-home');
Route::get('/currencies', 'Api\HomeController@getCurrencies')->name('currencies');
Route::get('/product-types', 'Api\HomeController@getProductTypes')->name('product-types');
Route::get('/address-types', 'Api\HomeController@getAddressTypes')->name('address-types');
Route::get('/payment-methods', 'Api\HomeController@getPaymentMethods')->name('payment-methods');
Route::get('/subscription-plans', 'Api\HomeController@getSubscriptionPlans')->name('subscription-plans');

//  Auth Routes
Route::namespace('Api')->prefix('auth')->group(function () {
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('account-exists', 'AuthController@accountExists')->name('account-exists');
    Route::post('reset-password', 'AuthController@resetPassword')->name('reset-password');
    Route::post('logout', 'AuthController@logout')->middleware('auth:api')->name('logout');
    Route::get('show-mobile-verification-code', 'AuthController@showMobileVerificationCode')->name('show-mobile-verification-code');
    Route::post('generate-mobile-verification-code', 'AuthController@generateMobileVerificationCode')->name('generate-mobile-verification-code');
    Route::post('verify-mobile-verification-code', 'AuthController@checkMobileVerificationCodeValidity')->name('verify-mobile-verification-code');
});

/** NOTE THAT THIS IS REPLACED BY THE "/account-exists" ROUTE ABOVE, BUT BEFORE WE CAN REMOVE THIS ROUTE BELOW
 *  WE NEED TO UPDATE THE BONAKO USSD BUILDER TO USE THE NEW ROUTE ABOVE TO CHECK IF AN ACCOUNT EXISTS
 *
 *  REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE REMOVE
 */
Route::get('/{mobile_number}/mobile-account-exists', 'Api\UserController@checkMobileAccountExistence')->name('mobile-account-exists');

//  Auth Routes
Route::middleware('auth:api')->namespace('Api')->group(function () {

    //  Me Resource Routes
    Route::prefix('me')->name('my-')->group(function () {
        Route::get('/', 'UserController@getUser')->name('profile');
        Route::post('/accept-terms-and-conditions', 'AuthController@acceptTermsAndConditions')->name('accept-terms-and-conditions');

        Route::get('/stores', 'UserController@getUserStores')->name('stores');
        Route::get('/stores?type=favourite', 'UserController@getUserStores')->name('favourite-stores');
        Route::get('/stores?type=created', 'UserController@getUserStores')->name('created-stores');
        Route::get('/stores?type=shared', 'UserController@getUserStores')->name('shared-stores');

        //  Single store    /api/me/store/{store_id}   name => my-store
        Route::get('/stores/{store_id}', 'UserController@getUserStore')->name('store');

        //  Single store resources    /api/me/store/{store_id}   name => my-store-*
        Route::prefix('stores/{store_id}')->name('store-')->group(function () {

            Route::get('/locations', 'UserController@getUserStoreLocations')->name('locations');
            Route::get('/default-location', 'UserController@getUserStoreDefaultLocation')->name('default-location');
            Route::put('/default-location', 'UserController@updateUserStoreDefaultLocation')->name('default-location-update');

            //  Single Location    /api/me/store/{store_id}/locations   name => my-store-locations
            Route::get('/locations/{location_id}', 'UserController@getUserStoreLocation')->name('location');

            //  Single Location    /api/me/store/{store_id}/locations   name => my-store-locations
            Route::get('/locations/{location_id}/user-permissions', 'UserController@getUserStoreLocationPermissions')->name('location-permissions');

        });

        Route::get('/addresses', 'UserController@getUserAddresses')->name('addresses');
        Route::post('/addresses', 'UserController@createUserAddress')->name('addresses-create');

        Route::get('/subscriptions', 'UserController@getUserSubscriptions')->name('subscriptions');

    });

    //  Users Resource Routes
    Route::prefix('users')->group(function () {

        Route::post('search_by_mobile_number', 'UserController@searchUsersByMobileNumber')->name('search-user-by-mobile-number');

    });

    //  Popular Stores Resource Routes
    Route::prefix('popular-stores')->group(function () {

        Route::get('/', 'PopularStoreController@getPopularStores')->name('popular-stores');
        Route::post('/', 'PopularStoreController@createPopularStore')->name('popular-store-create');
        Route::post('/arrangement', 'PopularStoreController@arrangePopularStores')->name('popular-store-arrangement');

        //  Single popular store resources    /api/popular-stores/{popular_store_id}   name => popular-store-*
        Route::prefix('/{popular_store_id}')->name('popular-store-')->group(function () {

            Route::get('/', 'PopularStoreController@getPopularStore')->name('show')->where('popular_store_id', '[0-9]+');
            Route::put('/', 'PopularStoreController@updatePopularStore')->name('update')->where('popular_store_id', '[0-9]+');
            Route::delete('/', 'PopularStoreController@deletePopularStore')->name('delete')->where('popular_store_id', '[0-9]+');

        });

    });

    //  Adverts Resource Routes
    Route::prefix('adverts')->group(function () {

        Route::get('/', 'AdvertController@getAdverts')->name('adverts');
        Route::post('/', 'AdvertController@createAdvert')->name('advert-create');
        Route::post('/arrangement', 'AdvertController@arrangeAdverts')->name('advert-arrangement');

        //  Single advert resources    /api/adverts/{advert_id}   name => advert-*
        Route::prefix('/{advert_id}')->name('advert-')->group(function () {

            Route::get('/', 'AdvertController@getAdvert')->name('show')->where('advert_id', '[0-9]+');
            Route::put('/', 'AdvertController@updateAdvert')->name('update')->where('advert_id', '[0-9]+');
            Route::delete('/', 'AdvertController@deleteAdvert')->name('delete')->where('advert_id', '[0-9]+');

        });

    });

    //  Store Resource Routes
    Route::prefix('stores')->group(function () {

        Route::get('/', 'StoreController@getStores')->name('stores');
        Route::post('/', 'StoreController@createStore')->name('store-create');

        //  Single store resources    /api/stores/{store_id}   name => store-*
        Route::prefix('/{store_id}')->name('store-')->group(function () {

            Route::get('/', 'StoreController@getStore')->name('show')->where('store_id', '[0-9]+');
            Route::put('/', 'StoreController@updateStore')->name('update')->where('store_id', '[0-9]+');
            Route::delete('/', 'StoreController@deleteStore')->name('delete')->where('store_id', '[0-9]+');

            //  Store locations  /stores/{store_id}/locations
            Route::get('/locations', 'StoreController@getStoreLocations')->name('locations');
            Route::post('/locations', 'StoreController@createStoreLocation')->name('location-create');

            Route::post('/subscribe', 'StoreController@generateSubscription')->name('subscribe')->where('store_id', '[0-9]+');
            Route::post('/generate-payment-shortcode', 'StoreController@generatePaymentShortCode')->name('generate-payment-shortcode')->where('store_id', '[0-9]+');

        });

    });

    //  Location Resource Routes
    Route::prefix('locations')->group(function () {

        Route::get('/', 'LocationController@getLocations')->name('locations');
        Route::post('/', 'LocationController@createLocation')->name('location-create');

        //  Single location resources    /api/locations/{location_id}   name => location-*
        Route::prefix('/{location_id}')->name('location-')->group(function () {

            Route::get('/', 'LocationController@getLocation')->name('show')->where('location_id', '[0-9]+');
            Route::put('/', 'LocationController@updateLocation')->name('update')->where('location_id', '[0-9]+');
            Route::delete('/', 'LocationController@deleteLocation')->name('delete')->where('location_id', '[0-9]+');

            Route::get('/store', 'LocationController@getLocationStore')->name('store')->where('location_id', '[0-9]+');

            Route::get('/totals', 'LocationController@getLocationTotals')->name('totals');

            Route::get('/users', 'LocationController@getLocationUsers')->name('users');
            Route::post('/users', 'LocationController@assignLocationUserAsTeamMember')->name('assign-users');
            Route::delete('/users', 'LocationController@removeLocationUserAsTeamMember')->name('remove-users');

            Route::get('/orders', 'LocationController@getLocationOrders')->name('orders');

            Route::get('/coupons', 'LocationController@getLocationCoupons')->name('coupons');
            Route::get('/products', 'LocationController@getLocationProducts')->name('products');
            Route::get('/customers', 'LocationController@getLocationCustomers')->name('customers');
            Route::get('/instant-carts', 'LocationController@getLocationInstantCarts')->name('instant-carts');

            Route::get('/favourite-status', 'LocationController@getLocationFavouriteStatus')->name('favourite-status');
            Route::post('/toggle-favourite', 'LocationController@toggleLocationAsFavourite')->name('toggle-favourite');

            Route::post('/product-arrangement', 'LocationController@arrangeLocationProducts')->name('product-arrangement');

            Route::get('/statistics', 'LocationController@getLocationReportStatistics')->name('report-statistics')->where('location_id', '[0-9]+');

            Route::post('/user-permissions', 'LocationController@getLocationUserPermissions')->name('user-permissions');
            Route::post('/update-user-permissions', 'LocationController@updateLocationUserPermissions')->name('update-user-permissions');
            Route::get('/available-permissions', 'LocationController@getLocationAvailablePermissions')->name('available-permissions');

        });

    });

    //  Order Resource Routes
    Route::prefix('orders')->group(function () {

        Route::get('/', 'OrderController@getOrders')->name('orders');
        Route::post('/', 'OrderController@createOrder')->name('order-create');
        Route::post('/verify-delivery-confirmation-code', 'OrderController@checkOrderDeliveryConfirmationCodeValidity')->name('order-verify-delivery-confirmation-code');

        //  Single order resources    /api/orders/{order_id}   name => order-*
        Route::prefix('/{order_id}')->name('order-')->group(function () {

            Route::get('/', 'OrderController@getOrder')->name('show')->where('order_id', '[0-9]+');
            Route::put('/', 'OrderController@updateOrder')->name('update')->where('order_id', '[0-9]+');
            Route::delete('/', 'OrderController@deleteOrder')->name('delete')->where('order_id', '[0-9]+');

            Route::put('/deliver', 'OrderController@deliverOrder')->name('deliver');
            Route::put('/undeliver', 'OrderController@undeliverOrder')->name('undeliver');

            Route::put('/cancel', 'OrderController@cancelOrder')->name('cancel');
            Route::put('/uncancel', 'OrderController@uncancelOrder')->name('uncancel');

            Route::post('/payment-request', 'OrderController@sendOrderPaymentRequest')->name('payment-request');
            Route::post('/mark-as-paid', 'OrderController@markOrderAsPaid')->name('mark-as-paid');
            Route::post('/pay', 'OrderController@payOrder')->name('pay');

            Route::get('/transactions', 'OrderController@getOrderTransactions')->name('transactions');
            Route::post('/transactions', 'OrderController@createOrderTransaction')->name('transactions-create');
            Route::put('/transactions/{transaction_id}/status', 'OrderController@updateOrderTransactionStatus')->name('transaction-status-update');

            Route::get('/item-lines', 'OrderController@getOrderItemLines')->name('item-lines');
            Route::get('/coupon-lines', 'OrderController@getOrderCouponLines')->name('coupon-lines');

            Route::get('/shared-locations', 'OrderController@getOrderSharedLocations')->name('shared-locations');
            Route::get('/received-location', 'OrderController@getOrderReceivedLocation')->name('received-location');
            Route::post('/shared-locations', 'OrderController@updateOrderSharedLocations')->name('update-shared-locations');

            Route::get('/store', 'OrderController@getOrderStore')->name('store');

            Route::put('/resend-delivery-confirmation-code', 'OrderController@resendOrderDeliveryConfirmationCode')
                      ->name('resend-delivery-confirmation-code');

        });

    });

    //  Transaction Resource Routes
    Route::prefix('transactions')->group(function () {

        //  Single transaction resources    /api/transactions/{transaction_id}   name => transaction-*
        Route::prefix('/{transaction_id}')->name('transaction-')->group(function () {

            //  Update transation status after payment (Successful / Failed)
            Route::put('/status', 'TransactionController@updateTransaction')->name('update')->where('transaction_id', '[0-9]+');

        });

    });

    //  Product Resource Routes
    Route::prefix('products')->group(function () {

        Route::get('/', 'ProductController@getProducts')->name('products');
        Route::post('/', 'ProductController@createProduct')->name('product-create');

        //  Single product resources    /api/products/{product_id}   name => product-*
        Route::prefix('/{product_id}')->name('product-')->group(function () {

            Route::get('/', 'ProductController@getProduct')->name('show')->where('product_id', '[0-9]+');
            Route::put('/', 'ProductController@updateProduct')->name('update')->where('product_id', '[0-9]+');
            Route::delete('/', 'ProductController@deleteProduct')->name('delete')->where('product_id', '[0-9]+');

            //  Single product resources    /api/products/{product_id}   name => product-*
            Route::prefix('/variations')->name('variations-')->group(function () {

                Route::get('/', 'ProductController@getProductVariations')->name('list');
                Route::post('/', 'ProductController@createProductVariations')->name('create');

            });

            Route::get('/locations', 'ProductController@getProductLocations')->name('locations');

        });

    });

    //  Coupon Resource Routes
    Route::prefix('coupons')->group(function () {

        Route::get('/', 'CouponController@getCoupons')->name('coupons');
        Route::post('/', 'CouponController@createCoupon')->name('coupon-create');

        //  Single Coupon resources    /api/coupons/{coupon_id}   name => coupon-*
        Route::prefix('/{coupon_id}')->name('coupon-')->group(function () {

            Route::get('/', 'CouponController@getCoupon')->name('show')->where('coupon_id', '[0-9]+');
            Route::put('/', 'CouponController@updateCoupon')->name('update')->where('coupon_id', '[0-9]+');
            Route::delete('/', 'CouponController@deleteCoupon')->name('delete')->where('coupon_id', '[0-9]+');

            Route::get('/location', 'CouponController@getCouponLocation')->name('location')->where('coupon_id', '[0-9]+');

        });

    });

    //  Instant Cart Resource Routes
    Route::prefix('instant-carts')->group(function () {

        Route::get('/', 'InstantCartController@getInstantCarts')->name('instant-carts');
        Route::post('/', 'InstantCartController@createInstantCart')->name('instant-cart-create');

        //  Single instant cart resources    /api/instant-carts/{instant_cart_id}   name => instant-cart-*
        Route::prefix('/{instant_cart_id}')->name('instant-cart-')->group(function () {

            Route::get('/', 'InstantCartController@getInstantCart')->name('show')->where('instant_cart_id', '[0-9]+');
            Route::put('/', 'InstantCartController@updateInstantCart')->name('update')->where('instant_cart_id', '[0-9]+');
            Route::delete('/', 'InstantCartController@deleteInstantCart')->name('delete')->where('instant_cart_id', '[0-9]+');

            Route::get('/location', 'InstantCartController@getInstantCartLocation')->name('location')->where('instant_cart_id', '[0-9]+');
            Route::post('/generate-payment-shortcode', 'InstantCartController@generatePaymentShortCode')->name('generate-payment-shortcode')->where('instant_cart_id', '[0-9]+');
            Route::post('/subscribe', 'InstantCartController@generateSubscription')->name('subscribe')->where('instant_cart_id', '[0-9]+');

        });

    });

    //  Customer Resource Routes
    Route::prefix('customers')->group(function () {

        Route::get('/', 'CustomerController@getCustomers')->name('customers');
        Route::post('/', 'CustomerController@createCustomer')->name('customer-create');

        //  Single Customer resources    /api/customers/{customer_id}   name => customer-*
        Route::prefix('/{customer_id}')->name('customer-')->group(function () {

            Route::get('/', 'CustomerController@getCustomer')->name('show')->where('customer_id', '[0-9]+');
            Route::put('/', 'CustomerController@updateCustomer')->name('update')->where('customer_id', '[0-9]+');
            Route::delete('/', 'CustomerController@deleteCustomer')->name('delete')->where('customer_id', '[0-9]+');

            Route::get('/orders', 'CustomerController@getCustomerOrders')->name('orders');

        });

    });

    //  Shortcode Resource Routes
    Route::prefix('shortcodes')->group(function () {

        //  Single shortcode resources    /api/shortcodes/{code}   name => shortcode-*
        Route::prefix('/{code}')->name('shortcode-')->group(function () {

            Route::get('/search', 'ShortcodeController@searchShortcode')->name('show')->where('product_id', '[0-9]+');

        });

    });

    //  Cart Resource Routes
    Route::prefix('carts')->group(function () {

        Route::get('/', 'CartController@getCarts')->name('carts');
        Route::post('/', 'CartController@createCart')->name('cart-create');
        Route::post('/calculator', 'CartController@calculateCart')->name('cart-calculate');

        //  Single Cart resources    /api/carts/{cart_id}   name => cart-*
        Route::prefix('/{cart_id}')->name('cart-')->group(function () {

            Route::get('/', 'CartController@getCart')->name('show')->where('cart_id', '[0-9]+');
            Route::put('/', 'CartController@updateCart')->name('update')->where('cart_id', '[0-9]+');
            Route::delete('/', 'CartController@deleteCart')->name('delete')->where('cart_id', '[0-9]+');

            Route::put('/refresh', 'CartController@refreshCart')->name('refresh')->where('cart_id', '[0-9]+');
            Route::put('/reset', 'CartController@resetCart')->name('reset')->where('cart_id', '[0-9]+');

        });

    });

    //  Report Resource Routes
    Route::prefix('reports')->group(function () {

        Route::get('/', 'ReportController@getReports')->name('reports');
        Route::get('/statistics', 'ReportController@getReportStatistics')->name('report-statistics');

    });

    /*
    //  Location Resource Routes
    Route::prefix('locations')->group(function () {
        Route::get('/', 'LocationController@getLocations')->name('locations');
        Route::post('/', 'LocationController@createLocation')->name('location-create');

        //  Single location: /locations/{location_id}
        Route::get('/{location_id}', 'LocationController@getLocation')->name('location')->where('location_id', '[0-9]+');
        Route::put('/{location_id}', 'LocationController@updateLocation')->name('location-update')->where('location_id', '[0-9]+');
        Route::delete('/{location_id}', 'LocationController@deleteLocation')->name('location-delete')->where('location_id', '[0-9]+');

        //  Single location products: /locations/{location_id}/products
        Route::get('/{location_id}/products', 'LocationController@getLocationProducts')->name('location-products')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/products?active=1', 'LocationController@getLocationProducts')->name('location-active-products')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/products?active=0', 'LocationController@getLocationProducts')->name('location-inactive-products')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/products?onsale=1', 'LocationController@getLocationProducts')->name('location-on-sale-products')->where('location_id', '[0-9]+');

        Route::post('/{location_id}/products', 'LocationController@createLocationProduct')->name('location-product-create');
        Route::put('/{location_id}/products/arrangement', 'LocationController@updateLocationProductArrangement')->name('location-product-arrangement');

        //  Single location users: /locations/{location_id}/users
        Route::get('/{location_id}/users', 'LocationController@getLocationUsers')->name('location-users')->where('location_id', '[0-9]+');

        //  Single location orders: /locations/{location_id}/orders
        Route::get('/{location_id}/orders', 'LocationController@getLocationOrders')->name('location-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?delivery_status=delivered', 'LocationController@getLocationOrders')->name('location-delivered-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?delivery_status=undelivered', 'LocationController@getLocationOrders')->name('location-undelivered-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?status=cancelled', 'LocationController@getLocationOrders')->name('location-cancelled-orders')->where('location_id', '[0-9]+');

        Route::get('/{location_id}/orders?is_customer=1', 'LocationController@getLocationOrders')->name('location-my-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?is_customer=1&delivery_status=delivered', 'LocationController@getLocationOrders')->name('location-my-delivered-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?is_customer=1&delivery_status=undelivered', 'LocationController@getLocationOrders')->name('location-my-undelivered-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?is_customer=1&status=cancelled', 'LocationController@getLocationOrders')->name('location-my-cancelled-orders')->where('location_id', '[0-9]+');

        Route::get('/{location_id}/unrated-orders', 'LocationController@getLocationUnratedOrders')->name('location-unrated-orders')->where('location_id', '[0-9]+');

        //  Single location instant carts: /locations/{location_id}/instant-carts
        Route::get('/{location_id}/instant-carts', 'LocationController@getLocationInstantCarts')->name('location-instant-carts')->where('location_id', '[0-9]+');

        //  Single location payment methods: /locations/{location_id}/payment-methods
        Route::get('/{location_id}/payment-methods', 'LocationController@getLocationPaymentMethods')->name('location-payment-methods')->where('location_id', '[0-9]+');
    });


    //  Product Resource Routes
    Route::prefix('products')->group(function () {
        Route::post('/', 'ProductController@createProduct')->name('product-create');

        //  Single product  /products/{product_id}
        Route::get('/{product_id}', 'ProductController@getProduct')->name('product')->where('product_id', '[0-9]+');
        Route::put('/{product_id}', 'ProductController@updateProduct')->name('product-update')->where('product_id', '[0-9]+');
        Route::delete('/{product_id}', 'ProductController@deleteProduct')->name('product-delete')->where('product_id', '[0-9]+');

        //  Single product variations: /products/{product_id}/variations
        Route::get('/{product_id}/variations', 'ProductController@getProductVariations')->name('product-variations')->where('product_id', '[0-9]+');
        Route::post('/{product_id}/variations', 'ProductController@createProductVariations')->name('product-variations-create')->where('product_id', '[0-9]+');
    });

    //  Order Resource Routes
    Route::prefix('orders')->group(function () {
        Route::post('/', 'OrderController@createOrder')->name('order-create');

        //  Single order  /orders/{order_id}
        Route::get('/{order_id}', 'OrderController@getOrder')->name('order')->where('order_id', '[0-9]+');
        Route::delete('/{order_id}', 'OrderController@deleteOrder')->name('order-delete')->where('order_id', '[0-9]+');

        //  Delivery related resources
        Route::post('/{order_id}/deliver', 'OrderController@deliverOrder')->name('order-delivery')->where('order_id', '[0-9]+');
        Route::post('/{order_id}/cancel', 'OrderController@cancelOrder')->name('order-cancellation')->where('order_id', '[0-9]+');

    });

    //  Coupon Resource Routes
    Route::prefix('coupons')->group(function () {
        Route::post('/', 'CouponController@createCoupon')->name('coupon-create');

        //  Single coupon  /coupons/{coupon_id}
        Route::get('/{coupon_id}', 'CouponController@getCoupon')->name('coupon')->where('coupon_id', '[0-9]+');
    });

    //  Instant Carts Resource Routes
    Route::prefix('instant-carts')->group(function () {
        Route::post('/', 'InstantCartController@createInstantCart')->name('instant-cart-create');

        //  Single instant cart  /instant-carts/{instant_cart_id}
        Route::get('/{instant_cart_id}', 'InstantCartController@getInstantCart')->name('instant-cart')->where('instant_cart_id', '[0-9]+');
        Route::put('/{instant_cart_id}', 'InstantCartController@updateInstantCart')->name('instant-cart-update')->where('instant_cart_id', '[0-9]+');
    });
    */
});
