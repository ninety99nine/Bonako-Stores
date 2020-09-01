<?php

namespace App\Http\Controllers\Api;

use App\MyCart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class CartController extends Controller
{

    public function calculateCart(Request $request)
    {
        $products = $request->input('products') ?? [];
        $store_id = $request->input('store_id') ?? null;
        $coupon_codes = $request->input('coupon_codes') ?? [];
        $delivery_fee = $request->input('delivery_fee') ?? null;

        $info = [
            'items' => $products,
            'store_id' => $store_id,
            'coupon_codes' => $coupon_codes,
            'delivery_fee' => $delivery_fee
        ];

        //  Get the cart details
        $cart = (new MyCart)->getCartDetails($info);

        return $cart;
        
    }

}
