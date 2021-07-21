<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Coupon extends JsonResource
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
            'description' => $this->description,
            'active' => $this->active,
            'apply_discount' => $this->apply_discount,
            'activation_type' => $this->activation_type,
            'code' => $this->code,
            'allow_free_delivery' => $this->allow_free_delivery,
            'currency' => $this->currency,
            'discount_rate_type' => $this->discount_rate_type,
            'fixed_rate' => $this->fixed_rate,
            'percentage_rate' => $this->percentage_rate,
            'allow_discount_on_minimum_total' => $this->allow_discount_on_minimum_total,
            'discount_on_minimum_total' => $this->discount_on_minimum_total,
            'allow_discount_on_total_items' => $this->allow_discount_on_total_items,
            'discount_on_total_items' => $this->discount_on_total_items,
            'allow_discount_on_total_unique_items' => $this->allow_discount_on_total_unique_items,
            'discount_on_total_unique_items' => $this->discount_on_total_unique_items,
            'allow_discount_on_start_datetime' => $this->allow_discount_on_start_datetime,
            'discount_on_start_datetime' => $this->discount_on_start_datetime,
            'allow_discount_on_end_datetime' => $this->allow_discount_on_end_datetime,
            'discount_on_end_datetime' => $this->discount_on_end_datetime,
            'allow_usage_limit' => $this->allow_usage_limit,
            'usage_limit' => $this->usage_limit,
            'usage_quantity' => $this->usage_quantity,
            'quantity_remaining' => $this->quantity_remaining,
            'has_quantity_remaining' => $this->has_quantity_remaining,

            'allow_discount_on_times' => $this->allow_discount_on_times,
            'discount_on_times' => $this->discount_on_times,
            'allow_discount_on_days_of_the_week' => $this->allow_discount_on_days_of_the_week,
            'discount_on_days_of_the_week' => $this->discount_on_days_of_the_week,
            'allow_discount_on_days_of_the_month' => $this->allow_discount_on_days_of_the_month,
            'discount_on_days_of_the_month' => $this->discount_on_days_of_the_month,
            'allow_discount_on_months_of_the_year' => $this->allow_discount_on_months_of_the_year,
            'discount_on_months_of_the_year' => $this->discount_on_months_of_the_year,
            'allow_discount_on_new_customer' => $this->allow_discount_on_new_customer,
            'allow_discount_on_existing_customer' => $this->allow_discount_on_existing_customer,

            'location_id' => $this->location_id,

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
                ],

                'self' => [
                    'href' => route('coupon-show', ['coupon_id' => $this->id]),
                    'title' => 'This coupon',
                ],

            ],

            '_embedded' => [

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
