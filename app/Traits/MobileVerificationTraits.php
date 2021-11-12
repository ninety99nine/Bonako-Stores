<?php

namespace App\Traits;
use App\Http\Resources\MobileVerification as MobileVerificationResource;
use App\Http\Resources\MobileVerifications as MobileVerificationsResource;

trait MobileVerificationTraits
{
    public $item_line = null;

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
                return new MobileVerificationsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\MobileVerification){

                //  Transform the single instance
                return new MobileVerificationResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function getMobileVerificationType($type){

        $types = ['account_registration', 'password_reset', 'order_delivery_confirmation'];

        if(collect($types)->contains($type)){
            return $type;
        }else{
            return 'unkwown';
        }

    }
}
