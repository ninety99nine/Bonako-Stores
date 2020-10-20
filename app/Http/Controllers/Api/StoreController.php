<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Store;
use Illuminate\Http\Request;

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
            //  Check if the user is authourized to update the create
            if ($this->user && $this->user->can('create', Store::class)) {
                //  Create the store
                $store = (new Store())->initiateCreate($request);

                //  If the created successfully
                if ($store) {
                    //  Return an API Readable Format of the Store Instance
                    return $store->convertToApiFormat();
                }
            } else {
                //  Not Authourized
                return help_not_authorized();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStores(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStore($store_id)
    {
        try {
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
                //  Check if the user is authourized to update the store
                if ($this->user && $this->user->can('update', $store)) {
                    //  Update the store
                    $updated = $store->update($request->all());

                    //  If the update was successful
                    if ($updated) {
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
                return help_resource_not_fonud();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getStoreLocations(Request $request, $store_id)
    {
        try {
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
                return help_resource_not_fonud();
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
                return help_resource_not_fonud();
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
                return help_resource_not_fonud();
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
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }
}
