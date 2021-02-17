<?php

namespace App\Traits;

use App\Http\Resources\AddressType as AddressTypeResource;
use App\Http\Resources\AddressTypes as AddressTypesResource;

trait AddressTypeTraits
{
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
                return new AddressTypesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\AddressType){

                //  Transform the single instance
                return new AddressTypeResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
