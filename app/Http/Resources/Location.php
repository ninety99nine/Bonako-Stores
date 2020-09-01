<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Location extends JsonResource
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
            'store_id' => $this->store_id,
            'about_us' => $this->about_us,
            'contact_us' => $this->contact_us,
            'abbreviation' => $this->abbreviation,
            'call_to_action' => $this->call_to_action,
            'allow_delivery' => $this->allow_delivery,
            'allow_pickups' => $this->allow_pickups,
            'delivery_note' => $this->delivery_note,
            'delivery_flat_fee' => $this->delivery_flat_fee,
            'delivery_destinations' => $this->delivery_destinations,
            'pickup_destinations' => $this->pickup_destinations,
            'delivery_days' => $this->delivery_days,
            'pickup_days' => $this->pickup_days,
            'delivery_times' => $this->delivery_times,
            'pickup_note' => $this->pickup_note,
            'pickup_times' => $this->pickup_times,
            'allow_payments' => $this->allow_payments,
            'online_payment_methods' => $this->online_payment_methods,
            'offline_payment_methods' => $this->offline_payment_methods,
            'offline_message' => $this->offline_message,

            

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
                    'href' => route('location', ['location_id' => $this->id]),
                    'title' => 'This location'
                ],

                //  Link to the products
                'bos:products' => [
                    'href' => route('location-products', ['location_id' => $this->id]),
                    'title' => 'The location products',
                    'total' => $this->products()->count()
                ],

                //  Link to the create products
                'bos:product-create' => [
                    'href' => route('product-create'),
                    'title' => 'The POST route to create a new product'
                ]
                                
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
