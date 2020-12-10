<?php

namespace App\Traits;

use App\Http\Resources\Store as StoreResource;
use App\Http\Resources\Stores as StoresResource;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait StoreTraits
{
    public $store = null;
    public $request = null;
    public $store_to_clone = null;
    public $default_offline_offline_message = 'Sorry, we are currently offline';

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($stores = null)
    {
        if ($stores) {
            //  Transform the multiple instances
            return new StoresResource($stores);
        } else {
            //  Transform the single instance
            return new StoreResource($this);
        }
    }

    /*  This method creates a new store
     */
    public function initiateCreate($request)
    {
        try {
            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $this->initiateValidation();

            //  If we have the store id representing the store to clone
            if ($request->input('clone_store_id')) {
                //  Retrieve the store to clone
                $this->store_to_clone = \App\Store::find($request->input('clone_store_id'));
            }

            //  Capture the resource fields allowed
            $template = $request->only($this->getFillable());

            //  Set the current authenticated user as the user responsible for creating this resource
            $template['user_id'] = auth('api')->user()->id;

            /*
             *  Create new a store, then retrieve a fresh instance
             */
            $this->store = $this->create($template)->fresh();

            //  If created successfully
            if ($this->store) {
                //  Assign user as an Admin to this store
                $this->assignUserAsAdmin();

                //  Create and assign a location and manage cloning of resources
                $this->assignLocationAndManageCloning();

                //  Return a fresh instance
                return $this->store->fresh();
            }
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /*  This method updates an existing store
     */
    public function initiateUpdate($store, $request)
    {
        try {
            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $this->initiateValidation();

            //  Capture the resource fields allowed
            $template = $request->only($this->getFillable());

            //  Set the original user as the primary user responsible for creating this resource
            $template['user_id'] = $store->user_id;

            //  Update the store details
            $updated = $store->update($template);

            //  If created successfully
            if ($updated) {
                //  Return a fresh instance
                return $store->fresh();
            }
        } catch (\Exception $e) {
            throw($e);
        }
    }

    public function addOrRemoveStoreAsFavourite()
    {
        //  Get the current authenticated user ID
        $user_id = auth()->user()->id;

        //  Check if the user already marked this store as a favourite
        $exists = DB::table('favourites')->where(['store_id' => $this->id, 'user_id' => $user_id])->exists();

        //  If we already have this store marked as a favourite
        if ($exists) {

            //  Unmark store as favourite
            DB::table('favourites')->where(['store_id' => $this->id, 'user_id' => $user_id])->delete();

        //  If we don't already have this store marked as a favourite
        } else {

            //  Mark store as favourite
            DB::table('favourites')->insert([
                'user_id' => $user_id,
                'store_id' => $this->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        }

    }

    public function initiateValidation()
    {
        try {
            //  Validation Rules
            $validationRules = [
                'name' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\s]+$/i',
                'online' => 'sometimes|required|boolean',
                'offline_message' => 'sometimes|nullable|string|max:255',
                'currency' => 'sometimes|required|string|regex:/^[a-zA-Z]+$/i',
                'minimum_stock_quantity' => 'sometimes|required|numeric|min:1',
                'clone_store_id' => 'sometimes|required|numeric',
                'user_id' => 'sometimes|required|numeric',
            ];

            //  Validation Custom Messages
            $validationMessages = [
                //  Name Validation Error Messages
                'name.required' => 'The store name is required e.g Heavenly Fruits',
                'name.string' => 'The store name must be a valid string e.g Heavenly Fruits',
                'name.regex' => 'The store name must contain only letters, numbers and spaces e.g Heavenly Fruits',
                'name.min' => 'The store name must be atleast 3 characters long',
                'name.max' => 'The store name must not be more than 50 characters long',
                //  Online Validation Error Messages
                'online.boolean' => 'The store online attribute must be a boolean e.g true, false, 1 or 0',
                //  Offline Message Validation Error Messages
                'offline_message.string' => 'The store offline message must be a string e.g We are currently offline',
                'offline_message.max' => 'The store offline message must not be more than 255 characters long',
                //  Currency Validation Error Messages
                'currency.required' => 'Enter a valid store currency e.g BWP, ZAR, USD',
                'currency.string' => 'Enter a valid store currency e.g BWP, ZAR, USD',
                'currency.regex' => 'The store currency must be a valid ISO 4217 standard e.g BWP, ZAR, USD',
                //  Minimum Stock Quantity Validation Error Messages
                'minimum_stock_quantity' => 'The store minimum stock quantity must be a valid number greater than 1',
                //  Clone Store Id Validation Error Messages
                'clone_store_id.required' => 'Provide a valid store id for the store to clone e.g 123',
                'clone_store_id.numeric' => 'The store id for the store to clone must be a valid number e.g 123',
                //  User Id Validation Error Messages
                'user_id.required' => 'Provide a valid user id to link to this store e.g 123',
                'user_id.numeric' => 'The user id must be a valid number e.g 123',
            ];

            //  Validate request
            $validator = Validator::make($this->request->all(), $validationRules, $validationMessages);

            //  If the validation failed
            if ($validator->fails()) {
                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());
            }
        } catch (\Exception $e) {
            throw($e);
        }
    }

    public function assignUserAsAdmin()
    {
        try {
            //  Get the currently authenticated users id
            $user_id = auth('api')->user()->id;

            //  If the current authenticated user is a Super Admin
            if (auth('api')->user()->isSuperAdmin()) {
                //  If the Super Admin has provided a user responsible for this resource
                if (!empty($this->request->input('user_id'))) {
                    //  Set the provided user id as the user responsible for this resource
                    $user_id = $this->request->input('user_id');
                }
            }

            //  Add the user as an Admin to the current store
            DB::table('store_user')->insert([
                'type' => 'admin',
                'user_id' => $user_id,
                'store_id' => $this->store->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            throw($e);
        }
    }

    public function assignLocationAndManageCloning()
    {
        try {
            //  If we have locations to clone
            if ($this->store_to_clone) {
                /***************************
                 *  CLONE LOCATIONS        *
                 **************************/

                // Check if we should clone the store locations
                if ($this->request->input('clone_locations')) {
                    //  Load the locations to clone
                    $this->store_to_clone->load(['locations' => function ($query) {
                        $query->orderBy('id', 'asc');
                    }]);

                    //  Get the locations to clone
                    $locations_to_clone = $this->store_to_clone->locations;

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
                if ($this->request->input('clone_products')) {
                    //  Load the products to clone (order by id)
                    $this->store_to_clone->load(['products' => function ($query) {
                        $query->orderBy('id', 'asc');
                    }]);

                    //  Get the products to clone
                    $products_to_clone = $this->store_to_clone->products;

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
                        if ($this->request->input('clone_locations')) {
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
            } else {
                //  Set the location name on the request
                $this->request->merge(['name' => 'Main branch']);

                //  Set the store id on the request
                $this->request->merge(['store_id' => $this->store->id]);

                //  Create a new location
                $location = ( new \App\Location() )->initiateCreate($this->request);
            }
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /*
     *  Checks if a given user is the owner of the store
     */
    public function isOwner($user_id)
    {
        try {
            return $this->whereUserId($user_id)->exists();
        } catch (\Exception $e) {
            throw($e);
        }
    }

    /*
     *  Checks if a given user is the admin of the store
     */
    public function isAdmin($user_id = null)
    {
        try {
            if (!empty($user_id)) {
                return $this->users()->wherePivot('user_id', $user_id)->wherePivot('type', 'admin')->exists();
            }
        } catch (\Exception $e) {
            throw($e);
        }
    }
}
