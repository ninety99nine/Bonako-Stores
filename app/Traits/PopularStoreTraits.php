<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Http\Resources\PopularStore as PopularStoreResource;
use App\Http\Resources\PopularStores as PopularStoresResource;

trait PopularStoreTraits
{
    public $popular_store = null;

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
                return new PopularStoresResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\PopularStore){

                //  Transform the single instance
                return new PopularStoreResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new popular store
     */
    public function createResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Verify permissions
            $this->createResourcePermission($user);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Get the popular popular store (If already listed)
            $popular_store = \App\PopularStore::where('store_id', $data['store_id'])->first() ?? null;

            //  Check if the popular popular store exists
            if ($popular_store) {

                //  Return the updated popular popular store
                return $popular_store->updateResource($data, $user);

            } else {

                //  Set the "duration"
                $duration = $data['duration'] ?? 1;

                //  Set the "start_at" datetime
                $start_at = $data['start_at'] ?? (Carbon::now())->format('Y-m-d H:i:s');

                //  Calculate the "end_at" datetime based on the "start_at" datetime and "duration"
                $data['end_at'] = Carbon::parse($start_at)->addDays( $duration );

                //  Set the template with the resource fields allowed
                $template = collect($data)->only($this->getFillable())->toArray();

            }

            /**
             *  Create a new resource
             */
            $this->popular_store = $this->create($template);

            //  If created successfully
            if ( $this->popular_store ) {

                //  Set this popular store arrangement
                $data = [
                    'arrangements' => [
                        [
                            'id' => $this->id,
                            'arrangement' => $this->arrangement ?? 1
                        ]
                    ]
                ];

                $this->reorderPopularStores($data);

                //  Return the popular store
                return $this->popular_store;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method updates an existing popular store
     */
    public function updateResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Merge the existing data with the new data
            $data = array_merge(collect($this)->only($this->getFillable())->toArray(), $data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Validate the data
            $this->updateResourceValidation($data);

            if( $data['reset_dates'] == true ){

                //  Set the "duration"
                $duration = $data['duration'] ?? 1;

                //  Set the "start_at" datetime
                $start_at = $data['start_at'] ?? (Carbon::now())->format('Y-m-d H:i:s');

                //  Calculate the "end_at" datetime based on the "start_at" datetime and "duration"
                $data['end_at'] = Carbon::parse($start_at)->addDays( $duration );

            }else{

                //  Do not update the "start_at" attribute
                unset($data['start_at']);

                //  Do not update the "end_at" attribute
                unset($data['end_at']);

            }

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            /**
             *  Update the resource details
             */
            $updated = $this->update($template);

            //  If updated successfully
            if ($updated) {

                //  Set this popular store arrangement
                $data = [
                    'arrangements' => [
                        [
                            'id' => $this->id,
                            'arrangement' => $this->arrangement ?? 1
                        ]
                    ]
                ];

                $this->reorderPopularStores($data);

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
     *  This method returns a list of popular stores
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

                //  Set the popular stores to this eloquent builder
                $popular_stores = $builder;

            }else{

                //  Get the popular stores
                $popular_stores = \App\PopularStore::orderBy('arrangement');

            }

            //  Filter the popular stores
            $popular_stores = $this->filterResources($data, $popular_stores);

            //  Return popular stores
            return $this->collectionResponse($data, $popular_stores, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single popular store
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $popular_store = \App\PopularStore::where('id', $id)->first() ?? null;

            //  If exists
            if ($popular_store) {

                //  Return popular store
                return $popular_store;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a single popular store
     */
    public function deleteResource($user = null)
    {
        try {

            //  Verify permissions
            $this->forceDeleteResourcePermission($user);

            /**
             *  Delete the resource
             */
            return $this->delete();


        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the popular stores by search or status
     */
    public function filterResources($data = [], $popular_stores)
    {
        //  If we need to search for specific popular stores
        if ( isset($data['search']) && !empty($data['search']) ) {

            $popular_stores = $this->filterResourcesBySearch($data, $popular_stores);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $popular_stores = $this->filterResourcesByStatus($data, $popular_stores);

        }

        //  Return the popular stores
        return $popular_stores;
    }

    /**
     *  This method filters the popular stores by search
     */
    public function filterResourcesBySearch($data = [], $popular_stores)
    {
        //  Set the search term e.g "Bravo Cinema"
        $search_term = $data['search'] ?? null;

        //  Return searched popular stores otherwise original popular stores
        return empty($search_term) ? $popular_stores : $popular_stores->search($search_term);

    }

    /**
     *  This method filters the popular stores by status
     */
    public function filterResourcesByStatus($data = [], $popular_stores)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["visible", "invisible", "expired", ...] or "visible,invisible,expired, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "visible,invisible,expired"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["visible", "invisible", "expired"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["visible", "invisible", "expired"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " visible " to "Visible"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $popular_stores && count($statuses) ) {

            if( in_array('Visible', $statuses) ){

                $popular_stores = $popular_stores->visible();

            }elseif( in_array('Invisible', $statuses) ){

                $popular_stores = $popular_stores->inVisible();

            }

            if( in_array('Expired', $statuses) ){

                $popular_stores = $popular_stores->expired();

            }

        }

        //  Return the popular stores
        return $popular_stores;
    }

    /**
     *  This method updates the arrangement of popular stores
     */
    public function reorderPopularStores($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the arrangements
            $popular_store_arrangements = $data['arrangements'] ?? [];

            //  Get the popular stores
            $popular_store_records = collect( (new \App\PopularStore())->all() )->toArray();

            //  Set the popular store arrangement in correct assending order (Remove invalid arrangements)
            $popular_store_arrangements =
                collect($popular_store_arrangements)->filter(function ($popular_store_record) {

                    //  Must have an id
                    return isset($popular_store_record['id']) && !empty($popular_store_record['id']) &&

                        //  Must have an arrangement
                        isset($popular_store_record['arrangement']) && !empty($popular_store_record['arrangement']);

                //  Order by arrangement
                })->sortBy('arrangement');

            //  Foreach popular_store_record we must arrange
            foreach ($popular_store_arrangements as $key => $popular_store_arrangement) {

                //  Set the id (i.e Target)
                $id = $popular_store_arrangement['id'];

                //  Set the arrangement (i.e Position)
                $arrangement = $popular_store_arrangement['arrangement'];

                //  Foreach popular store
                foreach($popular_store_records as $key => $popular_store_record){

                    //  If the popular store id matches the popular store id we must arrange
                    if( $popular_store_record['id'] === $id ){

                        //  Remove the popular store from its current arrangement
                        unset($popular_store_records[$key]);

                        //  Add the popular store to its new arrangement
                        array_splice($popular_store_records, $arrangement - 1, 0, [$popular_store_record]);

                    }

                }

            }

            //  Set the updated popular stores
            $updated_popular_stores = [];

            //  Foreach of the popular store
            foreach ($popular_store_records as $key => $popular_store_record) {

                //  Set the popular store arrangement
                $arrangement = ($key + 1);

                //  Update the popular store arrangement
                $popular_store_record['arrangement'] = $arrangement;

                //  Capture the updated popular store
                array_push($updated_popular_stores, $popular_store_record);

            }

            //  If we have updated popular stores
            if( count($updated_popular_stores) ){

                //  Update the popular store arrangements
                \App\PopularStore::upsert($updated_popular_stores, ['id'], ['arrangement']);

            }

            //  If this instance is not an Eloquent Record
            if( !$this->id ){

                //  Return a list of popular stores in their new arrangement
                return $this->getResources($data);

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', PopularStore::class) === false) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for updating an existing resource
     */
    public function updateResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to update the resource
                if ($user->can('update', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for deleting an existing resource
     */
    public function forceDeleteResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to delete the resource
                if ($user->can('forceDelete', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

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
