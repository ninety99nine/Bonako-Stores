<?php

namespace App\Http\Resources;

use App\Http\Resources\Cart as CartResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Status as StatusResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DeliveryLine as DeliveryLineResource;

class Order extends JsonResource
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
            'number' => $this->number,
            'delivery_verified' => $this->delivery_verified,
            'delivery_verified_at' => $this->delivery_verified_at,
            'customer_id' => $this->customer_id,
            'location_id' => $this->location_id,
            'cancellation_reason' => $this->cancellation_reason,
            'request_customer_rating_at' => $this->request_customer_rating_at,

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
                    'href' => route('order-show', ['order_id' => $this->id]),
                    'title' => 'This order'
                ],

                //  Link to fulfil order
                'bos:fulfil' => [
                    'href' => route('order-fulfil', ['order_id' => $this->id]),
                    'title' => 'The POST route to fulfil this order'
                ],

                //  Link to cancel order
                'bos:cancel' => [
                    'href' => route('order-cancel', ['order_id' => $this->id]),
                    'title' => 'The POST route to cancel this order'
                ],

                //  Link to shared locations
                'bos:shared-locations' => [
                    'href' => route('order-shared-locations', ['order_id' => $this->id]),
                    'title' => 'The order shared locations',
                ],

                //  Link to received locations
                'bos:received-location' => [
                    'href' => route('order-received-location', ['order_id' => $this->id]),
                    'title' => 'The order received location',
                ]

            ],

            '_attributes' => [
                'is_paid' => $this->isPaid(),
                'is_fulfilled' => $this->isFulfilled()
            ],

            '_embedded' => [

                'status' => new StatusResource( $this->status ),
                'payment_status' => new StatusResource( $this->paymentStatus ),
                'fulfillment_status' => new StatusResource( $this->fulfillmentStatus ),
                'active_cart' => new CartResource( $this->activeCart ),
                'delivery_line' => new DeliveryLineResource( $this->deliveryLine ),
                'customer' => new UserResource( $this->customer ),

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
