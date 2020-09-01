<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
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
            'number' => $this->number,
            'currency' => $this->currency,
            'created_date' => $this->created_date,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'item_lines' => $this->item_lines,
            'coupon_lines' => $this->coupon_lines,
            'sub_total' => $this->sub_total,
            'coupon_total' => $this->coupon_total,
            'discount_total' => $this->discount_total,
            'coupon_and_discount_total' => $this->coupon_and_discount_total,
            'delivery_fee' => $this->delivery_fee,
            'grand_total' => $this->grand_total,
            'customer_info' => $this->customer_info,
            'delivery_info' => $this->delivery_info,
            'checkout_method' => $this->checkout_method,
            'store_id' => $this->store_id,
            'location_id' => $this->location_id,

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
                    'href' => route('order', ['order_id' => $this->id]),
                    'title' => 'This order'
                ]
                
            ],

            '_embedded' => [
                
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
