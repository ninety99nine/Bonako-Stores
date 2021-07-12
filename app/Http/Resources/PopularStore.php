<?php

namespace App\Http\Resources;

use App\Http\Resources\Store as StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PopularStore extends JsonResource
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
            'store_id' => $this->store_id,
            'arrangement' => $this->arrangement,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'visible' => $this->visible,
                'has_expired' => $this->has_expired,
                'resource_type' => $this->resource_type
            ],

            /*  Resource Links */
            '_links' => [
                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                //  Link to current resource
                'self' => [
                    'href' => route('popular-store-show', ['popular_store_id' => $this->id]),
                    'title' => 'This popular store',
                ]

            ],

            /*  Embedded Resources */
            '_embedded' => [

                'store' => new StoreResource( $this->store )

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
