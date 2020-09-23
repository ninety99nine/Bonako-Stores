<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use App\Http\Resources\Coupon as CouponResource;
use App\Http\Resources\Coupons as CouponsResource;

trait CouponTraits
{
    public $coupon = null;
    public $request = null;

    /** convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format.
     */
    public function convertToApiFormat($coupons = null)
    {
        if ($coupons) {

            //  Transform the multiple instances
            return new CouponsResource($coupons);

        } else {

            //  Transform the single instance
            return new CouponResource($this);

        }
    }
}
