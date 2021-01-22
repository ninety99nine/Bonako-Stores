<?php

namespace App\Traits;

//  Resources
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Users as UsersResource;
use Illuminate\Validation\ValidationException;

trait UserTraits
{
    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($users = null)
    {
        if( $users ){

            //  Transform the multiple instances
            return new UsersResource($users);

        }else{

            //  Transform the single instance
            return new UserResource($this);

        }
    }

    /**
     *   This method returns a list of stores
     */
    public function getStores($request)
    {
        //  Set the request variable
        $this->request = $request;

        //  Set the type e.g "created", "shared" or "favourite"
        $type = $request->input('type');

        //  Set the pagination limit e.g 15
        $limit = $request->input('limit');

        //  Set the search term e.g "Heavenly Fruits"
        $search_term = $request->input('search');

        //  Validate the request
        $this->handleValidation('GET');

        if ($type === 'created') {

            //  Scope stores created by the user
            $stores = $this->stores()->asOwner($this->id);

        } elseif ($type === 'shared') {

            //  Scope stores shared with the user
            $stores = $this->stores()->asNonOwner($this->id);

        } elseif ($type === 'favourite') {

            //  Scope stores favourated by the user
            $stores = (new \App\Store)->asFavourite($this->id);

        }else{

            //  Scope stores
            $stores = $this->stores();

        }

        //  If we need to search for specific stores
        if (!empty($search_term)) {

            $stores = $stores->search($search_term);

        }

        //  Paginate the stores
        $stores = $stores->paginate($limit) ?? [];

        //  Return an API Readable Format
        return ( new \App\Store() )->convertToApiFormat( $stores );

    }

    /**
     *   This method returns a single store
     */
    public function getStore($store_id)
    {
        try {

            //  Get the store
            $store = $this->stores()->where('stores.id', $store_id)->first() ?? null;

            //  If exists
            if ($store) {

                //  Return an API Readable Format
                return $store->convertToApiFormat();

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *   This method returns a list of store locations
     */
    public function getStoreLocations($request, $store_id)
    {
        try {

            //  Get the store
            $store = $this->stores()->where('stores.id', $store_id)->first() ?? null;

            //  If exists
            if ($store) {

                //  Set the request variable
                $this->request = $request;

                //  Set the pagination limit e.g 15
                $limit = $request->input('limit');

                //  Set the search term e.g "Main Branch"
                $search_term = $request->input('search');

                //  Validate the request
                $this->handleValidation('GET');

                //  Get the locations
                $locations = $store->locations();

                //  If we need to search for specific locations
                if (!empty($search_term)) {

                    $locations = $locations->search($search_term);

                }

                //  Paginate the locations
                $locations = $locations->paginate($limit) ?? [];

                //  Return an API Readable Format
                return ( new \App\Location() )->convertToApiFormat( $locations );

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *   This method returns a single store location
     */
    public function getStoreLocation($store_id, $location_id, $convetFormat = true)
    {
        try {

            //  Get the location
            $location = $this->locations()->where('location.id', $location_id)
                                ->whereHas('store', function (Builder $query) use ($store_id){
                                        $query->where('stores.id', $store_id);
                                })->first();

            //  If exists
            if ($location) {

                //  Return an API Readable Format
                return $location->convertToApiFormat();

            }

            //  Return "Not Found" Error
            return help_resource_not_found();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *   This method returns user's store default location
     */
    public function getStoreDefaultLocation($store_id)
    {
        try {

            //  Get the default location
            $location = $this->locations()->where('location_user.default_location', '1')
                                ->whereHas('store', function (Builder $query) use ($store_id){
                                        $query->where('stores.id', $store_id);
                                })->first();

            //  If exists
            if ($location) {

                //  Return an API Readable Format
                return $location->convertToApiFormat();

            }

            //  Return "Not Found" Error
            return help_resource_not_found();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *   This method updates user's store default location
     */
    public function updateStoreDefaultLocation($request, $store_id)
    {
        try {

            //  Set the request variable
            $this->request = $request;

            //  Set the location id
            $location_id = $request->input('location_id');

            //  Validate the request
            $this->handleValidation('UPDATE DEFAULT LOCATION');

            //  Get the location
            $location = $this->locations()->whereHas('store', function (Builder $query) use ($store_id){
                            $query->where('stores.id', $store_id);
                        })->where('locations.id', $location_id)
                        ->first();

            //  If exists
            if ($location) {

                //  Get all store location ids
                $location_ids = $this->locations()->whereHas('store', function (Builder $query) use ($store_id){
                                    $query->where('stores.id', $store_id);
                                })->pluck('locations.id');

                //  Set all locations as non-default
                DB::table('location_user')->whereIn('location_id', $location_ids)->where('user_id', $this->id)->update([
                    'default_location' => '0'
                ]);

                //  Set the selected location as default
                DB::table('location_user')->where('location_id', $location_id)->where('user_id', $this->id)->update([
                    'default_location' => '1'
                ]);

                //  Return success message
                return response()->json(['message' => $location->name . ' successfully set as default location'], 200);

            }

            //  Return "Not Found" Error
            return help_resource_not_found();


        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function handleValidation($type)
    {
        try {

            //  If reading resources
            if( $type == 'GET' ){

                $rules = [
                    'limit' => 'sometimes|required|numeric|min:1|max:100',
                ];

                $messages = [
                    'limit.required' => 'Enter a valid limit containing only digits e.g 50',
                    'limit.regex' => 'Enter a valid limit containing only digits e.g 50',
                    'limit.min' => 'The limit attribute must be a value between 1 and 100',
                    'limit.max' => 'The limit attribute must be a value between 1 and 100',
                ];

            //  If creating or updating a resource
            }elseif( $type == 'UPDATE DEFAULT LOCATION' ){

                $rules = [
                    'location_id' => 'required|numeric',
                ];

                $messages = [
                    'location_id.required' => 'The location_id attribute is required to set a default location',
                    'location_id.numeric' => 'The location_id must be a valid number e.g 123',
                ];

            }

            //  Validate request
            $validator = Validator::make($this->request->all(), $rules, $messages);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  Checks if a given user is the owner of the user account
     */
   public function isAccountOwner($user_id)
   {
        return $this->id == $user_id;
   }

   /**
    *  Checks if a given user is a Super Admin.
    */
   public function isSuperAdmin()
   {
       return $this->account_type == 'superadmin';
   }

}
