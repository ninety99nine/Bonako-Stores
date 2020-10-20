<?php

namespace App\Http\Controllers\Api;

use App\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function getPaymentMethods(Request $request)
    {
        try {
            
            //  Get the payment methods
            $Payment_method = \App\PaymentMethod::paginate(100) ?? null;

            //  Check if the stores exist
            if ($Payment_method) {
                    
                //  Return an API Readable Format of the PaymentMethod Instance
                return ( new \App\PaymentMethod() )->convertToApiFormat($Payment_method);
                
            } else {

                //  Not Found
                return help_resource_not_fonud();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

}
