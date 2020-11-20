<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Store extends JsonResource
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
            'online' => $this->online,
            'user_id' => $this->user_id,
            'currency' => $this->currency,
            'offline_message' => $this->offline_message,
            'minimum_stock_quantity' => $this->minimum_stock_quantity,

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
                    'href' => route('store', ['store_id' => $this->id]),
                    'title' => 'This store',
                ],

                //  Link to the store locations
                'bos:locations' => [
                    'href' => route('store-locations', ['store_id' => $this->id]),
                    'title' => 'The store locations',
                    'total' => $this->locations()->count(),
                ],

                //  Link to the instant carts
                'bos:products' => [
                    'href' => route('store-products', ['store_id' => $this->id]),
                    'title' => 'The store products',
                    'total' => $this->products()->count(),
                ],

                //  Link to the instant carts
                'bos:coupons' => [
                    'href' => route('store-coupons', ['store_id' => $this->id]),
                    'title' => 'The store coupons',
                    'total' => $this->coupons()->count(),
                ],

                //  Link to the instant carts
                'bos:instant_carts' => [
                    'href' => route('store-instant-carts', ['store_id' => $this->id]),
                    'title' => 'The store instant carts',
                    'total' => $this->instantCarts()->count(),
                ],

                //  Link to the create products
                'bos:product-create' => [
                    'href' => route('product-create'),
                    'title' => 'The POST route to create a new product',
                ],
            ],

            /*  Embedded Resources */
            '_embedded' => [
                'coupons' => $this->coupons,
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
