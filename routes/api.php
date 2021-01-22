<?php

use Illuminate\Support\Facades\Route;

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

//  API Home
Route::get('/', 'Api\HomeController@home')->name('api-home');
Route::get('/payment-methods', 'Api\HomeController@getPaymentMethods')->name('payment-methods');
Route::get('/subscription-plans', 'Api\HomeController@getSubscriptionPlans')->name('subscription-plans');

//  Auth Routes
Route::namespace('Api')->prefix('auth')->group(function () {
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('account-exists', 'AuthController@accountExists')->name('account-exists');
    Route::post('send-mobile-account-verification-code', 'AuthController@sendMobileAccountVerificationCode')->name('send-mobile-account-verification-code');
    Route::post('verify-mobile-account-verification-code', 'AuthController@verifyMobileAccountVerificationCode')->name('verify-mobile-account-verification-code');
    Route::post('send-password-reset-link', 'AuthController@sendPasswordResetLink')->name('send-password-reset-link');
    Route::post('reset-password', 'AuthController@resetPassword')->name('reset-password');
    Route::post('logout', 'AuthController@logout')->middleware('auth:api')->name('logout');
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

        Route::get('/stores', 'UserController@getUserStores')->name('stores');
        Route::get('/stores?type=created', 'UserController@getUserStores')->name('created-stores');
        Route::get('/stores?type=shared', 'UserController@getUserStores')->name('shared-stores');
        Route::get('/stores?type=favourite', 'UserController@getUserStores')->name('favourite-stores');

        //  Single store    /api/me/store/{store_id}   name => my-store
        Route::get('/stores/{store_id}', 'UserController@getUserStore')->name('store');

        //  Single store resources    /api/me/store/{store_id}   name => my-store-*
        Route::prefix('stores/{store_id}')->name('store-')->group(function () {

            Route::get('/locations', 'UserController@getUserStoreLocations')->name('locations');
            Route::get('/default-location', 'UserController@getUserStoreDefaultLocation')->name('default-location');
            Route::put('/default-location', 'UserController@updateUserStoreDefaultLocation')->name('default-location-update');

            //  Single Location    /api/me/store/{store_id}/locations   name => my-store-locations
            Route::get('/locations/{location_id}', 'UserController@getUserStoreLocation')->name('location');
        });

    });

    //  Store Resource Routes
    Route::prefix('stores')->group(function () {
        Route::get('/', 'StoreController@getStores')->name('stores');
        Route::post('/', 'StoreController@createStore')->name('store-create');

        //  Single store  /stores/{store_id}
        Route::get('/{store_id}', 'StoreController@getStore')->name('store')->where('store_id', '[0-9]+');
        Route::put('/{store_id}', 'StoreController@updateStore')->name('store-update')->where('store_id', '[0-9]+');
        Route::delete('/{store_id}', 'StoreController@deleteStore')->name('store-delete')->where('store_id', '[0-9]+');

        Route::post('/{store_id}/favourite', 'StoreController@addOrRemoveStoreAsFavourite')->name('store-favourite')->where('store_id', '[0-9]+');

        Route::post('/{store_id}/subscribe', 'StoreController@generateSubscription')->name('store-subscribe')->where('store_id', '[0-9]+');

        Route::post('/{store_id}/generate-payment-shortcode', 'StoreController@generatePaymentShortCode')->name('store-generate-payment-shortcode')->where('store_id', '[0-9]+');

        //  Single store users: /stores/{store_id}/users
        Route::get('/{store_id}/users', 'StoreController@getStoreUsers')->name('store-users')->where('store_id', '[0-9]+');

        //  Single store products: /stores/{store_id}/products
        Route::get('/{store_id}/products', 'StoreController@getStoreProducts')->name('store-products')->where('store_id', '[0-9]+');

        //  Single store locations: /stores/{store_id}/locations
        Route::get('/{store_id}/locations', 'StoreController@getStoreLocations')->name('store-locations')->where('store_id', '[0-9]+');

        //  Single store favourite locations: /stores/{store_id}/favourite-locations
        Route::get('/{store_id}/locations?type=favourite', 'StoreController@getStoreLocations')->name('store-favourite-locations')->where('store_id', '[0-9]+');

        //  Single store coupons: /stores/{store_id}/coupons
        Route::get('/{store_id}/coupons', 'StoreController@getStoreCoupons')->name('store-coupons')->where('store_id', '[0-9]+');

        //  Single store instant carts: /stores/{store_id}/instant-carts
        Route::get('/{store_id}/instant-carts', 'StoreController@getStoreInstantCarts')->name('store-instant-carts')->where('store_id', '[0-9]+');

        //  Single store rating statistics: /stores/{store_id}/rating-statistics
        Route::get('/{store_id}/rating-statistics', 'StoreController@getStoreRatingStatistics')->name('store-rating-statistics')->where('store_id', '[0-9]+');
    });

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
        Route::get('/{location_id}/orders?fulfillment_status=fulfilled', 'LocationController@getLocationOrders')->name('location-fulfilled-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?fulfillment_status=unfulfilled', 'LocationController@getLocationOrders')->name('location-unfulfilled-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?status=cancelled', 'LocationController@getLocationOrders')->name('location-cancelled-orders')->where('location_id', '[0-9]+');

        Route::get('/{location_id}/orders?is_customer=1', 'LocationController@getLocationOrders')->name('location-my-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?is_customer=1&fulfillment_status=fulfilled', 'LocationController@getLocationOrders')->name('location-my-fulfilled-orders')->where('location_id', '[0-9]+');
        Route::get('/{location_id}/orders?is_customer=1&fulfillment_status=unfulfilled', 'LocationController@getLocationOrders')->name('location-my-unfulfilled-orders')->where('location_id', '[0-9]+');
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

        //  Fulfillment related resources
        Route::post('/{order_id}/fulfil', 'OrderController@fulfilOrder')->name('order-fulfillment')->where('order_id', '[0-9]+');
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

    //  Cart Resource Routes
    Route::prefix('cart')->group(function () {
        Route::post('/', 'CartController@calculateCart')->name('cart-calculator');
    });
});
