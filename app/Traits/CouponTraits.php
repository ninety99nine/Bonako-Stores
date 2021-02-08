<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use App\Http\Resources\Coupon as CouponResource;
use App\Http\Resources\Coupons as CouponsResource;

trait CouponTraits
{
    public $coupon = null;
    public $request = null;

    /**
     *  This method transforms a collection or single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            // If this instance is a collection or a paginated collection
            if( $collection instanceof \Illuminate\Support\Collection ||
                $collection instanceof \Illuminate\Pagination\LengthAwarePaginator ){

                //  Transform the multiple instances
                return new CouponsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Coupon){

                //  Transform the single instance
                return new CouponResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
