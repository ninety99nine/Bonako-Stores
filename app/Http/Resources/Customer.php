<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Customer extends JsonResource
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
            'user_id' => $this->user_id,
            'total_coupons_used_on_checkout' => $this->total_coupons_used_on_checkout,
            'total_instant_carts_used_on_checkout' => $this->total_instant_carts_used_on_checkout,
            'total_adverts_used_on_checkout' => $this->total_adverts_used_on_checkout,
            'total_orders_placed_by_customer' => $this->total_orders_placed_by_customer,
            'total_orders_placed_by_store' => $this->total_orders_placed_by_store,
            'checkout_grand_total' => $this->checkout_grand_total,
            'checkout_sub_total' => $this->checkout_sub_total,
            'checkout_coupons_total' => $this->checkout_coupons_total,
            'checkout_sale_discount_total' => $this->checkout_sale_discount_total,
            'checkout_coupons_and_sale_discount_total' => $this->checkout_coupons_and_sale_discount_total,
            'checkout_delivery_fee' => $this->checkout_delivery_fee,
            'total_free_delivery_on_checkout' => $this->total_free_delivery_on_checkout,
            'checkout_total_items' => $this->checkout_total_items,
            'checkout_total_unique_items' => $this->checkout_total_unique_items,
            'location_id' => $this->location_id,

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

                'self' => [
                    'href' => route('customer-show', ['customer_id' => $this->id]),
                    'title' => 'This customer',
                ],

                //  Link to the orders
                'bos:orders' => [
                    'href' => route('customer-orders', ['customer_id' => $this->id]),
                    'title' => 'The customer orders',
                ],

            ],

            '_embedded' => [

                'user' => new UserResource($this->user),

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
