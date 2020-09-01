<?php

use Illuminate\Http\Request;
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

//  Auth Routes
Route::namespace('Api')->group(function () {

    //  Me Resource Routes
    Route::middleware('auth:api')->prefix('me')->name('my-')->group(function () {

        Route::get('/', 'UserController@getUser')->name('profile');

        Route::get('/stores', 'UserController@getUserStores')->name('stores');

    });

    //  Store Resource Routes
    Route::prefix('stores')->group(function () {

        Route::get('/', 'StoreController@getStores')->name('stores');
        Route::post('/', 'StoreController@createStore')->name('store-create');
            
        //  Single store  /stores/{store_id}
        Route::get('/{store_id}', 'StoreController@getStore')->name('store')->where('store_id', '[0-9]+');

        //  Single store actions - Update / Delete (Require Authenticated user)
        Route::middleware('auth:api')->group(function () {

            Route::put('/{store_id}', 'StoreController@updateStore')->name('store-update')->where('store_id', '[0-9]+');
            Route::delete('/{store_id}', 'StoreController@deleteStore')->name('store-delete')->where('store_id', '[0-9]+');
        
        });
            
        //  Single store locations  /stores/{store_id}/locations
        Route::get('/{store_id}/locations', 'StoreController@getStoreLocations')->name('store-locations')->where('store_id', '[0-9]+');
        
    });

    //  Location Resource Routes
    Route::prefix('locations')->group(function () {

        Route::get('/', 'LocationController@getLocations')->name('locations');
        Route::post('/', 'LocationController@createLocation')->name('location-create');
            
        //  Single location  /locations/{location_id}
        Route::get('/{location_id}', 'LocationController@getLocation')->name('location')->where('location_id', '[0-9]+');

        //  Single location actions - Update / Delete (Require Authenticated user)
        Route::middleware('auth:api')->group(function () {

            Route::put('/{location_id}', 'LocationController@updateLocation')->name('location-update')->where('location_id', '[0-9]+');
            Route::delete('/{location_id}', 'LocationController@deleteLocation')->name('location-delete')->where('location_id', '[0-9]+');
        
        });
            
        //  Single location products  /locations/{location_id}/products
        Route::get('/{location_id}/products', 'LocationController@getLocationProducts')->name('location-products')->where('location_id', '[0-9]+');

        //  Single location payment methods  /locations/{location_id}/payment-methods
        Route::get('/{location_id}/payment-methods', 'LocationController@getLocationPaymentMethods')->name('location-payment-methods')->where('location_id', '[0-9]+');
        
    });

    //  Product Resource Routes
    Route::prefix('products')->group(function () {

        Route::post('/', 'ProductController@createProduct')->name('product-create');
            
        //  Single product  /products/{product_id}
        Route::get('/{product_id}', 'ProductController@getProduct')->name('product')->where('product_id', '[0-9]+');
        Route::put('/{product_id}', 'ProductController@updateProduct')->name('product-update')->where('product_id', '[0-9]+');
            
        //  Single product variations  /products/{product_id}/variations
        Route::get('/{product_id}/variations', 'ProductController@getProductVariations')->name('product-variations')->where('product_id', '[0-9]+');

    });

    //  Order Resource Routes
    Route::prefix('orders')->group(function () {

        Route::post('/', 'OrderController@createOrder')->name('order-create');

        //  Single order  /orders/{order_id}
        Route::get('/{order_id}', 'OrderController@getOrder')->name('order')->where('order_id', '[0-9]+');

    });

    //  Payment Method Resource Routes
    Route::prefix('payment-methods')->group(function () {

        Route::get('/', 'PaymentMethodController@getPaymentMethods')->name('payment-methods');

    });

    //  Cart Resource Routes
    Route::prefix('cart')->group(function () {
        
        Route::post('/', 'CartController@calculateCart')->name('cart-calculator');

    });

});



Route::namespace('Api')->prefix('auth')->group(function () {
    
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('send-password-reset-link', 'AuthController@sendPasswordResetLink')->name('send-password-reset-link');
    Route::post('reset-password', 'AuthController@resetPassword')->name('reset-password');

    Route::post('logout', 'AuthController@logout')->middleware('auth:api')->name('logout');

});
