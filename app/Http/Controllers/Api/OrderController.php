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
        try {

            if ($this->user && $this->user->can('create', Order::class)) {

                //  Create the Order
                $order = (new Order)->initiateCreate( $request );

                //  If the created successfully
                if( $order ){

                    //  Return an API Readable Format of the Order Instance
                    return $order->convertToApiFormat();

                }

            } else {

                //  Not Authourized
                return help_not_authorized();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function fulfilOrder( Request $request, $order_id )
    {
        try {

            //  Get the order
            $order = order::where('id', $order_id)->first() ?? null;

            //  Check if the order exists
            if ($order) {

                //  Check if the user is authourized to fulfill the order
                if ($this->user && $this->user->can('fulfill', $order)) {

                    //  Fulfill the order
                    if( $order->initiateFulfillment($request) ){

                        //  Return success
                        return response()->json(null, 200);

                    }

                } else {

                    //  Not Authourized
                    return help_not_authorized();

                }

            } else {

                //  Not Found
                return help_resource_not_fonud();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
        
    }

    public function cancelOrder( Request $request, $order_id )
    {
        try {

            //  Get the order
            $order = order::where('id', $order_id)->first() ?? null;

            //  Check if the order exists
            if ($order) {

                //  Check if the user is authourized to cancel the order
                if ($this->user && $this->user->can('cancel', $order)) {

                    //  Cancel the order
                    if( $order->initiateCancellation($request) ){

                        //  Return success
                        return response()->json(null, 200);

                    }

                } else {

                    //  Not Authourized
                    return help_not_authorized();

                }

            } else {

                //  Not Found
                return help_resource_not_fonud();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
        
    }

    public function deleteOrder( Request $request, $order_id )
    {
        try{

            //  Get the order
            $order = \App\Order::where('id', $order_id)->first() ?? null;

            //  Check if the order exists
            if ($order) {

                //  Check if the user is authourized to permanently delete the order
                if ($this->user && $this->user->can('forceDelete', $order)) {

                    //  Delete the order
                    $order->delete();

                    //  Return nothing
                    return response()->json(null, 200);

                } else {

                    //  Not Authourized
                    return help_not_authorized();

                }
                
            } else {

                //  Not Found
                return help_resource_not_fonud();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }

    }

}
