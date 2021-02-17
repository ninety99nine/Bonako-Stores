<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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

            /*  Product Management  */
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'show_description' => $this->show_description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'visible' => $this->visible,
            'product_type_id' => $this->product_type_id,

            /*  Variation Management  */
            'allow_variants' => $this->allow_variants,
            'variant_attributes' => $this->variant_attributes,

            /*  Pricing Management  */
            'unit_regular_price' => $this->unit_regular_price,
            'unit_sale_price' => $this->unit_sale_price,
            'unit_cost' => $this->unit_cost,

            /*  Quantity Management  */
            'allow_multiple_quantity_per_order' => $this->allow_multiple_quantity_per_order,
            'allow_maximum_quantity_per_order' => $this->allow_maximum_quantity_per_order,
            'maximum_quantity_per_order' => $this->maximum_quantity_per_order,

            /*  Stock Management  */
            'allow_stock_management' => $this->allow_stock_management,
            'auto_manage_stock' => $this->auto_manage_stock,
            'stock_quantity' => $this->stock_quantity,

            /*  Ownership Management  */
            'parent_product_id' => $this->parent_product_id,
            'user_id' => $this->user_id,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'on_sale' => $this->on_sale,
                'unit_price' => $this->unit_price,
                'unit_profit' => $this->unit_profit,
                'unit_sale_discount' => $this->unit_sale_discount
            ],

            /*  Resource Links */
            '_links' => [
                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                'self' => [
                    'href' => route('product-show', ['product_id' => $this->id]),
                    'title' => 'This product',
                ],

                //  Link to the product locations
                'bos:locations' => [
                    'href' => route('product-locations', ['product_id' => $this->id]),
                    'title' => 'The product locations'
                ],

                /*
                //  Link to current resource

                //  Link to the product variations
                'bos:variations' => [
                    'href' => route('product-variations', ['product_id' => $this->id]),
                    'title' => 'The product variations',
                    'total' => $this->variations()->count(),
                ],
                */
            ],

            /*  Embedded  */
            '_embedded' => [
                'variables' => $this->whenLoaded('variables'),
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
