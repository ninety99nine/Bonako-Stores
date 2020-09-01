<?php

namespace App\Http\Controllers\Api;

use App\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createLocation( Request $request )
    {
        //  Check if the user is authourized to update the create
        if ($this->user && $this->user->can('create', Location::class)) {

            //  Create the location
            $location = (new Location)->initiateCreate( $request );

            //  If the created successfully
            if( $location ){

                //  Return an API Readable Format of the Location Instance
                return $location->convertToApiFormat();

            }

        } else {

            //  Not Authourized
            return help_not_authorized();

        }
    }

    public function getLocations(Request $request)
    {
        $limit = $request->input('limit');

        //  Get the locations
        $locations = \App\Location::latest()->paginate($limit) ?? null;

        //  Check if the locations exist
        if ($locations) {
                
            //  Return an API Readable Format of the Location Instance
            return ( new \App\Location() )->convertToApiFormat($locations);
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function getLocation($location_id)
    {
        //  Get the location
        $location = Location::where('id', $location_id)->first() ?? null;

        //  Check if the location exists
        if ($location) {

            //  Return an API Readable Format of the Location Instance
            return $location->convertToApiFormat();
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function updateLocation( Request $request, $location_id )
    {
        //  Get the location
        $location = \App\Location::where('id', $location_id)->first() ?? null;

        //  Check if the location exists
        if ($location) {

            //  Check if the user is authourized to update the location
            if ($this->user && $this->user->can('update', $location)) {

                //  Update the location
                $updated = $location->update( $request->all() );

                //  If the update was successful
                if( $updated ){

                    //  Return an API Readable Format of the Location Instance
                    return $location->fresh()->convertToApiFormat();

                }

            } else {

                //  Not Authourized
                return help_not_authorized();

            }
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function getLocationProducts( Request $request, $location_id)
    {
        $limit = $request->input('limit');

        //  Get the location
        $location = \App\Location::where('id', $location_id)->first() ?? null;

        //  Get the location products
        $products = $location->products()->paginate($limit) ?? null;

        //  Check if the location products exist
        if ($products) {

            //  Return an API Readable Format of the Product Instance
            return ( new \App\Product() )->convertToApiFormat($products);
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function getLocationPaymentMethods( Request $request, $location_id)
    {
        $limit = $request->input('limit');

        //  Get the location
        $location = \App\Location::where('id', $location_id)->first() ?? null;

        //  Get the location payment methods
        $payment_methods = $location->paymentMethods()->paginate($limit) ?? null;

        //  Check if the location products exist
        if ($payment_methods) {

            //  Return an API Readable Format of the PaymentMethod Instance
            return ( new \App\PaymentMethod() )->convertToApiFormat($payment_methods);
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function deleteLocation( Request $request, $location_id )
    {
        //  Get the location
        $location = \App\Location::where('id', $location_id)->first() ?? null;

        //  Check if the location exists
        if ($location) {

            //  Check if the user is authourized to permanently delete the location
            if ($this->user && $this->user->can('forceDelete', $location)) {

                //  Delete the location
                $location->delete();

                //  Return nothing
                return response()->json(null, 200);

            } else {

                //  Not Authourized
                return help_not_authorized();

            }
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }

    }

}
