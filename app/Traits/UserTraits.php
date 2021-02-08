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
     *  This method returns a list of user stores
     */
    public function getResourceStores($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Filter the user stores
            $stores = $this->filterResourceStoresByType($data);

            //  Return a list of user stores
            return (new \App\Store())->getResources($data, $stores, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the user stores by type
     */
    public function filterResourceStoresByType($data = [])
    {
        //  Extract the Request Object data (CommanTraits)
        $data = $this->extractRequestData($data);

        //  Set the type e.g "created", "shared" or "favourite"
        $type = $data['type'] ?? null;

        //  If we want created stores
        if ($type === 'created') {

            //  Scope stores created by the user
            $stores = $this->stores()->asOwner($this->id);

        //  If we want shared stores
        } elseif ($type === 'shared') {

            //  Scope stores shared with the user
            $stores = $this->stores()->asNonOwner($this->id);

        //  If we want favourite stores
        } elseif ($type === 'favourite') {

            //  Scope stores favourated by the user
            $stores = (new \App\Store)->asFavourite($this->id);

        }else{

            //  Scope stores
            $stores = $this->stores();

        }

        //  Return the orders
        return $stores;
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
            $location = $this->locations()->where('location.id', $location_id)
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
     *  This method returns a list of store location orders
     */
    public function getResourceStoreLocationOrders($data = [], $store_id, $location_id)
    {
        try {

            //  Get the location
            $location = $this->getResourceStoreLocation($store_id, $location_id);

            //  If exists
            if ($location) {

                //  Return a list of store location orders
                return $location->getResourceOrders($data, $this);

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns store location order totals
     */
    public function getResourceStoreLocationOrderTotals($data = [], $store_id, $location_id)
    {
        try {

            //  Get the location
            $location = $this->getResourceStoreLocation($store_id, $location_id);

            //  If exists
            if ($location) {

                //  Return store location order totals
                return $location->getResourceOrderTotals($data, $this);

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method fulfils the store location order
     */
    public function userStoreLocationOrderFulfillment($data = [], $store_id, $location_id, $order_id)
    {
        try {

            //  Get the order
            $order = $this->orders()->where('orders.id', $order_id)
                        ->whereHas('location', function (Builder $query) use ($store_id, $location_id){
                            $query->whereHas('location', function (Builder $query) use ($store_id){
                                $query->where('stores.id', $store_id);
                            })->where('locations.id', $location_id);
                        })->first();

            //  If exists
            if ($order) {

                //  Extract the Request Object data (CommanTraits)
                $data = $this->extractRequestData($data);

                //  Fulfil order
                $order->initiateFulfillment($data);

                //  Return a fresh instance
                return $order->fresh();

            }

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
