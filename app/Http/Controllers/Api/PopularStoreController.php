<?php

namespace App\Http\Controllers\Api;

use App\PopularStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PopularStoreController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createPopularStore(Request $request)
    {
        try {

            //  Return a new popular store
            return (new PopularStore)->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updatePopularStore(Request $request, $popular_store_id)
    {
        try {

            //  Get the popular popular store
            $popular_store = PopularStore::where('id', $popular_store_id)->first() ?? null;

            //  Check if the popular popular store exists
            if ($popular_store) {

                //  Return the updated popular popular store
                return $popular_store->updateResource($request, $this->user)->convertToApiFormat();

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getPopularStores(Request $request)
    {
        try {

            //  Return a list of popular stores
            return (new PopularStore)->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getPopularStore($popular_store_id)
    {
        try {

            //  Return a single popular store
            return (new PopularStore)->getResource($popular_store_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deletePopularStore($popular_store_id)
    {
        try {

            //  Delete the popular store
            return (new PopularStore)->getResource($popular_store_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function arrangePopularStores(Request $request)
    {
        try {

            //  Arrange popular stores
            return (new PopularStore())->reorderPopularStores($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
