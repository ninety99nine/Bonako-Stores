<?php

namespace App\Http\Controllers\Api;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class OrderController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createOrder( Request $request )
    {
        /** We need to allow creation of orders using the Bonako Dashboard
         *  and by requests from the USSD Application, which must pass an
         *  access token that we must verify here to allow order creation.
         *  This is so that we do not allow the route to be accessible by
         *  just everyone for potential malicious use.
         */

        //  if ($this->user && $this->user->can('create', Order::class)) {
        if( true ){

            //  Create the Order
            $order = (new Order)->initiateCreate( $request );

            return $order;

            //  If the created successfully
            if( $order ){

                //  Return an API Readable Format of the Order Instance
                return $order->convertToApiFormat();

            }

        } else {

            //  Not Authourized
            return help_not_authorized();

        }
    }

}
