<?php

namespace App\Http\Controllers\Api;

use DB;
use App\InstantCart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

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
        //  Check if the user is authourized to create instant_carts
        if ($this->user && $this->user->can('create', InstantCart::class)) {

            //  Create the instant cart
            $instant_cart = (new InstantCart())->initiateCreate($request);

            //  If the created successfully
            if ($instant_cart) {

                //  Return an API Readable Format of the InstantCart Instance
                return $instant_cart->convertToApiFormat();

            }

        } else {

            //  Not Authourized
            return help_not_authorized();
            
        }
    }

    public function getInstantCart($instant_cart_id)
    {
        //  If the first character of the instant cart id starts with "0"
        if( substr($instant_cart_id, 0, 1) == '0' ){

            //  Get the instant cart with a matching code e.g code = "043"
            $instant_cart = InstantCart::where('code', $instant_cart_id)
                                        ->with(['products', 'coupons', 'store', 'location'])->first() ?? null;

        }else{

            //  Get the instant cart with a matching id e.g id = "43"
            $instant_cart = InstantCart::where('id', $instant_cart_id)
                                        ->with(['products', 'coupons'])->first() ?? null;

        }

        //  Check if the instant cart exists
        if ($instant_cart) {

            //  Return an API Readable Format of the InstantCart Instance
            return $instant_cart->convertToApiFormat();

        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function updateInstantCart(Request $request, $instant_cart_id)
    {
        //  Get the instant cart
        $instant_cart = \App\InstantCart::where('id', $instant_cart_id)->first() ?? null;

        //  Check if the instant cart exists
        if ($instant_cart) {

            //  Check if the user is authourized to update the instant cart
            if ($this->user && $this->user->can('update', $instant_cart)) {

                //  Update the instant cart
                $instant_cart = $instant_cart->initiateUpdate($request);

                //  If the updated successfully
                if ($instant_cart) {
    
                    //  Return an API Readable Format of the InstantCart Instance
                    return $instant_cart->convertToApiFormat();
    
                }

            } else {

                //  Not Authourized
                return help_not_authorized();

            }
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }
}
