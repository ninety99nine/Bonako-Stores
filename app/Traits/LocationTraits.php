<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
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
        if( $locations ){
                
            //  Transform the multiple instances
            return new LocationsResource($locations);

        }else{
            
            //  Transform the single instance
            return new LocationResource($this);

        }
    }
 
    /*  This method creates a new Version
     */
    public function initiateCreate( $request )
    {
        //  Set the request variable
        $this->request = $request;

        //  Validate the request
        $validation_data = $request->validate([
            'name' => 'required'
        ]);

        //  If we have the location id representing the location to clone
        if ( $request->input('clone_location_id') ) {

            //  Retrieve the location to clone
            $this->location_to_clone = \App\Location::where('id', $request->input('clone_location_id') )->first();

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

        try {

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

            //  Throw a validation error
            throw $e;
            
        }
    }

    public function assignUserAsAdmin()
    {
        //  Associate the location with the current user as admin
        auth('api')->user()->locations()->save($this->location,
        //  Pivot table values
        [
            'type' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

}
