<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Http\Resources\Advert as AdvertResource;
use App\Http\Resources\Adverts as AdvertsResource;

trait AdvertTraits
{
    public $advert = null;

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
                return new AdvertsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Advert){

                //  Transform the single instance
                return new AdvertResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new advert
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

            //  Set the "duration"
            $duration = $data['duration'] ?? 1;

            //  Set the "start_at" datetime
            $start_at = $data['start_at'] ?? (Carbon::now())->format('Y-m-d H:i:s');

            //  Calculate the "end_at" datetime based on the "start_at" datetime and "duration"
            $data['end_at'] = Carbon::parse($start_at)->addDays( $duration );

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            /**
             *  Create a new resource
             */
            $this->advert = $this->create($template);

            //  If created successfully
            if ( $this->advert ) {

                //  If we have the advert resource
                if( (isset($data['resource_id']) && !empty($data['resource_id'])) &&
                     isset($data['resource_type']) && !empty($data['resource_type']) ){

                    //  Update the advert with the resource id and type
                    $this->advert->update([
                        'owner_id' => $data['resource_id'],
                        'owner_type' => $data['resource_type'],
                    ]);

                }

                //  Set this advert arrangement
                $data = [
                    'arrangements' => [
                        [
                            'id' => $this->id,
                            'arrangement' => $this->arrangement ?? 1
                        ]
                    ]
                ];

                //  $this->reorderAdverts($data);

                //  Return the advert
                return $this->advert;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method updates an existing advert
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

                //  If we have the advert resource
                if( (isset($data['resource_id']) && !empty($data['resource_id'])) &&
                     isset($data['resource_type']) && !empty($data['resource_type']) ){

                    //  Update the advert with the resource id and type
                    $this->update([
                        'owner_id' => $data['resource_id'],
                        'owner_type' => $data['resource_type'],
                    ]);

                }

                //  Set this advert arrangement
                $data = [
                    'arrangements' => [
                        [
                            'id' => $this->id,
                            'arrangement' => $this->arrangement ?? 1
                        ]
                    ]
                ];

                //  $this->reorderAdverts($data);

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
     *  This method returns a list of adverts
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

                //  Set the adverts to this eloquent builder
                $adverts = $builder;

            }else{

                //  Get the adverts
                $adverts = \App\Advert::orderBy('arrangement');

            }

            //  Filter the adverts
            $adverts = $this->filterResources($data, $adverts);

            //  Return adverts
            return $this->collectionResponse($data, $adverts, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single advert
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $advert = \App\Advert::where('id', $id)->first() ?? null;

            //  If exists
            if ($advert) {

                //  Return advert
                return $advert;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a single advert
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
     *  This method filters the adverts by search or status
     */
    public function filterResources($data = [], $adverts)
    {
        //  If we need to search for specific adverts
        if ( isset($data['search']) && !empty($data['search']) ) {

            $adverts = $this->filterResourcesBySearch($data, $adverts);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $adverts = $this->filterResourcesByStatus($data, $adverts);

        }

        //  Return the adverts
        return $adverts;
    }

    /**
     *  This method filters the adverts by search
     */
    public function filterResourcesBySearch($data = [], $adverts)
    {
        //  Set the search term e.g "Bravo Cinema"
        $search_term = $data['search'] ?? null;

        //  Return searched adverts otherwise original adverts
        return empty($search_term) ? $adverts : $adverts->search($search_term);

    }

    /**
     *  This method filters the adverts by status
     */
    public function filterResourcesByStatus($data = [], $adverts)
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

        if ( $adverts && count($statuses) ) {

            if( in_array('Visible', $statuses) ){

                $adverts = $adverts->visible();

            }elseif( in_array('Invisible', $statuses) ){

                $adverts = $adverts->inVisible();

            }

            if( in_array('Expired', $statuses) ){

                $adverts = $adverts->expired();

            }

        }

        //  Return the adverts
        return $adverts;
    }

    /**
     *  This method updates the arrangement of adverts
     */
    public function reorderAdverts($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the arrangements
            $advert_arrangements = $data['arrangements'] ?? [];

            //  Get the adverts
            $advert_records = collect( (new \App\Advert())->all() )->toArray();

            //  Set the advert arrangement in correct assending order (Remove invalid arrangements)
            $advert_arrangements =
                collect($advert_arrangements)->filter(function ($advert_record) {

                    //  Must have an id
                    return isset($advert_record['id']) && !empty($advert_record['id']) &&

                        //  Must have an arrangement
                        isset($advert_record['arrangement']) && !empty($advert_record['arrangement']);

                //  Order by arrangement
                })->sortBy('arrangement');

            //  Foreach advert_record we must arrange
            foreach ($advert_arrangements as $key => $advert_arrangement) {

                //  Set the id (i.e Target)
                $id = $advert_arrangement['id'];

                //  Set the arrangement (i.e Position)
                $arrangement = $advert_arrangement['arrangement'];

                //  Foreach advert
                foreach($advert_records as $key => $advert_record){

                    //  If the advert id matches the advert id we must arrange
                    if( $advert_record['id'] === $id ){

                        //  Remove the advert from its current arrangement
                        unset($advert_records[$key]);

                        //  Add the advert to its new arrangement
                        array_splice($advert_records, $arrangement - 1, 0, [$advert_record]);

                    }

                }

            }

            //  Set the updated adverts
            $updated_adverts = [];

            //  Foreach of the advert
            foreach ($advert_records as $key => $advert_record) {

                //  Set the advert arrangement
                $arrangement = ($key + 1);

                //  Update the advert arrangement
                $advert_record['arrangement'] = $arrangement;

                //  Capture the updated advert
                array_push($updated_adverts, $advert_record);

            }

            //  If we have updated adverts
            if( count($updated_adverts) ){

                //  Update the advert arrangements
                \App\Advert::upsert($updated_adverts, ['id'], ['arrangement']);

            }

            //  If this instance is not an Eloquent Record
            if( !$this->id ){

                //  Return a list of adverts in their new arrangement
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
                if ($user->can('create', Advert::class) === false) {

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
