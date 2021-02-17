<?php

namespace App\Http\Controllers\Api;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createOrder(Request $request)
    {
        try {

            //  Return a new order
            return (new Order())->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateOrder(Request $request, $order_id)
    {
        try {

            //  Get the order
            $order = \App\Order::where('id', $order_id)->first() ?? null;

            //  Check if the order exists
            if ($order) {

                //  Return the updated order
                return $order->updateResource($request, $this->user)->convertToApiFormat();

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getOrders(Request $request)
    {
        try {

            //  Return a list of orders
            return (new Order())->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getOrder($order_id)
    {
        try {

            //  Return a single order
            return (new Order())->getResource($order_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getOrderReceivedLocation($order_id)
    {
        try {

            //  Return the order received location
            return (new Order())->getResource($order_id)->getResourceReceivedLocation();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getOrderSharedLocations(Request $request, $order_id)
    {
        try {

            //  Return the order shared locations
            return (new Order())->getResource($order_id)->getResourceSharedLocations($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getOrderStore($order_id)
    {
        try {

            //  Return the order received location
            return (new Order())->getResource($order_id)->getResourceStore();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateOrderSharedLocations(Request $request, $order_id)
    {
        try {

            //  Return the order shared locations
            return (new Order())->getResource($order_id)->updateResourceSharedLocations($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function fulfilOrder(Request $request, $order_id)
    {
        try {

            //  Fulfill the order
            return (new Order())->getResource($order_id)->fulfilResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

















    /*
    public function createOrder( Request $request )
    {
        try {

            //  Return a new order
            return (new Order())->createResource($request, $this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function fulfilOrder( Request $request, $order_id )
    {
        try {

            //  Get the order
            $order = \App\Order::find($order_id) ?? null;

            //  Check if the order exists
            if ($order) {

                //  Fulfil the order
                return $order->fulfilResource($request, $this->user);

            } else {

                //  Not Found
                return help_resource_not_found();

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
                return help_resource_not_found();

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
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }

    }

    */

}
