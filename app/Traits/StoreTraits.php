<?php

namespace App\Traits;

use App\Http\Resources\Store as StoreResource;
use App\Http\Resources\Stores as StoresResource;
use DB;
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
        //  Set the request variable
        $this->request = $request;

        //  Validate the request
        $validation_data = $request->validate([
            'name' => 'required',
        ]);

        //  If we have the store id representing the store to clone
        if ($request->input('clone_store_id')) {
            //  Retrieve the store to clone
            $this->store_to_clone = \App\Store::where('id', $request->input('clone_store_id'))->first();
        }

        //  Set the template
        $template = [
            'name' => $request->input('name'),
            'online' => $request->input('online'),
            'user_id' => auth('api')->user()->id ?? $request->input('user_id'),
            'offline_message' => $request->input('offline_message') ?? $this->default_offline_offline_message,
            'currency' => [
                'code' => 'BWP',
                'symbol' => 'P',
            ],
        ];

        try {
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
            //  Throw a validation error
            throw ValidationException::withMessages(['general' => $e->getMessage()]);
        }
    }

    public function assignUserAsAdmin()
    {
        auth('api')->user()->stores()->save($this->store,
        //  Pivot table values
        [
            'type' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function assignLocationAndManageCloning()
    {
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
    }

    /*
     *  Checks if a given user is the owner of the store
     */
    public function isOwner($user_id)
    {
        return $this->whereUserId($user_id)->exists();
    }

    /*
     *  Checks if a given user is the admin of the store
     */
    public function isAdmin($user_id = null)
    {
        if ($user_id) {
            return $this->users()->wherePivot('user_id', $user_id)->wherePivot('type', 'admin')->exists();
        }
    }

    /*
     *  Checks if a given user is a viewer of the store
     */
    public function isViewer($user_id = null)
    {
        if ($user_id) {
            return $this->users()->wherePivot('user_id', $user_id)->wherePivot('type', 'viewer')->exists();
        }
    }
}
