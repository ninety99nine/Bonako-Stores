<?php

namespace App\Traits;
use App\Http\Resources\SubscriptionPlan as SubscriptionPlanResource;
use App\Http\Resources\SubscriptionPlans as SubscriptionPlansResource;

trait SubscriptionPlanTraits
{
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
                return new SubscriptionPlansResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\SubscriptionPlan){

                //  Transform the single instance
                return new SubscriptionPlanResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single subscription plan
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $subscription_plan = \App\SubscriptionPlan::where('id', $id)->first() ?? null;

            //  If exists
            if ($subscription_plan) {

                //  Return store
                return $subscription_plan;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
