<?php

namespace App\Http\Resources;

use App\Http\Resources\Cart as CartResource;
use Illuminate\Http\Resources\Json\JsonResource;

class InstantCart extends JsonResource
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
            'active' => $this->active,
            'name' => $this->name,
            'description' => $this->description,
            'location_id' => $this->location_id,

            /*  Free Delivery  */
            'allow_free_delivery' => $this->allow_free_delivery,

            /*  Stock Management  */
            'allow_stock_management' => $this->allow_stock_management,
            'stock_quantity' => $this->stock_quantity,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'resource_type' => $this->resource_type,

                'visit_short_code' => !empty($this->visitShortCode) ? collect($this->visitShortCode)->only(['dialing_code', 'expires_at']) : null,
                'has_visit_short_code' => !empty($this->visitShortCode) ? true : false,

                'payment_short_code' => !empty($this->paymentShortCode) ? collect($this->paymentShortCode)->only(['dialing_code', 'expires_at']) : null,
                'has_payment_short_code' => !empty($this->paymentShortCode) ? true : false,

                'subscription' => !empty($this->myActiveSubscription) ? collect($this->myActiveSubscription)->only(['id', 'subscription_plan_id', 'start_at', 'end_at']) : null,
                'has_subscription' => !empty($this->myActiveSubscription) ? true : false,

                'has_stock' => $this->has_stock,
            ],

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                //  Link to current resource
                'self' => [
                    'href' => route('instant-cart-show', ['instant_cart_id' => $this->id]),
                    'title' => 'This instant cart'
                ],

                //  Link to the location
                'bos:location' => [
                    'href' => route('instant-cart-location', ['instant_cart_id' => $this->id]),
                    'title' => 'The instant cart location',
                ],

                //  Link to subscribe to instant cart
                'bos:subscribe' => [
                    'href' => route('instant-cart-subscribe', ['instant_cart_id' => $this->id]),
                    'title' => 'The POST route to create an instant cart subscription'
                ],

                //  Link to create store payment short code
                'bos:generate-payment-shortcode' => [
                    'href' => route('instant-cart-generate-payment-shortcode', ['instant_cart_id' => $this->id]),
                    'title' => 'The POST route to create an instant cart payment shortcode'
                ],

            ],

            '_embedded' => [

                'cart' => new CartResource( $this->cart ),

            ]

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
