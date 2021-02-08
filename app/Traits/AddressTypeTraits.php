<?php

namespace App\Traits;

use App\Http\Resources\AddressType as AddressTypeResource;

trait AddressTypeTraits
{
    public $address = null;
    public $request = null;

    /**
     *  This method transforms a single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            //  Transform the single instance
            return new AddressResource($this);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
