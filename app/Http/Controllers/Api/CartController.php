<?php

namespace App\Http\Controllers\Api;

use App\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createCart(Request $request)
    {
        try {

            //  Create and return the cart
            return (new Cart())->createResource($request, $this->user, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateCart(Request $request, $cart_id)
    {
        try {

            //  Return the updated cart
            return (new Cart())->getResource($cart_id)->updateResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function refreshCart($cart_id)
    {
        try {

            //  Return the refreshed cart
            return (new Cart())->getResource($cart_id)->refreshResource($this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function resetCart($cart_id)
    {
        try {

            //  Return the reset cart
            return (new Cart())->getResource($cart_id)->resetResource($this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getCart($cart_id)
    {
        try {

            //  Return the cart
            return (new Cart())->getResource($cart_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteCart($cart_id)
    {
        try {

            //  Delete the cart
            return (new Cart())->getResource($cart_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function calculateCart(Request $request)
    {
        try {

            //  Return the cart details
            $cart = (new Cart)->buildCartBasket($request);

            return $cart;

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }

    }

}
