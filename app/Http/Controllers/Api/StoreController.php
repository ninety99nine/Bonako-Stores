<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
            $store = (new Store())->createResource($request, $this->user);

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
                return $store->updateResource($request, $this->user);

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
            return (new Store())->getResourses($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getStore($store_id)
    {
        try {

            //  Return a single store
            return (new Store())->getResourse($store_id);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generatePaymentShortCode($store_id)
    {
        try {

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exists
            if ($store) {

                //  Return the generated payment short code
                return $store->generatePaymentShortCode();

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function generateSubscription(Request $request, $store_id)
    {
        try {

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exists
            if ($store) {

                //  Return the generated subscription
                return $store->generateSubscription($request);

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function addOrRemoveStoreAsFavourite(Request $request, $store_id)
    {
        try {

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exists
            if ($store) {

                //  Add or remove store as favourite
                $success = $store->addOrRemoveStoreAsFavourite($store_id);

                //  If successful
                if($success){

                    //  Return success status
                    return response()->json(null, 200);

                }

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }

    }

    public function getStoreRatingStatistics(Request $request, $store_id)
    {
        try {
            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exist
            if ($store) {
                //  Get the latest 500 ratings of this store
                $ratings = $store->ratings()->latest()->take(500)->get();

                $total_ratings = count($ratings);
                $highest_rating = collect($ratings)->max('value');
                $lowest_rating = collect($ratings)->min('value');

                //  Get the most recurring rating e.g "4"
                $rating_mode = collect($ratings)->mode('value') ?? [];

                //  Get the average rating e.g "4.666666666666667"
                $average_rating = $total_ratings ? collect($ratings)->sum('value') / $total_ratings : null;

                return response()->json([
                    'total_ratings' => $total_ratings,
                    'highest_rating' => $highest_rating,
                    'lowest_rating' => $lowest_rating,
                    'rating_mode' => count($rating_mode) ? $rating_mode[0] : null,
                    'average_rating' => [
                        //  Average for system e.g "4.666666666666667"
                        'actual' => $average_rating,

                        //  Average for merchants e.g "4.67"
                        'seller' => !is_null($average_rating) ? round($average_rating, 2) : null,

                        //  Average for customers e.g "4.7"
                        'buyer' => !is_null($average_rating) ? round($average_rating, 1) : null,
                    ],
                ], 200);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStoreLocations(Request $request, $store_id)
    {
        try {

            $type = $request->input('type');
            $limit = $request->input('limit');

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            $locations = $store->locations();

            if( $type == 'favourite' ){
                //  Filter favourite locations
                $locations = $locations->asFavourite($this->user->id);
            }

            //  If we need to search for specific locations
            if (!empty($search_term)) {
                //  Filter by search term
                $locations = $locations->search($search_term);
            }

            //  Get the store locations
            $locations = $locations->paginate($limit) ?? null;

            //  Return an API Readable Format of the Location Instance
            return ( new \App\Location() )->convertToApiFormat($locations);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getStoreProducts(Request $request, $store_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Get the store products
            $products = $store->products()->paginate($limit) ?? null;

            //  Check if the store products exist
            if ($products) {
                //  Return an API Readable Format of the Product Instance
                return ( new \App\Product() )->convertToApiFormat($products);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStoreUsers(Request $request, $store_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store users exist
            if ($store) {
                //  Get the store users
                $users = $store->users()->paginate($limit);

                //  Return an API Readable Format of the User Instance
                return ( new \App\User() )->convertToApiFormat($users);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStoreCoupons(Request $request, $store_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Get the store coupons
            $coupons = $store->coupons()->paginate($limit) ?? null;

            //  Check if the store coupons exist
            if ($coupons) {
                //  Return an API Readable Format of the Coupon Instance
                return ( new \App\Coupon() )->convertToApiFormat($coupons);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStoreInstantCarts(Request $request, $store_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Get the store instant carts
            $instant_carts = $store->instantCarts()->paginate($limit) ?? null;

            //  Check if the store instant carts exist
            if ($instant_carts) {
                //  Return an API Readable Format of the Instant Cart Instance
                return ( new \App\InstantCart() )->convertToApiFormat($instant_carts);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function deleteStore(Request $request, $store_id)
    {
        try {
            //  Get the store
            $store = \App\Store::where('id', $store_id)->first() ?? null;

            //  Check if the store exists
            if ($store) {
                //  Check if the user is authourized to permanently delete the store
                if ($this->user && $this->user->can('forceDelete', $store)) {
                    //  Delete the store
                    if ($store->delete()) {
                        /* Return 204 (No Content) status code since the action has been enacted and no
                         *  further information is to be supplied.
                         */
                        return response()->json(null, 204);
                    }
                } else {
                    //  Not Authourized
                    return help_not_authorized();
                }
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }
}
