<?php

namespace App\Http\Controllers\Api;

use App\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createStore( Request $request )
    {
        //  Check if the user is authourized to update the create
        if ($this->user && $this->user->can('create', Store::class)) {

            //  Create the store
            $store = (new Store)->initiateCreate( $request );

            //  If the created successfully
            if( $store ){

                //  Return an API Readable Format of the Store Instance
                return $store->convertToApiFormat();

            }

        } else {

            //  Not Authourized
            return help_not_authorized();

        }
    }

    public function getStores(Request $request)
    {
        $limit = $request->input('limit');

        //  Get the stores
        $stores = \App\Store::latest()->paginate($limit) ?? null;

        //  Check if the stores exist
        if ($stores) {
                
            //  Return an API Readable Format of the Store Instance
            return ( new \App\Store() )->convertToApiFormat($stores);
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function getStore($store_id)
    {
        //  Get the store
        $store = Store::where('id', $store_id)->first() ?? null;

        //  Check if the store exists
        if ($store) {

            //  Return an API Readable Format of the Store Instance
            return $store->convertToApiFormat();
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function updateStore( Request $request, $store_id )
    {
        //  Get the store
        $store = \App\Store::where('id', $store_id)->first() ?? null;

        //  Check if the store exists
        if ($store) {

            //  Check if the user is authourized to update the store
            if ($this->user && $this->user->can('update', $store)) {

                //  Update the store
                $updated = $store->update( $request->all() );

                //  If the update was successful
                if( $updated ){

                    //  Return an API Readable Format of the Store Instance
                    return $store->fresh()->convertToApiFormat();

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

    public function getStoreLocations( Request $request, $store_id)
    {
        $limit = $request->input('limit');

        //  Get the store
        $store = \App\Store::where('id', $store_id)->first() ?? null;

        //  Get the store locations
        $locations = $store->locations()->paginate($limit) ?? null;

        //  Check if the store locations exist
        if ($locations) {

            //  Return an API Readable Format of the Location Instance
            return ( new \App\Location() )->convertToApiFormat($locations);
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function deleteStore( Request $request, $store_id )
    {
        //  Get the store
        $store = \App\Store::where('id', $store_id)->first() ?? null;

        //  Check if the store exists
        if ($store) {

            //  Check if the user is authourized to permanently delete the store
            if ($this->user && $this->user->can('forceDelete', $store)) {

                //  Delete the store
                $store->delete();

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
