<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            return [
                'id' => $this->id,
                'name' => $this->name,
                'online' => $this->online,
                'user_id' => $this->user_id,
                'store_id' => $this->store_id,
                'about_us' => $this->about_us,
                'contact_us' => $this->contact_us,
                'abbreviation' => $this->abbreviation,
                'call_to_action' => $this->call_to_action,
                'allow_delivery' => $this->allow_delivery,
                'allow_pickups' => $this->allow_pickups,
                'delivery_note' => $this->delivery_note,
                'delivery_flat_fee' => $this->delivery_flat_fee,
                'delivery_destinations' => $this->delivery_destinations,
                'pickup_destinations' => $this->pickup_destinations,
                'delivery_days' => $this->delivery_days,
                'pickup_days' => $this->pickup_days,
                'delivery_times' => $this->delivery_times,
                'pickup_note' => $this->pickup_note,
                'pickup_times' => $this->pickup_times,
                'allow_payments' => $this->allow_payments,
                'online_payment_methods' => $this->online_payment_methods,
                'offline_payment_methods' => $this->offline_payment_methods,
                'offline_message' => $this->offline_message,

                /*  Timestamp Info  */
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,

                /*  Resource Links */
                '_links' => [
                    'curies' => [
                        ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                    ],

                    //  Link to current resource
                    'self' => [
                        'href' => route('location', ['location_id' => $this->id]),
                        'title' => 'This location',
                    ],

                    //  Link to the products
                    'bos:products' => [
                        'href' => route('location-products', ['location_id' => $this->id]),
                        'title' => 'The location products',
                        'total' => $this->products()->count(),
                    ],

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
                    'bos:product-arrangement' => [
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
                    'bos:orders' => [
                        'href' => route('location-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders',
                        'total' => $this->orders()->count(),
                    ],

                    //  Link to the fulfilled orders
                    'bos:fulfilled-orders' => [
                        'href' => route('location-fulfilled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are fulfilled',
                        'total' => $this->orders()->fulfilled()->count(),
                    ],

                    //  Link to the unfulfilled orders
                    'bos:unfulfilled-orders' => [
                        'href' => route('location-unfulfilled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are unfulfilled',
                        'total' => $this->orders()->unfulfilled()->count(),
                    ],

                    //  Link to the cancelled orders
                    'bos:cancelled-orders' => [
                        'href' => route('location-cancelled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are cancelled',
                        'total' => $this->orders()->cancelled()->count(),
                    ],

                    //  Link to my orders
                    'bos:my-orders' => [
                        'href' => route('location-my-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that have been placed by this user',
                        'total' => $this->orders()->userIsCustomer()->count(),
                    ],

                    //  Link to my fulfilled orders
                    'bos:my-fulfilled-orders' => [
                        'href' => route('location-my-fulfilled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are fulfilled for this user',
                        'total' => $this->orders()->userIsCustomer()->fulfilled()->count(),
                    ],

                    //  Link to my unfulfilled orders
                    'bos:my-unfulfilled-orders' => [
                        'href' => route('location-my-unfulfilled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are unfulfilled for this user',
                        'total' => $this->orders()->userIsCustomer()->unfulfilled()->count(),
                    ],

                    //  Link to my cancelled orders
                    'bos:my-cancelled-orders' => [
                        'href' => route('location-my-cancelled-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are cancelled for this user',
                        'total' => $this->orders()->userIsCustomer()->cancelled()->count(),
                    ],

                    //  Link to the unrated orders
                    'bos:unrated-orders' => [
                        'href' => route('location-unrated-orders', ['location_id' => $this->id]),
                        'title' => 'The location orders that are not rated',
                        'total' => $this->orders()->requireRating()->count(),
                    ],
                ],
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
