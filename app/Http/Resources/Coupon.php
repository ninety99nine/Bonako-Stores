<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Coupon extends JsonResource
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
            'description' => $this->description,
            'active' => $this->active,
            'always_apply' => $this->always_apply,
            'uses_code' => $this->uses_code,
            'code' => $this->code,
            'allow_free_delivery' => $this->allow_free_delivery,
            'currency' => $this->currency,
            'discount_rate_type' => $this->discount_rate_type,
            'fixed_rate' => $this->fixed_rate,
            'percentage_rate' => $this->percentage_rate,

            'allow_discount_on_minimum_total' => $this->allow_discount_on_minimum_total,
            'discount_on_minimum_total' => $this->discount_on_minimum_total,
            'allow_discount_on_total_items' => $this->allow_discount_on_total_items,
            'discount_on_total_items' => $this->discount_on_total_items,
            'allow_discount_on_total_unique_items' => $this->allow_discount_on_total_unique_items,
            'discount_on_total_unique_items' => $this->discount_on_total_unique_items,

            'location_id' => $this->location_id,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                /*
                //  Link to current resource
                'self' => [
                    'href' => route('coupon', ['coupon_id' => $this->id]),
                    'title' => 'This coupon'
                ]
                */

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
