<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ShortCodes as ShortCodesResource;
use App\Http\Resources\Subscriptions as SubscriptionsResource;

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
            'offline_message' => $this->offline_message,
            'allow_sending_merchant_sms' => $this->allow_sending_merchant_sms,

            'is_favourite' => $this->total_favourite_locations ? true : false,
            'total_favourite_locations' => $this->total_favourite_locations,

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
                    'href' => route('store-show', ['store_id' => $this->id]),
                    'title' => 'This store',
                ],

                //  Link to locations assigned to this user
                'bos:my-store-locations' => [
                    'href' => route('my-store-locations', ['store_id' => $this->id]),
                    'title' => 'The store locations that are assigned to this user'
                ],

                //  Link to the default location assigned to this user
                'bos:my-store-default-location' => [
                    'href' => route('my-store-default-location', ['store_id' => $this->id]),
                    'title' => 'The default location that is assigned to this user'
                ],

                //  Link to the store locations
                'bos:locations' => [
                    'href' => route('store-locations', ['store_id' => $this->id]),
                    'title' => 'The store locations',
                ],

                //  Link to subscribe to store
                'bos:subscribe' => [
                    'href' => route('store-subscribe', ['store_id' => $this->id]),
                    'title' => 'The POST route to create a store subscription'
                ],

                //  Link to create store payment short code
                'bos:generate-payment-shortcode' => [
                    'href' => route('store-generate-payment-shortcode', ['store_id' => $this->id]),
                    'title' => 'The POST route to create a store payment shortcode'
                ],

            ],

            '_attributes' => [
                'subscription' => $this->subscription,
                'visit_short_code' => $this->visit_short_code,
                'payment_short_code' => $this->payment_short_code,

                'has_subscribed' => $this->has_subscribed,
                'has_visit_short_code' => $this->has_visit_short_code,
                'has_payment_short_code' => $this->has_payment_short_code,
            ],

            /*  Embedded Resources */
            '_embedded' => [

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
