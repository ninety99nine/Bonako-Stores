<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Http\Resources\Location as LocationResource;
use App\Http\Resources\Locations as LocationsResource;

trait LocationTraits
{
    public $location = null;

    /**
     *  This method transforms a collection or single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            // If this instance is a collection or a paginated collection
            if( $collection instanceof \Illuminate\Support\Collection ||
                $collection instanceof \Illuminate\Pagination\LengthAwarePaginator ){

                //  Transform the multiple instances
                return new LocationsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Location){

                //  Transform the single instance
                return new LocationResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new location
     */
    public function createResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the store id
            $store_id = $data['store_id'];

            //  Verify permissions
            $this->createResourcePermission($user, $store_id);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  If the current authenticated user is a Super Admin and the "user_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['user_id']) ){

                //  Set the "user_id" provided as the user responsible for owning this resource
                $template['user_id'] = $data['user_id'];

                //  Overide the default user to insert this user for assignUserAsAdmin()
                $user = \App\User::find($data['user_id']);

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['user_id'] = auth('api')->user()->id;

            }

            /**
             *  Create a new resource
             */
            $this->location = $this->create($template);

            //  If created successfully
            if ( $this->location ) {

                //  Generate the resource creation report
                $this->location->generateResourceCreationReport();

                //  Update the supported payment methods
                $this->location->updateSupportedPaymentMethods($data);

                //  Assign user as an Admin to this location
                $this->location->assignUserAsAdmin($user);

                //  Return the location
                return $this->location;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a location creation report
     */
    public function generateResourceCreationReport()
    {
        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceCreationReport($this, ['name' => $this->name], $this->store_id, $this->id);
    }

    /**
     *  This method updates an existing location
     */
    public function updateResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Merge the existing data with the new data
            $data = array_merge(collect($this)->only($this->getFillable())->toArray(), $data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Validate the data
            $this->updateResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  Set the original user as the primary user responsible for creating this resource
            $template['user_id'] = $this->user_id;

            /**
             *  Update the resource details
             */
            $updated = $this->update($template);

            //  If updated successfully
            if ($updated) {

                //  Update the supported payment methods
                $this->updateSupportedPaymentMethods($data);

                //  Return a fresh instance
                return $this->fresh();

            }else{

                //  Return original instance
                return $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of locations
     */
    public function getResources($data = [], $builder = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data (CommanTraits)
            $this->getResourcesValidation($data);

            //  If we already have an eloquent builder defined
            if( is_object($builder) ){

                //  Set the locations to this eloquent builder
                $locations = $builder;

            }else{

                //  Get the locations
                $locations = \App\Location::latest();

            }

            //  Filter the locations by search
            $locations = $this->filterResourcesBySearch($data, $locations);

            //  Return locations
            return $this->collectionResponse($data, $locations, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the locations by search
     */
    public function filterResourcesBySearch($data = [], $locations)
    {
        //  Set the search term e.g "Location 1"
        $search_term = $data['search'] ?? null;

        //  Return searched locations otherwise original locations
        return empty($search_term) ? $locations : $locations->search($search_term);

    }

    /**
     *  This method returns a single location
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $location = \App\Location::where('id', $id)->first() ?? null;

            //  If exists
            if ($location) {

                //  Return location
                return $location;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single location store
     */
    public function getResourceStore()
    {
        try {

            //  Get the resource
            $store = $this->store;

            //  If exists
            if ($store) {

                //  Return store
                return $store;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location totals
     */
    public function getResourceTotals($data = [], $user)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            return [
                'users' => $this->getResourceUserTotals($data),
                'products' => $this->getResourceProductTotals($data),
                'orders' => [
                    'sent' => $this->getResourceOrderTotals(array_merge($data, ['type' => 'sent']), $user),
                    'received' => $this->getResourceOrderTotals(array_merge($data, ['type' => 'received']), $user),
                ],
                'coupons' => $this->getResourceCouponTotals($data),
                'instant_carts' => $this->getResourceInstantCartTotals($data)
            ];

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location user totals
     */
    public function getResourceUserTotals($data = [])
    {
        try {

            //  Get location users
            $users = $this->getResourceUsers($data, null);

            //  Return location user totals
            return (new \App\User())->getResourceTotals($data, $users);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location order totals
     */
    public function getResourceOrderTotals($data = [], $user)
    {
        try {

            //  Get location orders
            $orders = $this->getResourceOrders($data, $user, null);

            //  Return location order totals
            return (new \App\Order())->getResourceTotals($data, $orders);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location coupon totals
     */
    public function getResourceCouponTotals($data = [])
    {
        try {

            //  Get location coupons
            $coupons = $this->getResourceCoupons($data, null);

            //  Return location coupon totals
            return (new \App\Coupon())->getResourceTotals($data, $coupons);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location instant cart totals
     */
    public function getResourceInstantCartTotals($data = [])
    {
        try {

            //  Get location instant carts
            $instant_carts = $this->getResourceInstantCarts($data, null);

            //  Return location instant cart totals
            return (new \App\InstantCart())->getResourceTotals($data, $instant_carts);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns location product totals
     */
    public function getResourceProductTotals($data = [])
    {
        try {

            //  Get location products
            $products = $this->getResourceProducts($data, null);

            //  Return location product totals
            return (new \App\Product())->getResourceTotals($data, $products);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of location users
     */
    public function getResourceUsers($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the users
            $users = $this->users();

            //  Return a list of location users
            return (new \App\User())->getResources($data, $users, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of location orders
     */
    public function getResourceOrders($data = [], $user, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Filter the location orders by type
            $orders = $this->filterResourceOrdersByType($data, $user);

            //  Return a list of location orders
            return (new \App\Order())->getResources($data, $orders, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the user orders by type
     */
    public function filterResourceOrdersByType($data = [], $user)
    {
        //  Extract the Request Object data (CommanTraits)
        $data = $this->extractRequestData($data);

        //  Set the type e.g "received", "shared" or "sent"
        $type = isset($data['type']) ? strtolower($data['type']) : null;

        //  If we want received orders
        if ($type === 'received') {

            //  Scope orders received
            $orders = $this->receivedOrders();

        //  If we want shared orders
        } elseif ($type === 'shared') {

            //  Scope orders shared
            $orders = $this->sharedOrders();

        //  If we want orders sent
        } elseif ($type === 'sent') {

            //  Scope orders received sent by user
            $orders = $this->receivedOrders()->userIsCustomer($user);

        }else{

            //  Scope orders
            $orders = $this->orders();

        }

        //  Return the orders
        return $orders;
    }

    /**
     *  This method returns a list of location coupons
     */
    public function getResourceCoupons($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the coupons
            $coupons = $this->coupons();

            //  Return a list of location coupons
            return (new \App\Coupon())->getResources($data, $coupons, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of location products
     */
    public function getResourceProducts($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the products
            $products = $this->products();

            //  Return a list of location products
            return (new \App\Product())->getResources($data, $products, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of location instant carts
     */
    public function getResourceInstantCarts($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the instant carts
            $instant_carts = $this->instantCarts();

            //  Return a list of location instant carts
            return (new \App\InstantCart())->getResources($data, $instant_carts, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method updates the arrangement of products in the current location
     */
    public function arrangeResourceProducts($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the product arrangements
            $product_arrangements = $data['product_arrangements'] ?? [];

            //  Get the products assigned to this location (Products must not be variations)
            $products = collect( $this->products()->get() )->toArray();

            //  Set the product arrangement in correct assending order (Remove invalid arrangements)
            $product_arrangements =
                collect($product_arrangements)->filter(function ($product) {

                    //  Must have an id
                    return isset($product['id']) && !empty($product['id']) &&

                        //  Must have an arrangement
                        isset($product['arrangement']) && !empty($product['arrangement']);

                //  Order by arrangement
                })->sortBy('arrangement');

            //  Foreach product we must arrange
            foreach ($product_arrangements as $key => $product_arrangement) {

                //  Set the id (i.e Target)
                $id = $product_arrangement['id'] ?? null;

                //  Set the arrangement (i.e Position)
                $arrangement = $product_arrangement['arrangement'] ?? null;

                //  Foreach location product
                foreach($products as $key => $product){

                    //  If the location product matches the product we must arrange
                    if( $product['id'] === $id ){

                        //  Remove the location product from its current arrangement
                        unset($products[$key]);

                        //  Add the location product to its new arrangement
                        array_splice($products, $arrangement - 1, 0, [$product]);

                    }

                }

            }

            //  Set the arrangements
            $arrangements = [];

            //  Foreach of the location products
            foreach ($products as $key => $product) {

                //  Set the location product arrangement
                $arrangement = ($key + 1);

                //  Convert the product to an Object
                $product = (Object) $product;

                //  Set the location product as the next product arrangement
                $arrangement = (new \App\Product())->getResourceArrangementTemplate($product, $this, $arrangement);

                //  Add this arrangement to the list of arrangements
                array_push($arrangements, $arrangement);

            }

            //  If we have any arrangements
            if( count($arrangements) ){

                //  Delete previously assigned location products
                DB::table('product_allocations')->where('owner_id', $this->id)->where('owner_type', $this->resource_type)->delete();

                //  Assign products to locations
                DB::table('product_allocations')->insert($arrangements);

            }

            //  Return a list of location products
            return $this->getResourceProducts($data);

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method returns a true/false status if the given user marked as favourite
     */
    public function getResourceFavouriteStatus($user = null)
    {
        //  Retrieve the User ID
        $user_id = ($user instanceof \App\User) ? $user->id : auth('api')->user()->id;

        //  Set the search
        $search = [
            'user_id' => $user_id,
            'location_id' => $this->id
        ];

        //  Check if the user already marked this location as a favourite
        $status = DB::table('favourites')->where($search)->exists();

        //  Return the status
        return ['status' => $status];

    }

    /**
     *  This method adds or removes this location as a favourite for given user
     */
    public function toggleResourceAsFavourite($user = null)
    {
        //  Retrieve the User ID
        $user_id = ($user instanceof \App\User) ? $user->id : auth('api')->user()->id;

        //  Set the search
        $search = [
            'user_id' => $user_id,
            'location_id' => $this->id
        ];

        //  Check if the user already marked this location as a favourite
        $exists = DB::table('favourites')->where($search)->exists();

        //  If this location is marked as a favourite
        if ( $exists ) {

            //  Unmark location as favourite
            DB::table('favourites')->where($search)->delete();

        //  If this location is not marked as a favourite
        } else {

            //  Set the record
            $record = [
                'user_id' => $user_id,
                'location_id' => $this->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            //  Mark location as favourite
            DB::table('favourites')->insert($record);

        }

    }

    /**
     *  This method deletes a location
     */
    public function deleteResource($user = null)
    {
        try {

            //  Verify permissions
            $this->forceDeleteResourcePermission($user);

            /**
             *  Delete the resource
             */
            return $this->delete();


        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method assigns a user as an admin to the location
     */
    public function assignUserAsAdmin($user = null)
    {
        try {

            //  Add the user as an Admin to the current location
            return $this->assignUserRole('admin', $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method updates the supported payment methods
     */
    public function updateSupportedPaymentMethods($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we want to update the online or offline supported payment methods
            if( isset($data['online_payment_methods']) || isset($data['offline_payment_methods']) ){

                //  Set the supported online payment method ids
                $online_payment_method_ids = $data['online_payment_methods'] ?? [];

                //  Set the supported offline payment method ids
                $offline_payment_method_ids = $data['offline_payment_methods'] ?? [];

                //  Set the payment method ids by merging the online and offline supported payment method ids
                $payment_method_ids = array_merge($online_payment_method_ids, $offline_payment_method_ids);

                $location_payment_methods = collect($payment_method_ids)->map(function($id) use ($online_payment_method_ids, $offline_payment_method_ids){

                    //  Check if this payment method is used online
                    $used_online = collect($online_payment_method_ids)->contains(function ($payment_method_id) use ($id) {

                        //	Check if we have matching ids
                        return ($payment_method_id == $id);

                    });

                    //  Check if this payment method is used offline
                    $used_offline = collect($offline_payment_method_ids)->contains(function ($payment_method_id) use ($id) {

                        //	Check if we have matching ids
                        return ($payment_method_id == $id);

                    });

                    //  Set the record of assigning a supported payment method to a location
                    $record = [
                        'location_id' => $this->id,
                        'payment_method_id' => $id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'used_online' => $used_online,
                        'used_offline' => $used_offline,
                    ];

                    //  Return the record
                    return $record;

                })->unique('payment_method_id')->toArray();

                //  Delete previous records of supported payment methods
                DB::table('location_payment_methods')->where('location_id', $this->id)->delete();

                //  Insert supported payment methods
                DB::table('location_payment_methods')->insert($location_payment_methods);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method assigns a user with a role to the location
     */
    public function assignUserRole($type, $user = null)
    {
        try {

            //  Retrieve the User ID
            $user_id = ($user instanceof \App\User) ? $user->id : auth('api')->user()->id;

           if ( !empty($user_id) ) {

                //  Set the record of user to location assignment
                $record = [
                    'type' => $type,
                    'user_id' => $user_id,
                    'location_id' => $this->id,
                    'default_location' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                //  Remove previous user and role assignments
                DB::table('location_user')->where(['user_id' => $user_id , 'location_id' => $this->id])->delete();

                //  Add the user as an Admin to the current location
                DB::table('location_user')->insert($record);

           }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the location rating statistics
     */
    public function getResourceRatingStatistics()
    {
        try {

            //  Get the latest 500 ratings of this location
            $ratings = $this->ratings()->latest()->take(500)->get();

            $total_ratings = count($ratings);
            $lowest_rating = collect($ratings)->min('value');
            $highest_rating = collect($ratings)->max('value');

            //  Get the most recurring rating e.g "4"
            $rating_mode = collect($ratings)->mode('value') ?? [];

            //  Get the average rating e.g "4.666666666666667"
            $average_rating = $total_ratings ? collect($ratings)->sum('value') / $total_ratings : null;

            return [

                'rating_mode' => count($rating_mode) ? $rating_mode[0] : null,
                'highest_rating' => $highest_rating,
                'lowest_rating' => $lowest_rating,
                'total_ratings' => $total_ratings,
                'average_rating' => [

                    //  Average for system e.g "4.666666666666667"
                    'actual' => $average_rating,

                    //  Average for merchants e.g "4.67"
                    'seller' => !is_null($average_rating) ? round($average_rating, 2) : null,

                    //  Average for customers e.g "4.7"
                    'buyer' => !is_null($average_rating) ? round($average_rating, 1) : null

                ]

            ];

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    /**
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null, $store_id = null)
    {
        try {

            //  If the user is provided
            if( $user && $store_id ){

                /**
                 *  Check if the user is authourized to create the store resource.
                 *  NOTE: To create a new location, the user must be the Owner
                 *  of the store for which the location is being created.
                 */
                return (new \App\Store())->getResource($store_id)->updateResourcePermission($user);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for updating an existing resource
     */
    public function updateResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to update the resource
                if ($user->can('update', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for deleting an existing resource
     */
    public function forceDeleteResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to delete the resource
                if ($user->can('forceDelete', $this)) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates creating a new resource
     */
    public function createResourceValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [
                'name' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\s]+$/i',
                'currency' => 'sometimes|required|string|regex:/^[a-zA-Z]+$/i',
                'minimum_stock_quantity' => 'sometimes|required|numeric|min:1',
            ];

            //  Set validation messages
            $messages = [
                'name.required' => 'The location name is required e.g Gaborone',
                'name.string' => 'The location name must be a valid string e.g Gaborone',
                'name.regex' => 'The location name must contain only letters, numbers and spaces e.g Gaborone',
                'name.min' => 'The location name must be atleast 3 characters long',
                'name.max' => 'The location name must not be more than 50 characters long',

                'currency.required' => 'Enter a valid currency e.g BWP, ZAR, USD',
                'currency.string' => 'Enter a valid currency e.g BWP, ZAR, USD',
                'currency.regex' => 'The currency must be a valid ISO 4217 standard e.g BWP, ZAR, USD',

                'minimum_stock_quantity.required' => 'The minimum stock quantity must be a valid number greater than 0 e.g 10',
                'minimum_stock_quantity.numeric' => 'The minimum stock quantity must be a valid number greater than 0 e.g 10',
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method validates updating an existing resource
     */
    public function updateResourceValidation($data = [])
    {
        try {

            //  Run the resource creation validation
            $this->createResourceValidation($data);

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method verifies if the user is the owner of the location
     */
   public function isOwner($resource = null)
   {
       try {

            //  Retrieve the User ID
            $user_id = ($resource instanceof \App\User) ? $resource->id : $resource;

            //  Check if this is the owner
            return ( !empty($user_id) ) ? $this->whereUserId($user_id)->exists() : false;

       } catch (\Exception $e) {

           throw($e);

       }
   }

    /**
     *  This method verifies if the user is the admin of the location
     */
   public function isAdmin($resource = null)
   {
       try{

            //  Retrieve the User ID
            $user_id = ($resource instanceof \App\User) ? $resource->id : $resource;

            //  Check if the user is an admin
            return ( !empty($user_id) ) ? $this->users()->wherePivot('user_id', $user_id)->wherePivot('type', 'admin')->exists() : false;

       } catch (\Exception $e) {

           throw($e);

       }
   }

}
