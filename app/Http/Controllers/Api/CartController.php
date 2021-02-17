<?php

namespace App\Http\Controllers\Api;

use App\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class CartController extends Controller
{

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
