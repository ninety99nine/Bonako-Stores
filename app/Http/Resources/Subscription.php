<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\SubscriptionPlan as SubscriptionPlanResource;

class Subscription extends JsonResource
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
            'subscription_plan_id' => $this->subscription_plan_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'active' => $this->active,
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,

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

            '_embedded' => [

                'transaction' => new TransactionResource($this->transaction),
                'subscription_plan' => new SubscriptionPlanResource($this->subscriptionPlan),

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
