<?php

namespace App\Http\Controllers\Api;

use App\InstantCart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InstantCartController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createInstantCart(Request $request)
    {
        try {

            //  Create and return the instant cart
            return (new InstantCart())->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateInstantCart(Request $request, $instant_cart_id)
    {
        try {

            //  Return the updated instant cart
            return (new InstantCart())->getResource($instant_cart_id)->updateResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getInstantCarts(Request $request)
    {
        try {

            //  Return a list of instant carts
            return (new InstantCart())->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getInstantCart($instant_cart_id)
    {
        try {

            //  Return the instant cart
            return (new InstantCart())->getResource($instant_cart_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getInstantCartLocation($instant_cart_id)
    {
        try {

            //  Return the instant cart location
            return (new InstantCart())->getResource($instant_cart_id)->getResourceLocation()->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generatePaymentShortCode($instant_cart_id)
    {
        try {

            //  Return the generated payment short code
            return (new InstantCart())->getResource($instant_cart_id)->generateResourcePaymentShortCode([], $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generateSubscription(Request $request, $instant_cart_id)
    {
        try {

            //  Return the generated subscription
            return (new InstantCart())->getResource($instant_cart_id)->generateResourceSubscription($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteInstantCart($instant_cart_id)
    {
        try {

            //  Delete the instant cart
            return (new InstantCart())->getResource($instant_cart_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
