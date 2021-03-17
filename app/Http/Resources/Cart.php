<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ItemLines as ItemLinesResource;
use App\Http\Resources\CouponLines as CouponLinesResource;

class Cart extends JsonResource
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
        //  Set the item lines (Extract the nested item lines)
        $item_lines = (new ItemLinesResource($this->itemLines ?? []))->collection;

        //  Set the coupon lines (Extract the nested coupon lines)
        $coupon_lines = (new CouponLinesResource($this->couponLines ?? []))->collection;

        return [

            'id' => $this->id,
            'active' => $this->active,
            'currency' => $this->currency,
            'sub_total' => $this->sub_total,
            'coupon_total' => $this->coupon_total,
            'sale_discount_total' => $this->sale_discount_total,
            'coupon_and_sale_discount_total' => $this->coupon_and_sale_discount_total,
            'allow_free_delivery' => $this->allow_free_delivery,
            'delivery_fee' => $this->delivery_fee,
            'grand_total' => $this->grand_total,
            'total_items' => $this->total_items,
            'total_unique_items' => $this->total_unique_items,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

            ],

            '_embedded' => [
                'coupon_lines' => $coupon_lines,
                'item_lines' => $item_lines,
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
