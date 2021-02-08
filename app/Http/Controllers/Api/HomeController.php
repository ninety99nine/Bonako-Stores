<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Home as HomeResource;

class HomeController extends Controller
{
    /** home()
     *
     *  1) Returns the API links for the non-authenticated
     *     user that is not logged in
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

    public function getCurrencies()
    {
        try {

            //  Get the currencies
            $currencies = \App\Currency::paginate();

            //  Return an API Readable Format of the Currency Instance
            return ( new \App\Currency() )->convertToApiFormat($currencies);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getPaymentMethods()
    {
        try {

            //  Get the payment methods
            $Payment_method = \App\PaymentMethod::paginate();

            //  Return an API Readable Format of the PaymentMethod Instance
            return ( new \App\PaymentMethod() )->convertToApiFormat($Payment_method);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getSubscriptionPlans()
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
