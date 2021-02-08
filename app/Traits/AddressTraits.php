<?php

namespace App\Traits;

use App\Http\Resources\Address as AddressResource;
use App\Http\Resources\Addresses as AddressesResource;

trait AddressTraits
{
    public $address = null;
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
                return new AddressesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Address){

                //  Transform the single instance
                return new AddressResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new address
     */
    public function createResource($data = [], $model = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            /**
             *  Create a new resource
             */
            $this->address = $this->create($template);

            //  If created successfully
            if ( $this->address ) {

                //  If we have an owning model
                if( $model ){

                    //  Update the address owner id and owner type
                    $this->address->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                    ]);

                }

                //  Return address
                return $this->address;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method updates an existing address
     */
    public function updateResource($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Merge the existing data with the new data
            $data = array_merge(collect($this)->only($this->getFillable())->toArray(), $data);

            //  Validate the data
            $this->updateResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->except(['owner_id', 'owner_type'])->toArray();

            /**
             *  Update the resource details
             */
            $updated = $this->update($template);

            //  If updated successfully
            if ($updated) {

                //  Return a fresh instance
                return $this->fresh();

            }else{

                //  Return original instance
                return $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates creating a new resource
     */
    public function createResourceValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [

            ];

            //  Set validation messages
            $messages = [

            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates updating an existing resource
     */
    public function updateResourceValidation($data = [])
    {
        try {

            //  Run the resource creation validation
            $this->createResourceValidation($data);

        } catch (\Exception $e) {

            throw($e);

        }

    }
}
