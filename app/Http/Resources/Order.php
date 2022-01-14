<?php

namespace App\Http\Resources;

use App\Http\Resources\Cart as CartResource;
use App\Http\Resources\Status as StatusResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer as CustomerResource;
use App\Http\Resources\Transaction as TransactionResource;
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
            'delivery_verified_by' => $this->delivery_verified_by,
            'delivery_verified_by_user_id' => $this->delivery_verified_by_user_id,
            'submitted_by_store_user' => $this->submitted_by_store_user,
            'store_user_id' => $this->store_user_id,
            'customer_id' => $this->customer_id,
            'location_id' => $this->location_id,
            'cancellation_reason' => $this->cancellation_reason,
            'request_customer_rating_at' => $this->request_customer_rating_at,

            /*  Timestamp Info  */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            /*  Attributes  */
            '_attributes' => [
                'is_paid' => $this->is_paid,
                'is_delivered' => $this->is_delivered,
                'time_elapsed_to_delivery_verified' => $this->time_elapsed_to_delivery_verified,
                'requires_delivery_confirmation_code' => $this->requires_delivery_confirmation_code,

                'resource_type' => $this->resource_type,
                'delivery_verified_description' => $this->delivery_verified_description,

                'payment_short_code' => !empty($this->paymentShortCode) ? collect($this->paymentShortCode)->only(['dialing_code', 'updated_at', 'expires_at']) : null,
                'has_payment_short_code' => !empty($this->paymentShortCode) ? true : false,
            ],

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

                //  Link to deliver order
                'bos:deliver' => [
                    'href' => route('order-deliver', ['order_id' => $this->id]),
                    'title' => 'The POST route to deliver this order'
                ],

                //  Link to cancel order
                'bos:cancel' => [
                    'href' => route('order-cancel', ['order_id' => $this->id]),
                    'title' => 'The POST route to cancel this order'
                ],

                //  Link to send payment request
                'bos:payment_request' => [
                    'href' => route('order-payment-request', ['order_id' => $this->id]),
                    'title' => 'The POST route to send payment request to customer'
                ],

                //  Link to pay
                'bos:pay' => [
                    'href' => route('order-pay', ['order_id' => $this->id]),
                    'title' => 'The POST route to pay order'
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
                ],

                //  Link to store
                'bos:store' => [
                    'href' => route('order-store', ['order_id' => $this->id]),
                    'title' => 'The order store',
                ],

                //  Link to create transaction
                'bos:transactions' => [
                    'href' => route('order-transactions-create', ['order_id' => $this->id]),
                    'title' => 'The order transactions',
                ]

            ],

            /*  Embedded  */
            '_embedded' => [

                'status' => new StatusResource( $this->status ),
                'payment_status' => new StatusResource( $this->paymentStatus ),
                'delivery_status' => new StatusResource( $this->deliveryStatus ),
                'active_cart' => new CartResource( $this->activeCart ),
                'delivery_line' => new DeliveryLineResource( $this->deliveryLine ),
                'transaction' => new TransactionResource( $this->transaction ),
                'customer' => new CustomerResource( $this->customer ),

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
