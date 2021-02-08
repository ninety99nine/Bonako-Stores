<?php

namespace App\Traits;
use App\Http\Resources\Status as StatusResource;
use App\Http\Resources\Statuses as StatusesResource;

trait StatusTraits
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
                return new StatusesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Status){

                //  Transform the single instance
                return new StatusResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
