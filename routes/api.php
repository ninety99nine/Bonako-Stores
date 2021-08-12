<?php

use App\Subscription;
use Illuminate\Support\Facades\DB;
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
Route::get('/exception', function(){

    return \App\Store::find(1);

    //  return Illuminate\Support\Facades\DB::table('stores')->get();

    return [
        'auth(api)->user' => auth('api')->user()->id,
    ];
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

        Route::get('/addresses', 'UserController@getUserAddresses')->name('addresses');
        Route::post('/addresses', 'UserController@createUserAddress')->name('addresses-create');

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

            Route::get('/orders', 'LocationController@getLocationOrders')->name('orders');

            Route::get('/coupons', 'LocationController@getLocationCoupons')->name('coupons');
            Route::get('/products', 'LocationController@getLocationProducts')->name('products');
            Route::get('/customers', 'LocationController@getLocationCustomers')->name('customers');
            Route::get('/instant-carts', 'LocationController@getLocationInstantCarts')->name('instant-carts');

            Route::get('/favourite-status', 'LocationController@getLocationFavouriteStatus')->name('favourite-status');
            Route::post('/toggle-favourite', 'LocationController@toggleLocationAsFavourite')->name('toggle-favourite');

            Route::post('/product-arrangement', 'LocationController@arrangeLocationProducts')->name('product-arrangement');

            Route::get('/statistics', 'LocationController@getLocationReportStatistics')->name('report-statistics')->where('location_id', '[0-9]+');

        });

    });

    //  Order Resource Routes
    Route::prefix('orders')->group(function () {

        Route::get('/', 'OrderController@getOrders')->name('orders');
        Route::post('/', 'OrderController@createOrder')->name('order-create');

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
            Route::post('/pay', 'OrderController@payOrder')->name('pay');

            Route::get('/transactions', 'OrderController@getOrderTransactions')->name('transactions');
            Route::post('/transactions', 'OrderController@createOrderTransaction')->name('transactions-create');

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
