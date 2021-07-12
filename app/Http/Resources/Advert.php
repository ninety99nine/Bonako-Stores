<?php

namespace App\Http\Resources;

use App\Http\Resources\Store as StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Location as LocationResource;
use App\Http\Resources\InstantCart as InstantCartResource;

class Advert extends JsonResource
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
            'title' => $this->title,
            'message' => $this->message,
            'arrangement' => $this->arrangement,
            'call_to_action' => $this->call_to_action,
            'allow_limited_views' => $this->allow_limited_views,
            'limited_views' => $this->limited_views,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,

            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,

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
                    'href' => route('advert-show', ['advert_id' => $this->id]),
                    'title' => 'This advert',
                ]

            ],

            /*  Embedded Resources */
            '_embedded' => [

                'owner' => $this->owner()

            ]

        ];
    }

    public function owner()
    {
        if( $this->owner ){
            switch ($this->owner->resource_type) {
                case 'store':
                    return new StoreResource( $this->owner );
                case 'location':
                    return new LocationResource( $this->owner );
                case 'instant_cart':
                    return new InstantCartResource( $this->owner );
                break;
            }
        }
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
