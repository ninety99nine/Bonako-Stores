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

    public function createStore(Request $request)
    {
        try {

            //  Return a new store
            return (new Store())->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateStore(Request $request, $store_id)
    {
        try {

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exists
            if ($store) {

                //  Return the updated store
                return $store->updateResource($request, $this->user)->convertToApiFormat();

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getStores(Request $request)
    {
        try {

            //  Return a list of stores
            return (new Store())->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getStore($store_id)
    {
        try {

            //  Return a single store
            return (new Store())->getResource($store_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generateSubscription(Request $request, $store_id)
    {
        try {

            //  Return the generated subscription
            return (new Store())->getResource($store_id)->generateResourceSubscription($request)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generatePaymentShortCode($store_id)
    {
        try {

            //  Return the generated payment short code
            return (new Store())->getResource($store_id)->generateResourcePaymentShortCode()->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function createStoreLocation(Request $request, $store_id)
    {
        try {

            //  Return a new location
            return (new Store())->getResource($store_id)->createResourceLocation($request, $this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getStoreLocations(Request $request, $store_id)
    {
        try {

            //  Return a list of store locations
            return (new Store())->getResource($store_id)->getResourceLocations($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteStore($store_id)
    {
        try {

            //  Delete the store
            return (new Store())->getResource($store_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
