<?php

namespace App\Traits;

use DB;
use App\Http\Resources\Location as LocationResource;
use App\Http\Resources\Locations as LocationsResource;

trait LocationTraits
{
    public $request = null;
    public $location = null;
    public $location_to_clone = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($locations = null)
    {
        if ($locations) {
            //  Transform the multiple instances
            return new LocationsResource($locations);
        } else {
            //  Transform the single instance
            return new LocationResource($this);
        }
    }

    /**  initiateCreate()
     *
     *  This method creates a new Location
     */
    public function initiateCreate($request)
    {
        try {

            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $validation_data = $request->validate([
                'name' => 'required',
            ]);

            //  If we have the location id representing the location to clone
            if ($request->input('clone_location_id')) {
                //  Retrieve the location to clone
                $this->location_to_clone = \App\Location::where('id', $request->input('clone_location_id'))->first();
            }

            //  Set the template
            $template = [
                'user_id' => auth('api')->user()->id,
                'name' => $this->request->input('name'),
                'online' => $this->request->input('online'),
                'about_us' => $this->request->input('about_us'),
                'store_id' => $this->request->input('store_id'),
                'contact_us' => $this->request->input('contact_us'),
                'abbreviation' => $this->request->input('abbreviation'),
                'allow_payments' => $this->request->input('allow_payments'),
                'call_to_action' => $this->request->input('call_to_action'),
                'allow_delivery' => $this->request->input('allow_delivery'),
                'offline_message' => $this->request->input('offline_message'),
                'delivery_note' => $this->request->input('delivery_note'),
                'delivery_flat_fee' => $this->request->input('delivery_flat_fee'),
                'delivery_days' => $this->request->input('delivery_days'),
                'pickup_days' => $this->request->input('pickup_days'),
                'delivery_times' => $this->request->input('delivery_times'),
                'pickup_note' => $this->request->input('pickup_note'),
                'pickup_times' => $this->request->input('pickup_times'),
                'delivery_destinations' => $this->request->input('delivery_destinations'),
                'pickup_destinations' => $this->request->input('pickup_destinations'),
                'allow_pickups' => $this->request->input('allow_pickups'),
                'online_payment_methods' => $this->request->input('online_payment_methods'),
                'offline_payment_methods' => $this->request->input('offline_payment_methods'),
            ];

            /*
             *  Create new a location, then retrieve a fresh instance
             */
            $this->location = $this->create($template);

            //  If the location was created successfully
            if ($this->location) {
                //  Assign user as an Admin to this location
                $this->assignUserAsAdmin();

                return $this->location->fresh();
            }


        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**  initiateUpdateProductArrangement()
     *
     *  This method updates the arrangement of products in the current location.
     *  The logic allows us to run a single query to update multiple products
     *  with different values of their arrangement for a given location.
     */
    public function initiateUpdateProductArrangement($request)
    {
        try {

            //  Get the products assigned to this location (Products must not be variations)
            $products = collect($this->products()->isNotVariation()->get())->toArray();

            //  Get the request product arrangement
            $product_arrangements = $request->input('product_arrangements');

            $ids = [];
            $cases = [];
            $params = [];

            //  Foreach product we must arrange
            foreach ($product_arrangements as $key => $product_arrangement) {

                $id = $product_arrangement['id'];
                $arrangement = $product_arrangement['arrangement'];

                $cases[] = "WHEN {$id} then ?";
                $params[] = $arrangement;
                $ids[] = $id;

                //  Remove from the products
                $products = collect($products)->reject(function ($product) use ($id){
                    return $product['id'] == $id;
                });

            }

            //  Count how many products we have arranged
            $total_product_arrangements = count($product_arrangements);

            //  Foreach product we did not arrange
            foreach ($products as $key => $product) {

                $id = $product['id'];
                $arrangement = $total_product_arrangements + ($key + 1);

                $cases[] = "WHEN {$id} then ?";
                $params[] = $arrangement;
                $ids[] = $id;

            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);

            if (!empty($ids)) {

                DB::update("UPDATE products SET `arrangement` = CASE `id` {$cases} END WHERE `id` in ({$ids})", $params);

            }

            //  Return the location products in their new order
            return $this->products()->paginate();

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

            //  Add the user as an Admin to the current location
            DB::table('location_user')->insert([
                'type' => 'admin',
                'user_id' => $user_id,
                'location_id' => $this->location->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /*
     *  Checks if a given user is the owner of the location
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
    *  Checks if a given user is the admin of the location
    */
   public function isAdmin($user_id = null)
   {
       try{

           if ( !empty($user_id) ) {

               return $this->users()->wherePivot('user_id', $user_id)->wherePivot('type', 'admin')->exists();

           }

       } catch (\Exception $e) {

           throw($e);

       }
   }
}
