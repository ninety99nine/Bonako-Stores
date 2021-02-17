<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
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
     *  This method returns a list of addresses
     */
    public function getResources($data = [], $builder = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data (CommanTraits)
            $this->getResourcesValidation($data);

            //  If we already have an eloquent builder defined
            if( is_object($builder) ){

                //  Set the addresses to this eloquent builder
                $addresses = $builder;

            }else{

                //  Get the addresses
                $addresses = \App\Address::latest();

            }

            //  Filter the addresses
            $addresses = $this->filterResourcesByType($data, $addresses);

            //  Return addresses
            return $this->collectionResponse($data, $addresses, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the addresses by type
     */
    public function filterResourcesByType($data = [], $addresses)
    {
        //  Set the statuses to an empty array
        $selected_filters = [];

        //  Set the filters e.g ["work", "home", "friend", ...] or "work,home,friend, ..."
        $filters = $data['type'] ?? null;

        //  If the filters are provided as String format e.g "work,home,friend"
        if( is_string($filters) ){

            //  Set the statuses to the exploded Array ["work", "home", "friend"]
            $selected_filters = explode(',', $filters);

        }elseif( is_array($filters) ){

            //  Set the statuses to the given Array ["work", "home", "friend"]
            $selected_filters = $filters;

        }

        //  Clean-up each filter
        foreach ($selected_filters as $key => $filter) {

            //  Convert " work " to "Work"
            $selected_filters[$key] = ucfirst(strtolower(trim($filter)));

        }

        if ( $addresses && count($selected_filters) ) {

            $addresses = $addresses->whereHas('addressType', function (Builder $query) use ($selected_filters){
                $query->whereIn('name', $selected_filters);
            });

        }

        //  Return the addresses
        return $addresses;
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
