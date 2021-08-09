<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentMethods as PaymentMethodsResource;

class Location extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        try {

            //  Set the online payment methods (Extract the nested online payment methods)
            $online_payment_methods = (new PaymentMethodsResource($this->onlinePaymentMethods ?? []))->collection;

            //  Set the offline payment methods (Extract the nested offline payment methods)
            $offline_payment_methods = (new PaymentMethodsResource($this->offlinePaymentMethods ?? []))->collection;

            return [
                'id' => $this->id,
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
                'currency' => $this->currency,
                'about_us' => $this->about_us,
                'contact_us' => $this->contact_us,
                'call_to_action' => $this->call_to_action,
                'online' => $this->online,
                'offline_message' => $this->offline_message,
                'allow_delivery' => $this->allow_delivery,
                'delivery_note' => $this->delivery_note,
                'allow_free_delivery' => $this->allow_free_delivery,
                'delivery_flat_fee' => $this->delivery_flat_fee,
                'delivery_destinations' => $this->delivery_destinations,
                'delivery_days' => $this->delivery_days,
                'delivery_times' => $this->delivery_times,
                'allow_pickups' => $this->allow_pickups,
                'pickup_note' => $this->pickup_note,
                'pickup_destinations' => $this->pickup_destinations,
                'pickup_days' => $this->pickup_days,
                'pickup_times' => $this->pickup_times,
                'allow_payments' => $this->allow_payments,
                'orange_money_merchant_code' => $this->orange_money_merchant_code,
                'minimum_stock_quantity' => $this->minimum_stock_quantity,
                'allow_sending_merchant_sms' => $this->allow_sending_merchant_sms,

                /*  Timestamp Info  */
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,

                /*  Attributes  */
                '_attributes' => [
                    'resource_type' => $this->resource_type
                ],

                /*  Resource Links */
                '_links' => [
                    'curies' => [
                        ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                    ],

                    //  Link to current resource
                    'self' => [
                        'href' => route('location-show', ['location_id' => $this->id]),
                        'title' => 'This location',
                    ],

                    //  Link to the store
                    'bos:store' => [
                        'href' => route('location-store', ['location_id' => $this->id]),
                        'title' => 'The location store',
                    ],

                    //  Link to the location totals
                    'bos:totals' => [
                        'href' => route('location-totals', ['location_id' => $this->id]),
                        'title' => 'The location resource totals',
                    ],

                    //  Link to the users
                    'bos:users' => [
                        'href' => route('location-users', ['location_id' => $this->id]),
                        'title' => 'The location users',
                    ],

                    //  Link to the orders
                    'bos:orders' => [
                        'href' => route('location-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders',
                    ],

                    //  Link to the instant carts
                    'bos:instant_carts' => [
                        'href' => route('location-instant-carts', ['location_id' => $this->id]),
                        'title' => 'The location instant carts',
                    ],

                    //  Link to the coupons
                    'bos:coupons' => [
                        'href' => route('location-coupons', ['location_id' => $this->id]),
                        'title' => 'The location coupons',
                    ],

                    //  Link to the products
                    'bos:products' => [
                        'href' => route('location-products', ['location_id' => $this->id]),
                        'title' => 'The location products',
                    ],

                    //  Link to the customers
                    'bos:customers' => [
                        'href' => route('location-customers', ['location_id' => $this->id]),
                        'title' => 'The location customers',
                    ],

                    //  Link to update product arrangement
                    'bos:product_arrangement' => [
                        'href' => route('location-product-arrangement', ['location_id' => $this->id]),
                        'title' => 'The POST route to update the arrangement of products for this location',
                    ],

                    //  Link to the instant carts
                    'bos:instant_carts' => [
                        'href' => route('location-instant-carts', ['location_id' => $this->id]),
                        'title' => 'The location instant carts',
                    ],

                    //  Link to check if favourite
                    'bos:favourite_status' => [
                        'href' => route('location-favourite-status', ['location_id' => $this->id]),
                        'title' => 'The route to check if this location is a favourite location',
                    ],

                    //  Link to toggle favourite
                    'bos:toggle_favourite' => [
                        'href' => route('location-toggle-favourite', ['location_id' => $this->id]),
                        'title' => 'The POST route to Mark or unmark location as favourite',
                    ],

                    //  Link to the report statistics
                    'bos:report_statistics' => [
                        'href' => route('location-report-statistics', ['location_id' => $this->id]),
                        'title' => 'The location statistics',
                    ],











                    /*

                    //  Link to the products
                    'bos:active-products' => [
                        'href' => route('location-active-products', ['location_id' => $this->id]),
                        'title' => 'The location products',
                        'total' => $this->products()->active()->count(),
                    ],

                    //  Link to the products
                    'bos:inactive-products' => [
                        'href' => route('location-inactive-products', ['location_id' => $this->id]),
                        'title' => 'The location products',
                        'total' => $this->products()->inActive()->count(),
                    ],

                    //  Link to the products
                    'bos:on-sale-products' => [
                        'href' => route('location-on-sale-products', ['location_id' => $this->id]),
                        'title' => 'The location products',
                        'total' => $this->products()->onSale()->count(),
                    ],

                    //  Link to the create products
                    'bos:product-create' => [
                        'href' => route('location-product-create', ['location_id' => $this->id]),
                        'title' => 'The POST route to create a new product for this location',
                    ],

                    //  Link to update product arrangement
                    'bos:product_arrangement' => [
                        'href' => route('location-product-arrangement', ['location_id' => $this->id]),
                        'title' => 'The PUT route to update the order of products for this location',
                    ],

                    //  Link to the users
                    'bos:users' => [
                        'href' => route('location-users', ['location_id' => $this->id]),
                        'title' => 'The location users',
                        'total' => $this->users()->count(),
                    ],

                    //  Link to the isntant carts
                    'bos:instant_carts' => [
                        'href' => route('location-instant-carts', ['location_id' => $this->id]),
                        'title' => 'The location instant carts',
                        'total' => $this->instantCarts()->count(),
                    ],

                    //  Link to the create instant cart
                    'bos:instant-cart-create' => [
                        'href' => route('instant-cart-create'),
                        'title' => 'The POST route to create a new instant cart',
                    ],

                    //  Link to the orders
                    'bos:received-orders' => [
                        'href' => route('my-store-location-orders', ['store_id' => $this->store_id, 'location_id' => $this->id]),
                        'title' => 'The location orders',
                        'total' => $this->orders()->count(),
                    ],

                    //  Link to the delivered orders
                    'bos:received-delivered-orders' => [
                        'href' => route('my-store-location-delivered-orders', ['store_id' => $this->store_id, 'location_id' => $this->id]),
                        'title' => 'The location orders that are delivered',
                        'total' => $this->orders()->delivered()->count(),
                    ],

                    //  Link to the undelivered orders
                    'bos:received-undelivered-orders' => [
                        'href' => route('my-store-location-undelivered-orders', ['store_id' => $this->store_id, 'location_id' => $this->id]),
                        'title' => 'The location orders that are undelivered',
                        'total' => $this->orders()->undelivered()->count(),
                    ],

                    //  Link to the cancelled orders
                    'bos:received-cancelled-orders' => [
                        'href' => route('my-store-location-cancelled-orders', ['store_id' => $this->store_id, 'location_id' => $this->id]),
                        'title' => 'The location orders that are cancelled',
                        'total' => $this->orders()->cancelled()->count(),
                    ],

                    //  Link to my orders
                    'bos:sent-orders' => [
                        'href' => route('location-my-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that have been placed by this user',
                        'total' => $this->orders()->userIsCustomer()->count(),
                    ],

                    //  Link to my delivered orders
                    'bos:sent-delivered-orders' => [
                        'href' => route('location-my-delivered-orders', ['location_id' => $this->id]),
                        'title' => 'TThe location orders that have been placed by this user and are delivered',
                        'total' => $this->orders()->userIsCustomer()->delivered()->count(),
                    ],

                    //  Link to my undelivered orders
                    'bos:sent-undelivered-orders' => [
                        'href' => route('location-my-undelivered-orders', ['location_id' => $this->id]),
                        'title' => 'TThe location orders that have been placed by this user and are undelivered',
                        'total' => $this->orders()->userIsCustomer()->undelivered()->count(),
                    ],

                    //  Link to my cancelled orders
                    'bos:sent-cancelled-orders' => [
                        'href' => route('location-my-cancelled-orders', ['location_id' => $this->id]),
                        'title' => 'TThe location orders that have been placed by this user and are cancelled',
                        'total' => $this->orders()->userIsCustomer()->cancelled()->count(),
                    ],

                    //  Link to the unrated orders
                    'bos:unrated-orders' => [
                        'href' => route('location-unrated-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are not rated',
                        'total' => $this->orders()->requireRating()->count(),
                    ],

                    */
                ],

                 /*  Embedded  */
                '_embedded' => [
                    'online_payment_methods' => $online_payment_methods,
                    'offline_payment_methods' => $offline_payment_methods
                ]
            ];
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     */
    public function withResponse($request, $response)
    {
        $response->header('Content-Type', 'application/hal+json');
    }
}
