<?php

namespace App\Http\Controllers\Api;

use App\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createCoupon(Request $request)
    {
        try {

            //  Create and return the coupon
            return (new Coupon())->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateCoupon(Request $request, $coupon_id)
    {
        try {

            //  Return the updated coupon
            return (new Coupon())->getResource($coupon_id)->updateResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getCoupon($coupon_id)
    {
        try {

            //  Return the coupon
            return (new Coupon())->getResource($coupon_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getCouponLocation($coupon_id)
    {
        try {

            //  Return the coupon location
            return (new Coupon())->getResource($coupon_id)->getResourceLocation()->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteCoupon($coupon_id)
    {
        try {

            //  Delete the coupon
            return (new Coupon())->getResource($coupon_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
