<?php

namespace App\Http\Resources;

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
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'store_id' => $this->store_id,
            'location_id' => $this->location_id,

            //  Attributes
            'short_code' => $this->short_code,

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
                    'href' => route('instant-cart', ['instant_cart_id' => $this->id]),
                    'title' => 'This instant cart'
                ]
                
            ],

            '_embedded' => [

                'location' => $this->location,
                'products' => $this->products,
                'coupons' => $this->coupons,
                'store' => $this->store

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
