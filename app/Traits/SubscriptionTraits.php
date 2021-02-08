<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Http\Resources\Subscription as SubscriptionResource;
use App\Http\Resources\Subscriptions as SubscriptionsResource;

trait SubscriptionTraits
{
    public $subscription = null;

    /**
     *  This method transforms a collection or single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            // If this instance is a collection or a paginated collection
            if( $collection instanceof \Illuminate\Support\Collection ||
                $collection instanceof \Illuminate\Pagination\LengthAwarePaginator ){

                //  Transform the multiple instances
                return new SubscriptionsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Subscription){

                //  Transform the single instance
                return new SubscriptionResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new subscription
     */
    public function createResource($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the subscription plan id
            $subscription_plan_id = $data['subscription_plan_id'] ?? null;

            //  Retrieve the subscription plan
            $subscription_plan = (new \App\SubscriptionPlan())->getResource($subscription_plan_id);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  Set the subscription "start_at" datetime
            $start_at = (Carbon::now())->format('Y-m-d H:i:s');

            //  If the subscription plan frequency is measured in days
            if( $subscription_plan->frequency == 'day' ){

                //  End the subscription plan after the given duration of days
                $end_at = Carbon::parse($start_at)->addDays( $subscription_plan->duration );

            //  If the subscription plan frequency is measured in weeks
            }elseif( $subscription_plan->frequency == 'week' ){

                //  End the subscription plan after the given duration of weeks
                $end_at = Carbon::parse($start_at)->addWeeks( $subscription_plan->duration );

            //  If the subscription plan frequency is measured in months
            }elseif( $subscription_plan->frequency == 'month' ){

                //  End the subscription plan after the given duration of months
                $end_at = Carbon::parse($start_at)->addMonths( $subscription_plan->duration );

                //  If the subscription plan frequency is measured in years
            }elseif( $subscription_plan->frequency == 'year' ){

                //  End the subscription plan after the given duration of years
                $end_at = Carbon::parse($start_at)->addYears( $subscription_plan->duration );

            }else{

                $end_at = null;

            }

            //  If the current authenticated user is a Super Admin and the "user_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['user_id']) ){

                //  Set the "user_id" provided as the user responsible for owning this resource
                $template['user_id'] = $data['user_id'];

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['user_id'] = auth('api')->user()->id;

            }

            //  Set the "start at" datetime
            $template['start_at'] = $start_at;

            //  Set the "start at" datetime
            $template['end_at'] = $end_at;

            /**
             *  Create a new resource
             */
            $this->subscription = $this->create($template);

            //  If created successfully
            if ( $this->subscription ) {

                //  Generate a new transaction
                $this->subscription->createResourceTransaction($data, $subscription_plan);

                //  Return a fresh instance
                return $this->subscription->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    /**
     *  This method creates a new subscription transaction
     */
    public function createResourceTransaction($data = [], $subscription_plan)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Merge the data with additional fields
            $data = array_merge($data, [

                //  Set the transaction type on the data
                'type' => 'subscription',

                //  Set the transaction amount on the data
                'amount' => $subscription_plan->price,

                //  Set the transaction description on the data
                'description' => 'Subscription for '.$subscription_plan->name

            ]);

            //  Set the transaction owning model
            $model = $this;

            //  Create a new transaction
            return ( new \App\Transaction() )->createResource($data, $model);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates creating a new resource
     */
    public function createResourceValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [
                'user_id' => 'sometimes|regex:/^[0-9]+$/i',
                'store_id' => 'required|regex:/^[0-9]+$/i',
                'subscription_plan_id' => 'required|regex:/^[0-9]+$/i'
            ];

            //  Set validation messages
            $messages = [
                'store_id.regex' => 'The user id must be a valid number e.g 123',

                'store_id.required' => 'The store id is required to create a subscription',
                'store_id.regex' => 'The store id must be a valid number e.g 123',

                'subscription_plan_id.required' => 'The subscription plan id is required to create a subscription',
                'subscription_plan_id.regex' => 'The store id must be a valid number e.g 1'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
