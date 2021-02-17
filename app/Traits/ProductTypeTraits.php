<?php

namespace App\Traits;

use App\Http\Resources\ProductType as ProductTypeResource;
use App\Http\Resources\ProductTypes as ProductTypesResource;

trait ProductTypeTraits
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
                return new ProductTypesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\ProductType){

                //  Transform the single instance
                return new ProductTypeResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
