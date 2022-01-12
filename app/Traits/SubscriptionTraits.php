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
    public function createResource($data = [], $model = null, $user = null)
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

                //  Set the user as the user responsible for owning this resource
                $template['user_id'] = $user->id;

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

                //  If we have an owning model
                if( $model ){

                    //  Update the transaction owner id and owner type
                    $this->subscription->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                    ]);

                }

                //  Generate the resource creation report
                $this->subscription->generateResourceCreationReport($model);

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
     *  This method returns a list of subscriptions
     */
    public function getResources($data = [], $builder = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data (CommanTraits)
            $this->getResourcesValidation($data);

            //  If we already have an eloquent builder defined
            if( is_object($builder) ){

                //  Set the subscriptions to this eloquent builder
                $subscriptions = $builder;

            }else{

                //  Get the subscriptions
                $subscriptions = \App\Subscription::with(['subscriptionPlan', 'transaction'])->latest();

            }

            //  Filter the subscriptions
            $subscriptions = $this->filterResources($data, $subscriptions);

            //  Return subscriptions
            return $this->collectionResponse($data, $subscriptions, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the subscriptions by search or status
     */
    public function filterResources($data = [], $subscriptions)
    {
        //  If we need to search for specific subscriptions
        if ( isset($data['search']) && !empty($data['search']) ) {

            $subscriptions = $this->filterResourcesBySearch($data, $subscriptions);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $subscriptions = $this->filterResourcesByStatus($data, $subscriptions);

        }

        //  Return the subscriptions
        return $subscriptions;
    }

    /**
     *  This method filters the subscriptions by search
     */
    public function filterResourcesBySearch($data = [], $subscriptions)
    {
        //  Set the search term e.g "Subscription 1"
        $search_term = $data['search'] ?? null;

        //  Return searched subscriptions otherwise original subscriptions
        return empty($search_term) ? $subscriptions : $subscriptions->search($search_term);

    }

    /**
     *  This method filters the subscriptions by status
     */
    public function filterResourcesByStatus($data = [], $subscriptions)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["active", "inactive", ...] or "active,inactive, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "active,inactive"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["active", "inactive"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["active", "inactive"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " active " to "Active"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $subscriptions && count($statuses) ) {

            if( in_array('Active', $statuses) ){

                $subscriptions = $subscriptions->active();

            }elseif( in_array('Inactive', $statuses) ){

                $subscriptions = $subscriptions->inActive();

            }

        }

        //  Return the subscriptions
        return $subscriptions;
    }

    /**
     *  This method generates a subscription creation report
     */
    public function generateResourceCreationReport($model)
    {
        $subscription_plan = $this->subscriptionPlan;

        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceCreationReport($this, [
            'name' => $subscription_plan->name,
            'frequency' => $subscription_plan->frequency,
            'duration' => $subscription_plan->duration,
            'price' => $subscription_plan->price['amount'],
            'owner_type' => $model->resource_type,
        ]);
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
                'amount' => $subscription_plan->price['amount'],

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
                'subscription_plan_id' => 'required|regex:/^[0-9]+$/i'
            ];

            //  Set validation messages
            $messages = [
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
