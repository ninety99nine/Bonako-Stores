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
            'total_orders_placed_by_customer_on_checkout' => $this->total_orders_placed_by_customer_on_checkout,
            'total_orders_placed_by_store_on_checkout' => $this->total_orders_placed_by_store_on_checkout,
            'total_free_delivery_on_checkout' => $this->total_free_delivery_on_checkout,
            'grand_total_on_checkout' => $this->grand_total_on_checkout,
            'sub_total_on_checkout' => $this->sub_total_on_checkout,
            'sale_discount_total_on_checkout' => $this->sale_discount_total_on_checkout,
            'coupon_total_on_checkout' => $this->coupon_total_on_checkout,
            'coupon_and_sale_discount_total_on_checkout' => $this->coupon_and_sale_discount_total_on_checkout,
            'delivery_fee_on_checkout' => $this->delivery_fee_on_checkout,
            'total_items_on_checkout' => $this->total_items_on_checkout,
            'total_unique_items_on_checkout' => $this->total_unique_items_on_checkout,

            'total_coupons_used_on_conversion' => $this->total_coupons_used_on_conversion,
            'total_instant_carts_used_on_conversion' => $this->total_instant_carts_used_on_conversion,
            'total_adverts_used_on_conversion' => $this->total_adverts_used_on_conversion,
            'total_orders_placed_by_customer_on_conversion' => $this->total_orders_placed_by_customer_on_conversion,
            'total_orders_placed_by_store_on_conversion' => $this->total_orders_placed_by_store_on_conversion,
            'total_free_delivery_on_conversion' => $this->total_free_delivery_on_conversion,
            'grand_total_on_conversion' => $this->grand_total_on_conversion,
            'sub_total_on_conversion' => $this->sub_total_on_conversion,
            'sale_discount_total_on_conversion' => $this->sale_discount_total_on_conversion,
            'coupon_total_on_conversion' => $this->coupon_total_on_conversion,
            'coupon_and_sale_discount_total_on_conversion' => $this->coupon_and_sale_discount_total_on_conversion,
            'delivery_fee_on_conversion' => $this->delivery_fee_on_conversion,
            'total_items_on_conversion' => $this->total_items_on_conversion,
            'total_unique_items_on_conversion' => $this->total_unique_items_on_conversion,

            'location_id' => $this->location_id,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'resource_type' => $this->resource_type,
                'total_orders_placed_on_checkout' => $this->total_orders_placed_on_checkout,
                'total_orders_placed_on_conversion' => $this->total_orders_placed_on_conversion,
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
