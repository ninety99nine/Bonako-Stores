<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use App\Http\Resources\Currency as CurrencyResource;
use App\Http\Resources\Currencies as CurrenciesResource;

trait CurrencyTraits
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
                return new CurrenciesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Currency){

                //  Transform the single instance
                return new CurrencyResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
