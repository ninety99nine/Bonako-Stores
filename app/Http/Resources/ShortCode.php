<?php

namespace App\Http\Resources;

use App\Http\Resources\Store as StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\InstantCart as InstantCartResource;

class ShortCode extends JsonResource
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
            'action' => $this->action,
            'expires_at' => $this->expires_at,
            'dialing_code' => $this->dialing_code,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'is_creator' => $this->is_creator,
                'is_expired' => $this->is_expired,
                'resource_type' => $this->resource_type
            ],

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
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
                //  If store, then its used to visit a store
                case 'store':
                    return new StoreResource( $this->owner );

                //  If instant cart, then its used to visit an instant cart
                case 'instant_cart':
                    return new InstantCartResource( $this->owner );

                //  If transaction, then its used to pay for a transaction of a subscription or order
                case 'transaction':
                    return new TransactionResource( $this->owner );

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
