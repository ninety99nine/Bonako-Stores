<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Product as ProductResource;

class ItemLine extends JsonResource
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
            'is_free' => $this->is_free,
            'currency' => $this->currency,
            'unit_regular_price' => $this->unit_regular_price,
            'unit_sale_price' => $this->unit_sale_price,
            'unit_price' => $this->unit_price,
            'unit_sale_discount' => $this->unit_sale_discount,
            'sub_total' => $this->sub_total,
            'sale_discount_total' => $this->sale_discount_total,
            'grand_total' => $this->grand_total,
            'quantity' => $this->quantity,

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
                
                'product' => new ProductResource($this->product),

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
