<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ShortCodes as ShortCodesResource;
use App\Http\Resources\Subscriptions as SubscriptionsResource;

class Store extends JsonResource
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'online' => $this->online,
            'user_id' => $this->user_id,
            'currency' => $this->currency,
            'offline_message' => $this->offline_message,
            'minimum_stock_quantity' => $this->minimum_stock_quantity,

            'is_favourite' => $this->total_favourite_locations ? true : false,
            'total_favourite_locations' => $this->total_favourite_locations,

            'has_subscribed' => count($this->myActiveSubscriptions) ? true: false,
            'has_visit_short_codes' => count($this->visitShortCodes) ? true: false,
            'has_payment_short_codes' => count($this->paymentShortCodes) ? true: false,

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
                    'href' => route('store', ['store_id' => $this->id]),
                    'title' => 'This store',
                ],

                //  Link to locations assigned to this user
                'bos:my-store-locations' => [
                    'href' => route('my-store-locations', ['store_id' => $this->id]),
                    'title' => 'The store locations that are assigned to this user'
                ],

                //  Link to the default location assigned to this user
                'bos:my-store-default-location' => [
                    'href' => route('my-store-default-location', ['store_id' => $this->id]),
                    'title' => 'The default location that is assigned to this user'
                ],

                //  Link to the store locations
                'bos:locations' => [
                    'href' => route('store-locations', ['store_id' => $this->id]),
                    'title' => 'The store locations',
                    'total' => $this->locations()->count(),
                ],

                //  Link to the store favourite locations
                'bos:favourite_locations' => [
                    'href' => route('store-favourite-locations', ['store_id' => $this->id]),
                    'title' => 'The store favourite locations',
                    'total' => $this->locations()->asFavourite(auth()->user()->id)->count(),
                ],

                //  Link to the instant carts
                'bos:products' => [
                    'href' => route('store-products', ['store_id' => $this->id]),
                    'title' => 'The store products',
                    'total' => $this->products()->count(),
                ],

                //  Link to the instant carts
                'bos:coupons' => [
                    'href' => route('store-coupons', ['store_id' => $this->id]),
                    'title' => 'The store coupons',
                    'total' => $this->coupons()->count(),
                ],

                //  Link to the instant carts
                'bos:instant_carts' => [
                    'href' => route('store-instant-carts', ['store_id' => $this->id]),
                    'title' => 'The store instant carts',
                    'total' => $this->instantCarts()->count(),
                ],

                //  Link to subscribe to store
                'bos:subscribe' => [
                    'href' => route('store-favourite', ['store_id' => $this->id]),
                    'title' => 'The POST route to create a store subscription'
                ],

                //  Link to create store payment short code
                'bos:generate-payment-shortcode' => [
                    'href' => route('store-generate-payment-shortcode', ['store_id' => $this->id]),
                    'title' => 'The POST route to create a store payment shortcode'
                ],

                //  Link to add or remove a favourite store
                'bos:add_or_remove_favourite_store' => [
                    'href' => route('store-favourite', ['store_id' => $this->id]),
                    'title' => 'The POST route to mark or unmark a favourite store'
                ],

            ],

            '_attributes' => [
                'subscription' => $this->subscription,
                'visit_short_code' => $this->visit_short_code,
                'visit_short_code' => $this->visit_short_code,
                'payment_short_code' => $this->visit_short_code,
                'has_subscribed' => $this->has_subscribed,
                'has_visit_short_code' => $this->has_payment_short_code,
                'has_payment_short_code' => $this->has_payment_short_code,
            ],

            /*  Embedded Resources */
            '_embedded' => [
                'coupons' => $this->coupons,
                'visit_short_codes' => new ShortCodesResource( $this->visitShortCodes ),
                'payment_short_codes' => new ShortCodesResource( $this->paymentShortCodes ),
                'my_active_subscriptions' => new SubscriptionsResource( $this->myActiveSubscriptions ),
            ],
        ];
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
