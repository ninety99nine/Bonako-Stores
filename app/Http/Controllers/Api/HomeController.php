<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home as HomeResource;
use Illuminate\Http\Request;
use App\Http\Resources\SubscriptionPlan as SubscriptionPlanResource;
use App\Http\Resources\SubscriptionPlans as SubscriptionPlansResource;

class HomeController extends Controller
{
    /** home()
     *
     *  1) Returns the API links for the non-authenticated
     *     user that is logged in
     *
     *  2) Returns the API links for the authenticated
     *     user that is logged in
     */
    public function home(Request $request)
    {
        try {

            //  Return the home Api Resource
            return new HomeResource($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getPaymentMethods(Request $request)
    {
        try {

            //  Get the payment methods
            $Payment_method = \App\PaymentMethod::paginate() ?? null;

            //  Check if the stores exist
            if ($Payment_method) {

                //  Return an API Readable Format of the PaymentMethod Instance
                return ( new \App\PaymentMethod() )->convertToApiFormat($Payment_method);

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getSubscriptionPlans(Request $request)
    {
        try {

            //  Get the subscription plans
            $subcription_plans = \App\SubscriptionPlan::paginate();

            //  Return an API Readable Format of the SubscriptionPlan Instance
            return ( new \App\SubscriptionPlan() )->convertToApiFormat($subcription_plans);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
