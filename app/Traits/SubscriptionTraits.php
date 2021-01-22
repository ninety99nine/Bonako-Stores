<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Subscription as SubscriptionResource;
use App\Http\Resources\Subscriptions as SubscriptionsResource;

trait SubscriptionTraits
{
    public $request = null;
    public $subscription = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($subscriptions = null)
    {
        if( $subscriptions ){

            //  Transform the multiple instances
            return new SubscriptionsResource($subscriptions);

        }else{

            //  Transform the single instance
            return new SubscriptionResource($this);

        }
    }

    /**
     *  This method creates a new subscription
     */
    public function createResource($request, $model = null)
    {
        try {

            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $this->handleValidation('CREATE');

            /**
             *  Retrieve the request values
             */
            $store_id = $request->input('store_id') ?? null;
            $subscription_plan_id = $request->input('subscription_plan_id') ?? null;

            //  Retrieve the subscription plan
            $subscription_plan = \App\SubscriptionPlan::find($subscription_plan_id);

            //  If we don't have a subscription plan
            if( !$subscription_plan ){

                //  The subscription plan does not exist. Throw a validation error
                throw ValidationException::withMessages(['message' => 'The subscription plan does not exist']);

            }

            $start_at = (Carbon::now())->format('Y-m-d H:i:s');

            //  If the subscription plan frequency of measured in days
            if( $subscription_plan->frequency == 'day' ){

                //  End the subscription plan after the given duration of days
                $end_at = Carbon::parse($start_at)->addDays( $subscription_plan->duration );

            //  If the subscription plan frequency of measured in months
            }elseif( $subscription_plan->frequency == 'month' ){

                //  End the subscription plan after the given duration of months
                $end_at = Carbon::parse($start_at)->addMonths( $subscription_plan->duration );

            }else{

                $end_at = null;

            }

            //  Set the template
            $template = [

                /*  Basic Info  */
                'store_id' => $store_id,
                'user_id' => auth('api')->user()->id,
                'subscription_plan_id' => $subscription_plan_id,
                'start_at' => $start_at,
                'end_at' => $end_at,
                'active' => true

            ];

            /**
             *  Create new a subscription, then retrieve a fresh instance
             */
            $this->subscription = $this->create($template)->fresh();

            //  If created successfully
            if ( $this->subscription ) {

                //  Generate a new transaction
                $this->generateTransaction($subscription_plan);

                //  If a model was provided
                if( $model ){

                    //  Generate the visit short code
                    $this->generateVisitShortCode($model);

                    //  Expire the payment short codes
                    $this->expirePaymentShortCodes($model);

                }

                //  Get a fresh instance
                $subscription = $this->subscription->fresh();

                //  Return an API Readable Format
                return $subscription->convertToApiFormat();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function expirePaymentShortCodes($model)
    {
        //  Expire payment short codes
        $model->paymentShortCodes()->update([
            'expires_at' => Carbon::now()
        ]);
    }

    public function generateVisitShortCode($model)
    {
        try {

            //  Set the short code action
            $action = 'visit';

            //  Create a new visit short code
            return ( new \App\ShortCode() )->createResource($action, $model);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function generateTransaction($subscription_plan)
    {
        try {

            //  Set the owning model
            $model = $this->subscription;

            //  Set the transaction amount on the request
            $this->request->merge(['amount' => $subscription_plan->price]);

            //  Set the transaction type on the request
            $this->request->merge(['type' => 'subscription']);

            //  Set the transaction description on the request
            $this->request->merge(['description' => 'Subscription for '.$subscription_plan->name]);

            //  Create a new transaction
            return ( new \App\Transaction() )->createResource($this->request, $model);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function handleValidation($type)
    {
        try {

            if( $type == 'CREATE' ){

                $rules = [
                    'store_id' => 'required|regex:/^[0-9]+$/i',
                    'subscription_plan_id' => 'required|regex:/^[0-9]+$/i',
                ];

                $messages = [
                    'store_id.required' => 'The store id is required to create a subscription',
                    'store_id.regex' => 'The store id must be a valid number e.g 123',
                    'subscription_plan_id.required' => 'The subscription plan id is required to create a subscription',
                    'subscription_plan_id.regex' => 'The store id must be a valid number e.g 1'
                ];

            }

            //  Validate request
            $validator = Validator::make($this->request->all(), $rules, $messages);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
