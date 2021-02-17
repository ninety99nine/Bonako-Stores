<?php

namespace App\Traits;

//  Resources
use DB;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Users as UsersResource;

trait UserTraits
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
                return new UsersResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\User){

                //  Transform the single instance
                return new UserResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of users
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

                //  Set the users to this eloquent builder
                $users = $builder;

            }else{

                //  Get the users
                $users = \App\User::latest();

            }

            //  Filter the users
            $users = $this->filterResources($data, $users);

            //  Return users
            return $this->collectionResponse($data, $users, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of user totals
     *
     *  Note: $builder is an instance of the eloquent builder. In this
     *  case the eloquent builder must represent an instance of users
     */
    public function getResourceTotals($data = [], $builder)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the totals
            $totals = [
                'roles' => [],
                'total' => $builder->count()
            ];

            //  Set the status filters to calculate the totals
            $filters = ['admin'];

            collect($filters)->map(function($filter) use (&$totals, $builder){

                /**
                 *  $filter = 'admin' ... e.t.c
                 *
                 *  $bulder = Eloquent Builder Instance e.g $location->users()->latest()
                 *
                 *  We clone the builder object to have a new instance to use when filtering the users.
                 *  If we do not clone, only one object instance will be used for every filter producing
                 *  incorrect results e.g The instance may be used to filter only users with a role of
                 *  "admin" and return a few results. The same builder will then be used to filter users
                 *  with a role of "staff", however since we are using the same instance it would have
                 *  applied the previous filter of "admin", which means that the final users returned
                 *  will need to have an "admin" and "staff" role.
                 */
                $totals['roles'][$filter] = $this->filterResourcesByLocationRole($filter, clone $builder)->count();

            })->toArray();

            /**
             *  Return the totals
             *
             *  Example result
             *
             *  [
             *      "roles" => [
             *          "admin" => "10"
             *      ]
             *  ]
             */
            return $totals;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the users by search or account type
     */
    public function filterResources($data = [], $users)
    {
        //  If we need to search for specific users
        if ( isset($data['search']) && !empty($data['search']) ) {

            $users = $this->filterResourcesBySearch($data, $users);

        }elseif ( isset($data['account_type']) && !empty($data['account_type']) ) {

            $users = $this->filterResourcesByAccountType($data, $users);

        }

        //  Return the users
        return $users;
    }

    /**
     *  This method filters the users by search
     */
    public function filterResourcesBySearch($data = [], $users)
    {
        //  Set the search term e.g "John"
        $search_term = $data['search'] ?? null;

        //  Return searched users otherwise original users
        return empty($search_term) ? $users : $users->search($search_term);

    }

    /**
     *  This method filters the users by account type
     */
    public function filterResourcesByAccountType($data = [], $users)
    {
        //  Set the statuses to an empty array
        $selected_filters = [];

        //  Set the filters e.g ["basic", "superadmin", ...] or "basic,superadmin, ..."
        $filters = $data['account_type'] ?? $data;

        //  If the filters are provided as String format e.g "basic,superadmin"
        if( is_string($filters) ){

            //  Set the statuses to the exploded Array ["basic", "superadmin"]
            $selected_filters = explode(',', $filters);

        }elseif( is_array($filters) ){

            //  Set the statuses to the given Array ["basic", "superadmin"]
            $selected_filters = $filters;

        }

        //  Clean-up each filter
        foreach ($selected_filters as $key => $filter) {

            //  Convert " SuperAdmin " to "superadmin"
            $selected_filters[$key] = strtolower(trim($filter));

        }

        if ( $users && count($selected_filters) ) {

            $users = $users->whereIn('account_type', $selected_filters);

        }

        //  Return the users
        return $users;
    }

    /**
     *  This method filters the users by location role
     */
    public function filterResourcesByLocationRole($data = [], $users)
    {
        //  Set the selected filters to an empty array
        $selected_filters = [];

        //  Set the filters e.g ["admin", ...] or "admin, ..."
        $filters = $data['role'] ?? null;

        //  If the filters are provided as String format e.g "admin, ..."
        if( is_string($filters) ){

            //  Set the statuses to the exploded Array ["admin", ...]
            $selected_filters = explode(',', $filters);

        }elseif( is_array($filters) ){

            //  Set the statuses to the given Array ["admin", ...]
            $selected_filters = $filters;

        }

        //  Clean-up each filter
        foreach ($selected_filters as $key => $filter) {

            //  Convert " Admin " to "admin"
            $selected_filters[$key] = strtolower(trim($filter));

        }

        if ( $users && count($selected_filters) ) {

            $users = $users->whereIn('location_user.type', $selected_filters);

        }

        //  Return the users
        return $users;
    }

    /**
     *  This method returns a list of user stores
     */
    public function getResourceStores($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Set the user
            $user = $this;

            //  Set the user stores
            $stores = $this->stores();

            //  Return a list of user stores
            return (new \App\Store())->getResources($data, $stores, $user, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single user store
     */
    public function getResourceStore($store_id)
    {
        try {

            //  Get the store
            $store = $this->stores()->where('stores.id', $store_id)->first() ?? null;

            //  If exists
            if ($store) {

                //  Return store
                return $store;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of user store locations
     */
    public function getResourceStoreLocations($data = [], $store_id, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the store
            $store = $this->stores()->where('stores.id', $store_id)->first() ?? null;

            //  If exists
            if ($store) {

                //  Return a list of user store locations
                return (new \App\Location())->getResources($data, $store->locations()->latest(), $paginate, $convert_to_api_format);

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single user store location
     */
    public function getResourceStoreLocation($store_id, $location_id)
    {
        try {

            //  Get the location
            $location = $this->locations()->where('locations.id', $location_id)
                                ->whereHas('store', function (Builder $query) use ($store_id){
                                        $query->where('stores.id', $store_id);
                                })->first();

            //  If exists
            if ($location) {

                //  Return location
                return $location;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns user's default location
     */
    public function getResourceDefaultLocation($store_id)
    {
        try {

            //  Get the default location
            $location = $this->locations()->where('location_user.default_location', '1')
                                ->whereHas('store', function (Builder $query) use ($store_id){
                                        $query->where('stores.id', $store_id);
                                })->first();

            //  If exists
            if ($location) {

                //  Return location
                return $location;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method updates user's default location
     */
    public function updateResourceDefaultLocation($data = [], $store_id)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the location id
            $location_id = $data['location_id'];

            //  Validate the data
            $this->updateResourceDefaultLocationValidation($data);

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

    /**
     *  This method creates a new user address
     */
    public function createResourceAddress($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the address owning model
            $model = $this;

            /**
             *  Create new user address resource
             */
            return ( new \App\Address() )->createResource($data, $model);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of user addresses
     */
    public function getResourceAddresses($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the user addresses
            $addresses = $this->addresses();

            //  Return a list of user addresses
            return (new \App\Address())->getResources($data, $addresses, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates updating an existing resource default locatino
     */
    public function updateResourceDefaultLocationValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [
                'location_id' => 'required|numeric',
            ];

            //  Set validation messages
            $messages = [
                'location_id.required' => 'The location_id attribute is required to set a default location',
                'location_id.numeric' => 'The location_id must be a valid number e.g 123',

            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

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
