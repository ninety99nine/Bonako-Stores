<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createLocation(Request $request)
    {
        try {
            //  Check if the user is authourized to update the create
            if ($this->user && $this->user->can('create', Location::class)) {
                //  Create the location
                $location = (new Location())->initiateCreate($request);

                //  If the created successfully
                if ($location) {
                    //  Return an API Readable Format of the Location Instance
                    return $location->convertToApiFormat();
                }
            } else {
                //  Not Authourized
                return help_not_authorized();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getLocations(Request $request)
    {
        try {
            $limit = $request->input('limit');

            //  Get the locations
            $locations = \App\Location::latest()->paginate($limit) ?? null;

            //  Check if the locations exist
            if ($locations) {
                //  Return an API Readable Format of the Location Instance
                return ( new \App\Location() )->convertToApiFormat($locations);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getLocation($location_id)
    {
        try {
            //  Get the location
            $location = Location::find($location_id) ?? null;

            //  Check if the location exists
            if ($location) {
                //  Return an API Readable Format of the Location Instance
                return $location->convertToApiFormat();
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function updateLocation(Request $request, $location_id)
    {
        try {
            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Check if the location exists
            if ($location) {
                //  Check if the user is authourized to update the location
                if ($this->user && $this->user->can('update', $location)) {
                    //  Update the location
                    $updated = $location->update($request->all());

                    //  If the update was successful
                    if ($updated) {
                        //  Return an API Readable Format of the Location Instance
                        return $location->fresh()->convertToApiFormat();
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

    public function getLocationProducts(Request $request, $location_id)
    {
        try {
            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Check if the location products exist
            if ($location) {
                $limit = $request->input('limit');
                $active = $request->input('active');
                $onsale = $request->input('onsale');
                $search_term = $request->input('search');

                $products = $location->products();

                if ($active === '1') {
                    $products = $products->active();
                } elseif ($active === '0') {
                    $products = $products->inActive();
                }

                if ($onsale === '1') {
                    $products = $products->onSale();
                }

                //  If we need to search for specific products
                if (!empty($search_term)) {
                    $products = $products->search($search_term);
                }

                //  Get the location products
                $products = $products->paginate($limit) ?? [];

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

    public function createLocationProduct(Request $request, $location_id)
    {
        try {

            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Check if the location exists
            if ($location) {

                //  Check if the user is authourized to arrange the location products
                if ($this->user && $this->user->can('update', $location)) {

                    //  Check if the user is authourized to create products
                    if ($this->user && $this->user->can('create', \App\Product::class)) {

                        //  Set the location id on the request
                        $request->request->add(['location_id' => $location_id]);

                        //  Create the location product
                        $product = (new \App\Product())->initiateCreate($request);

                        //  If the created successfully
                        if ($product) {

                            //  Return an API Readable Format of the Product Instance
                            return $product->convertToApiFormat();

                        }

                    } else {

                        //  Not Authourized
                        return help_not_authorized();

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

    public function updateLocationProductArrangement(Request $request, $location_id)
    {
        try {

            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Check if the location exists
            if ($location) {

                //  Check if the user is authourized to arrange the location products
                if ($this->user && $this->user->can('update', $location)) {

                    //  Update the product arrangement of the location
                    $products = $location->initiateUpdateProductArrangement($request);

                    //  Return an API Readable Format of the Product Instance
                    return ( new \App\Product() )->convertToApiFormat($products);

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

    public function getLocationUsers(Request $request, $location_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            if (!empty($search_term = $request->input('search'))) {
                //  Get the location products
                $users = $location->users()->search($search_term)->paginate($limit) ?? null;
            } else {
                //  Get the location products
                $users = $location->users()->paginate($limit) ?? null;
            }

            //  Check if the location users exist
            if ($users) {
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

    public function getLocationOrders(Request $request, $location_id)
    {
        try {
            //  Get the limit
            $limit = $request->input('limit');

            //  Get the fulfillment status e.g "open" or "cancelled"
            $status = $request->input('status');

            //  Get the fulfillment status e.g "fulfilled" or "unfulfilled"
            $fulfillment_status = $request->input('fulfillment_status');

            //  Get the is_customer status e.g "0" or "1"
            $is_customer = $request->input('is_customer');

            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Get the location orders
            $orders = $location->orders();

            //  If we want only open orders
            if ($status === 'open') {
                //  Only fetch open orders
                $orders = $orders->open();
            //  If we want only archieved orders
            } elseif ($status === 'archieved') {
                //  Only fetch archieved orders
                $orders = $orders->archieved();
            } elseif ($status === 'cancelled') {
                //  Only fetch cancelled orders
                $orders = $orders->cancelled();
            }

            //  If we want only fulfilled orders
            if ($fulfillment_status === 'fulfilled') {
                //  Only fetch fulfilled orders
                $orders = $orders->fulfilled();
            //  If we want only unfulfilled orders
            } elseif ($fulfillment_status === 'unfulfilled') {
                //  Only fetch unfulfilled orders
                $orders = $orders->unfulfilled();
            }

            //  If we want to search
            if (!empty($search_term = trim($request->input('search')))) {
                //  Search order
                $orders = $orders->search($search_term);
            }

            //  Paginate orders
            $orders = $orders->paginate($limit);

            //  Check if the location orders exist
            if ($orders) {
                //  Return an API Readable Format of the Order Instance
                return ( new \App\Order() )->convertToApiFormat($orders);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getLocationUnratedOrders(Request $request, $location_id)
    {
        try {
            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Get the location orders that require a rating
            $orders = $location->orders()->requireRating();

            //  Paginate orders
            $orders = $orders->paginate($limit);

            //  Check if the location orders exist
            if ($orders) {
                //  Return an API Readable Format of the Order Instance
                return ( new \App\Order() )->convertToApiFormat($orders);
            } else {
                //  Not Found
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getLocationInstantCarts(Request $request, $location_id)
    {
        try {
            $limit = $request->input('limit');

            //  Get the location
            $location = \App\Location::where('id', $location_id)->first() ?? null;

            //  Get the location instant carts
            $instant_carts = $location->instantCarts()->paginate($limit) ?? null;

            //  Check if the location instant carts exist
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

    public function getLocationPaymentMethods(Request $request, $location_id)
    {
        try {
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
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function deleteLocation(Request $request, $location_id)
    {
        try {
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
                return help_resource_not_found();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }
}
