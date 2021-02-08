<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use App\Http\Resources\PaymentMethod as PaymentMethodResource;
use App\Http\Resources\PaymentMethods as PaymentMethodsResource;

trait PaymentMethodTraits
{
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
                return new PaymentMethodsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\PaymentMethod){

                //  Transform the single instance
                return new PaymentMethodResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    
}
