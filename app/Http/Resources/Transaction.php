<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Status as StatusResource;
use App\Http\Resources\PaymentMethod as PaymentMethodResource;

class Transaction extends JsonResource
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
            'type' => $this->type,
            'number' => $this->number,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'payment_method_id' => $this->payment_method_id,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'resource_type' => $this->resource_type
            ],

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ]

            ],

            /*  Embedded  */
            '_embedded' => [
                'status' => new StatusResource( $this->status ),
                'payment_method' => new PaymentMethodResource( $this->paymentMethod )
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
