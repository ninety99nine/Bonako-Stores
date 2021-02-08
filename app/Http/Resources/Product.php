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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'type' => $this->type,
            'cost_per_item' => $this->cost_per_item,
            'unit_regular_price' => $this->unit_regular_price,
            'unit_sale_price' => $this->unit_sale_price,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'stock_quantity' => $this->stock_quantity,
            'allow_stock_management' => $this->allow_stock_management,
            'auto_manage_stock' => $this->auto_manage_stock,
            'variant_attributes' => $this->variant_attributes,
            'allow_variants' => $this->allow_variants,
            'allow_downloads' => $this->allow_downloads,
            'show_on_store' => $this->show_on_store,
            'is_new' => $this->is_new,
            'is_featured' => $this->is_featured,
            'parent_product_id' => $this->parent_product_id,

            /*  Attributes  */
            'on_sale' => $this->on_sale,
            'unit_price' => $this->unit_price,
            'unit_sale_discount' => $this->unit_sale_discount,

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
                    'href' => route('product', ['product_id' => $this->id]),
                    'title' => 'This product',
                ],

                //  Link to the product variations
                'bos:variations' => [
                    'href' => route('product-variations', ['product_id' => $this->id]),
                    'title' => 'The product variations',
                    'total' => $this->variations()->count(),
                ],
                */
            ],

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
