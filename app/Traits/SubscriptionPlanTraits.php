<?php

namespace App\Traits;
use App\Http\Resources\SubscriptionPlan as SubscriptionPlanResource;
use App\Http\Resources\SubscriptionPlans as SubscriptionPlansResource;

trait SubscriptionPlanTraits
{
    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($subcription_plans = null)
    {
        if( $subcription_plans ){

            //  Transform the multiple instances
            return new SubscriptionPlansResource($subcription_plans);

        }else{

            //  Transform the single instance
            return new SubscriptionPlanResource($this);

        }
    }

}
