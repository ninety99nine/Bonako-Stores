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

    public function getStores(Request $request)
    {
        try {
            $limit = $request->input('limit');

            $validator = Validator::make($request->all(), [
                /* Validation Rules
                 *
                 *  If we must limit the results then make sure that the user provided the
                 *  limit value. This value must be a number with a value between 1 and 100.
                 *
                 */
                'limit' => 'sometimes|required|numeric|min:1|max:100',
            ], [
                //  Limit Validation Error Messages
                'limit.required' => 'Enter a valid limit containing only digits e.g 50',
                'limit.regex' => 'Enter a valid limit containing only digits e.g 50',
                'limit.min' => 'The limit attribute must be a value between 1 and 100',
                'limit.max' => 'The limit attribute must be a value between 1 and 100',
            ]);

            //  If the validation failed
            if ($validator->fails()) {
                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());
            }

            //  Get the stores
            $stores = \App\Store::latest()->paginate($limit) ?? [];

            //  Return an API Readable Format of the Store Instance
            return ( new \App\Store() )->convertToApiFormat($stores);
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function createStore(Request $request)
    {
        try {
            //  Check if the user is authourized to create the store
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
                    $store = (new Store())->initiateUpdate($store, $request);

                    //  If the created successfully
                    if ($store) {
                        //  Return an API Readable Format of the Store Instance
                        return $store->convertToApiFormat();
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
                return help_resource_not_fonud();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }
}
