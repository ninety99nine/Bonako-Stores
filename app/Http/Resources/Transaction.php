<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Status as StatusResource;
use App\Http\Resources\Subscription as SubscriptionResource;
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
            'percentage_rate' => $this->percentage_rate,
            'user_id' => $this->user_id,
            'payer_id' => $this->payer_id,
            'description' => $this->description,
            'payment_method_id' => $this->payment_method_id,

            'owner_type' => $this->owner_type,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'resource_type' => $this->resource_type,

                'payment_short_code' => !empty($this->paymentShortCode) ? collect($this->paymentShortCode)->only(['dialing_code', 'updated_at', 'expires_at']) : null,
                'has_payment_short_code' => !empty($this->paymentShortCode) ? true : false,
            ],

            /*  Resource Links */
            '_links' => [

                'curies' => [
                    ['name' => 'oq', 'href' => 'https://oqcloud.co.bw/docs/rels/{rel}', 'templated' => true],
                ],

                //  Link to transaction owning resource e.g Subscription, Order
                'bos:owning_resource' => $this->ownerLink()

            ],

            /*  Embedded  */
            '_embedded' => [
                'user' => new UserResource( $this->user ),
                'payer' => new UserResource( $this->payer ),
                'status' => new StatusResource( $this->status ),
                'payment_method' => new PaymentMethodResource( $this->paymentMethod ),
            ]

        ];
    }

    public function ownerLink()
    {
        if( $this->owner ){
            switch ($this->owner_type) {
                case 'subscription':
                    return [
                        'href' => null,     //    route('subscription-show', ['order_id' => $this->owner_id]),
                        'title' =>  null    //   'The subscription linked to this transaction'
                    ];
                case 'order':
                    return [
                        'href' => route('order-show', ['order_id' => $this->owner_id]),
                        'title' => 'The order linked to this transaction'
                    ];
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
