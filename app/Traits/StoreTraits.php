<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use App\Http\Resources\Store as StoreResource;
use App\Http\Resources\Stores as StoresResource;

trait StoreTraits
{
    public $store = null;

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
                return new StoresResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Store){

                //  Transform the single instance
                return new StoreResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new store
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

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  If the current authenticated user is a Super Admin and the "user_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['user_id']) ){

                //  Set the "user_id" provided as the user responsible for owning this resource
                $template['user_id'] = $data['user_id'];

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['user_id'] = auth('api')->user()->id;

            }

            //  If we have the "hex_color"
            if( isset($template['hex_color']) ){

                //  Remove the hex color hash symbol "#"
                $template['hex_color'] = str_replace('#', '', $template['hex_color']);

            }

            /**
             *  Create a new resource
             */
            $this->store = $this->create($template);

            //  If created successfully
            if ( $this->store ) {

                //  Generate the resource creation report
                $this->store->generateResourceCreationReport();

                //  Generate a payment shortcode (So that we can subscribe to the store via USSD) (CommonTraits)
                $this->store->generateResourcePaymentShortCode($user);

                //  If we have the store id representing the store with resources to clone
                if ( isset($data['clone_store_id']) ) {

                    //  Set the store id for cloning
                    $store_id = $data['clone_store_id'];

                    //  Clone the store resources
                    $this->cloneExternalResources($data, $store_id);

                }else{

                    //  Create a new location resource
                    $this->store->createResourceLocation($data, $user);

                }

                //  Return the store
                return $this->store;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a store creation report
     */
    public function generateResourceCreationReport()
    {
        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceCreationReport($this, ['name' => $this->name], $this->id);
    }

    /**
     *  This method updates an existing store
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

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  Set the original user as the primary user responsible for creating this resource
            $template['user_id'] = $this->user_id;

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
     *  This method returns a list of stores
     */
    public function getResources($data = [], $builder = null, $user = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data (CommanTraits)
            $this->getResourcesValidation($data);

            //  If we already have an eloquent builder defined
            if( is_object($builder) ){

                //  Set the stores to this eloquent builder
                $stores = $builder;

            }else{

                //  Get the stores
                $stores = \App\Store::latest();

            }

            //  Filter the stores
            $stores = $this->filterResources($data, $stores, $user);

            //  Return orders
            return $this->collectionResponse($data, $stores, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the stores by search or status
     */
    public function filterResources($data = [], $stores, $user)
    {
        //  If we need to filter for specific stores
        if ( isset($data['type']) && !empty($data['type']) ) {

            $stores = $this->filterResourceStoresByType($data, $stores, $user);

        }

        //  If we need to search for specific stores
        if ( isset($data['search']) && !empty($data['search']) ) {

            $stores = $this->filterResourcesBySearch($data, $stores);

        }

        //  Return the stores
        return $stores;
    }

    /**
     *  This method filters the stores by search
     */
    public function filterResourcesBySearch($data = [], $stores)
    {
        //  Set the search term e.g "Store 1"
        $search_term = $data['search'] ?? null;

        //  Return searched stores otherwise original stores
        return empty($search_term) ? $stores : $stores->search($search_term);

    }

    /**
     *  This method filters the stores by type
     */
    public function filterResourceStoresByType($data = [], $stores, $user)
    {
        //  Extract the Request Object data (CommanTraits)
        $data = $this->extractRequestData($data);

        //  Set the type e.g "created", "shared" or "favourite"
        $type = $data['type'] ?? null;

        //  If we want created stores
        if ($type === 'created') {

            //  Scope stores created by the user
            $stores = $stores->asOwner($user->id);

        //  If we want shared stores
        } elseif ($type === 'shared') {

            //  Scope stores shared with the user
            $stores = $stores->asNonOwner($user->id);

        //  If we want favourite stores
        } elseif ($type === 'favourite') {

            //  Scope stores favourated by the user
            $stores = (new \App\Store)->asFavourite($user->id);

        }

        //  Return the stores
        return $stores;
    }

    /**
     *  This method returns a single store
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $store = \App\Store::where('id', $id)->first() ?? null;

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
     *  This method generates a store subscription
     */
    public function generateResourceSubscription($data = [], $user)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the sms owning model
            $model = $this;

            //  Create a new subscription
            $subscription = ( new \App\Subscription() )->createResource($data, $model, $user);

            //  Generate visit short code (CommonTraits)
            $this->generateResourceVisitShortCode($user);

            //  Expire payment short codes (CommonTraits)
            $this->expirePaymentShortCode();

            //  Load the visit short code
            $this->load('visitShortCode');

            //  Send the payment request sms
            $this->sendSubscriptionSuccessSms($user);

            //  Return the new subscription
            return $subscription;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the subscription success message to the merchant
     */
    public function sendSubscriptionSuccessSms($user = null)
    {
        try {

            //  Set the store visit short code
            $visit_short_code = $this->visitShortCode;

            //  If we have the visit short code
            if( $visit_short_code ){

                //  Set expiry to the same time as the subscription end datetime
                $expiry_date = Carbon::parse($visit_short_code->expires_at)->format('d/m/Y H:i');

                //  Craft the sms message
                $message = 'Subscription for '.$this->name.' successful. Dial '.$visit_short_code->dialing_code.
                           ' to visit your store. Expires on '.$expiry_date;

                $type = 'Store subscription confirmation';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $message,

                    //  Set the mobile_number on the data
                    'mobile_number' => $user->mobile_number['number_with_code']

                ];

                //  Set the sms owning model
                $model = $this;

                //  Create a new sms and send
                return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new store location
     */
    public function createResourceLocation($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the location (Optional field)
            $location = $data['location'] ?? [];

            //  Set the user id (Optional field)
            $user_id = $data['user_id'] ?? null;

            //  Overide data with location data
            $data = $location;

            //  Count the total number of store locations
            $total = $this->locations()->count();

            //  Set the location name
            $name = $data['name'] ?? (($total == 0) ? 'Main Branch' : 'Location ' . (++$total));

            //  Merge the data with additional fields
            $data = array_merge($data, [

                //  Set the location name on the data
                'name' => $name,

                //  Set the store id on the data
                'store_id' => $this->id

            ]);

            if( $user_id ){

                //  Merge the data with additional fields
                $data = array_merge($data, [

                    //  Set the user id on the data
                    'user_id' => $user_id,

                ]);

            }

            /**
             *  Create new a location resource
             */
            return ( new \App\Location() )->createResource($data, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of store locations
     */
    public function getResourceLocations($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the store locations
            $locations = $this->locations()->latest();

            //  Return a list of store locations
            return (new \App\Location())->getResources($data, $locations, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a store
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
     *  This method clones the store related resources
     */
    public function cloneExternalResources($data = [], $store_id)
    {
        try {

            //  Retrieve the store to clone
            $store_to_clone = \App\Store::find($store_id);

            /***************************
             *  CLONE LOCATIONS        *
             **************************/

            // Check if we should clone the store locations
            if ( $data['clone_locations'] ) {

                //  Load the locations to clone
                $store_to_clone->load(['locations' => function ($query) {
                    $query->orderBy('id', 'asc');
                }]);

                //  Get the locations to clone
                $locations_to_clone = $store_to_clone->locations;

                //  Set the data of the locations to clone
                $locations_to_clone_data = collect($locations_to_clone)->map(function ($location, $key) {

                    //  Get the location fields and values
                    $location = $location->getAttributes();

                    //  Unset the location id, created_at and updated_at
                    unset($location['id'], $location['created_at'], $location['updated_at']);

                    //  Update the owning user
                    $location['user_id'] = auth('api')->user()->id;

                    //  Update the owning store
                    $location['store_id'] = $this->store->id;

                    //  Return location to copy
                    return $location;

                })->toArray();

                //  Create the locations (returns true on success)
                $hasClonedLocations = \App\Location::insert($locations_to_clone_data);

                //  Get the store products we just cloned
                $cloned_locations = $this->store->locations()->orderBy('id', 'asc')->get();

                /********************************
                 *  SET LOCATION PERMISSIONS    *
                 *******************************/

                //  Foreach cloned location
                foreach ($cloned_locations as $location) {

                    //  Associate the location with the current user as admin
                    auth('api')->user()->locations()->save($location,
                    //  Pivot table values
                    [
                        'type' => 'admin',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                }
            }

            /***************************
             *  CLONE PRODUCTS        *
             **************************/

            // Check if we should clone the store products
            if ($data['clone_products']) {

                //  Load the products to clone (order by id)
                $store_to_clone->load(['products' => function ($query) {
                    $query->orderBy('id', 'asc');
                }]);

                //  Get the products to clone
                $products_to_clone = $store_to_clone->products;

                //  Set the data of the products to clone
                $products_to_clone_data = collect($products_to_clone)->map(function ($product, $key) {
                    //  Get the product fields and values
                    $product = $product->getAttributes();

                    //  Unset the product id, created_at and updated_at
                    unset($product['id'], $product['created_at'], $product['updated_at']);

                    //  Update the owning user
                    $product['user_id'] = auth('api')->user()->id;

                    //  Update the owning store
                    $product['store_id'] = $this->store->id;

                    //  Return product to copy
                    return $product;
                })->toArray();

                //  Create the products (returns true on success)
                $hasClonedProducts = \App\Product::insert($products_to_clone_data);

                //  If we cloned the products
                if ($hasClonedProducts) {
                    //  Get the store products we just cloned
                    $cloned_products = $this->store->products()->orderBy('id', 'asc')->get();

                    /** Update the "parent_product_id" of every product to match the corrent "parent product id".
                     *  This is important so that we can match every product variation with its correct parent.
                     *  Currently the products are pointing to the "id" of the products used to clone instead
                     *  of the products that were actually created after cloning.
                     * */
                    $matching_parent_product_ids = collect($cloned_products)->map(function ($cloned_product, $key) use ($cloned_products, $products_to_clone) {
                        //  If this product is a variation
                        if (!is_null($cloned_product['parent_product_id'])) {
                            //  Get the index of the parent product that matches the "parent_product_id"
                            $index = $products_to_clone->search(function ($product) use ($cloned_product) {
                                //  If the ids match, then return the index of this parent product
                                return $product['id'] == $cloned_product['parent_product_id'];
                            });

                            /* Use the index to locate the corrent parent product, then use its "id" to
                            *  update the variation "parent_product_id" with the corrent "parent id"
                            */
                            return [
                                'old_parent_product_id' => $products_to_clone[$index]['id'],
                                'new_parent_product_id' => $cloned_products[$index]['id'],
                            ];
                        }

                        /* Use the index to locate the corrent parent product, then use its "id" to
                        *  update the variation "parent_product_id" with the corrent "parent id"
                        */
                        return null;

                        /* Remove any non-variation records. This means that if we have a record that has a null value
                        *  assigned, then this record represents parent products, therefore it does not have the
                        *  variation product ids that must be updated. We must filter out such records.
                        *
                        *  After this, make sure to only retrieve unique records (No repeating records)
                        *
                        *  Finally convert the entire output to an Array
                        */
                    })->filter()->unique('old_parent_product_id')->toArray();

                    foreach ($matching_parent_product_ids as $record) {
                        //  Update the cloned products to corrently set the "parent_product_id" for every product
                        $this->store->products()->where('parent_product_id', $record['old_parent_product_id'])
                                                ->update(['parent_product_id' => $record['new_parent_product_id']]);
                    }

                    //  Get the store products we just cloned
                    $cloned_products = $this->store->products()->orderBy('id', 'asc')->get();

                    /************************************
                     *  ASSIGN VARIANTS TO VARIATIONS  *
                     ***********************************/

                    /** Get the product variations to clone.
                     *  The values() method is used to return a new collection with the keys reset to consecutive integers.
                     */
                    $variations_to_clone = collect($products_to_clone)->filter(function ($product, $key) {
                        //  If the product "parent_product_id" is not null then this is a variation
                        return !is_null($product['parent_product_id']);
                    })->values();

                    /** Get the product variations we just cloned.
                     *  The values() method is used to return a new collection with the keys reset to consecutive integers.
                     */
                    $cloned_variations = collect($cloned_products)->filter(function ($product, $key) {
                        //  If the product "parent_product_id" is not null then this is a variation
                        return !is_null($product['parent_product_id']);
                    })->values();

                    $matching_variation_ids = collect($cloned_variations)->map(function ($cloned_variation, $key) use ($variations_to_clone) {
                        return [
                            'old_variation_id' => $variations_to_clone[$key]['id'],
                            'new_variation_id' => $cloned_variation['id'],
                        ];
                    })->toArray();

                    //  Get the records that manage the assignment of variables to the relevant product variations
                    $records_to_clone = \App\Variable::whereIn('product_id', $variations_to_clone->pluck('id'))->get();

                    //  Set the records that assign the variables to the relevant product product variations
                    $variable_to_variation_records_to_clone = collect($records_to_clone)->map(function ($record, $key) use ($matching_variation_ids) {
                        //  Get the record fields and values
                        $record = $record->getAttributes();

                        //  Unset the record id
                        unset($record['id']);

                        //  Update the product id
                        for ($i = 0; $i < count($matching_variation_ids); ++$i) {
                            if ($record['product_id'] == $matching_variation_ids[$i]['old_variation_id']) {
                                $record['product_id'] = $matching_variation_ids[$i]['new_variation_id'];
                            }
                        }

                        //  Update timestamps
                        $record['updated_at'] = (\Carbon\Carbon::now())->format('Y-m-d H:i:s');
                        $record['created_at'] = (\Carbon\Carbon::now())->format('Y-m-d H:i:s');

                        //  Return record to copy
                        return $record;
                    })->toArray();

                    //  Create the records to assign the variables to the relevant locations (for the cloned products and locations)
                    $hasClonedVariables = \App\Variable::insert($variable_to_variation_records_to_clone);

                    /**********************************
                     *  ASSIGN PRODUCTS TO LOCATIONS  *
                     *********************************/

                    // If we cloned the store locations
                    if ( $data['clone_locations'] ) {
                        //  Get the old and new cloned product ids according to how they are in sync with regards to position
                        $matching_product_ids = collect($cloned_products)->map(function ($cloned_product, $key) use ($products_to_clone) {
                            return [
                                'old_product_id' => $products_to_clone[$key]['id'],
                                'new_product_id' => $cloned_product['id'],
                            ];
                        })->toArray();

                        //  Get the old and new cloned location ids according to how they are in sync with regards to position
                        $matching_location_ids = collect($cloned_locations)->map(function ($cloned_location, $key) use ($locations_to_clone) {
                            return [
                                'old_location_id' => $locations_to_clone[$key]['id'],
                                'new_location_id' => $cloned_location['id'],
                            ];
                        })->toArray();

                        //  Get the records that manage the assignment of products to the relevant locations
                        $records_to_clone = DB::table('location_product')->whereIn('location_id', $locations_to_clone->pluck('id'))->get();

                        //  Set the records that assign the products to the relevant locations (for the cloned products and locations)
                        $product_to_location_records_to_clone = collect($records_to_clone)->map(function ($record, $key) use ($matching_product_ids, $matching_location_ids) {
                            //  Convert the record to array
                            $record = (array) $record;

                            //  Unset the record id
                            unset($record['id']);

                            //  Update the product id
                            for ($i = 0; $i < count($matching_product_ids); ++$i) {
                                if ($record['product_id'] == $matching_product_ids[$i]['old_product_id']) {
                                    $record['product_id'] = $matching_product_ids[$i]['new_product_id'];
                                }
                            }

                            //  Update the location id
                            for ($i = 0; $i < count($matching_location_ids); ++$i) {
                                if ($record['location_id'] == $matching_location_ids[$i]['old_location_id']) {
                                    $record['location_id'] = $matching_location_ids[$i]['new_location_id'];
                                }
                            }

                            //  Update timestamps
                            $record['updated_at'] = (\Carbon\Carbon::now())->format('Y-m-d H:i:s');
                            $record['created_at'] = (\Carbon\Carbon::now())->format('Y-m-d H:i:s');

                            //  Return record to copy
                            return $record;
                        })->toArray();

                        //  Create the records to assign the products to the relevant locations (for the cloned products and locations)
                        $cloned = DB::table('location_product')->insert($product_to_location_records_to_clone);
                    }
                }
            }

        } catch (\Exception $e) {
            throw($e);
        }
    }

    /**
     *  This method returns a list of location reports
     */
    public function getResourceStatistics($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the reports
            $reports = $this->reports();

            //  Return a list of location reports
            return (new \App\Report())->getResourceStatistics($data, $reports, $paginate, $convert_to_api_format);

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
                if ($user->can('create', Store::class) === false) {

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
     *  This method checks permissions for subscribing to an existing resource
     */
    public function subscribeResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to subscribe the resource
                if ($user->can('subscribe', $this)) {

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
                'name' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\s]+$/i',
                'offline_message' => 'sometimes|nullable|string|max:255',
                'clone_store_id' => 'sometimes|required|numeric',
                'user_id' => 'sometimes|required|numeric',
                'online' => 'sometimes|required|boolean',
            ];

            //  Set validation messages
            $messages = [

                'name.required' => 'The store name is required e.g Heavenly Fruits',
                'name.string' => 'The store name must be a valid string e.g Heavenly Fruits',
                'name.min' => 'The store name must be atleast 3 characters long',
                'name.max' => 'The store name must not be more than 50 characters long',
                'name.regex' => 'The store name must contain only letters, numbers and spaces e.g Heavenly Fruits',

                'offline_message.string' => 'The store offline message must be a valid string e.g We are currently offline',
                'offline_message.max' => 'The store offline message must not be more than 255 characters long',

                'clone_store_id.required' => 'Provide a valid store id to clone e.g 123',
                'clone_store_id.numeric' => 'The store id to clone must be a valid number e.g 123',

                'user_id.required' => 'Provide a valid user id to assign to this store e.g 123',
                'user_id.numeric' => 'The user id must be a valid number e.g 123',

                'online.boolean' => 'The store online attribute must be a boolean e.g true, false, 1 or 0',

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

    /**
     *  This method verifies if the user is the owner of the store
     */
    public function isOwner($resource = null)
    {
        try {

            //  Retrieve the User ID
            $user_id = ($resource instanceof \App\User) ? $resource->id : $resource;

            //  Check if this is the owner
            return ( !empty($user_id) ) ? $this->whereUserId($user_id)->exists() : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method verifies if the user is the owner of the store
     */
    public function isSubscribed($resource = null)
    {
        try {

            //  Retrieve the User ID
            $user_id = ($resource instanceof \App\User) ? $resource->id : $resource;

            //  Check if this is the owner
            return ( !empty($user_id) ) ? $this->subscriptions()->active()->asOwner($user_id) : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
