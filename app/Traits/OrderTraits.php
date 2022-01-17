<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Order as OrderResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Orders as OrdersResource;

trait OrderTraits
{
    public $order = null;
    public $_store = null;
    public $_location = null;
    public $new_order_merchant_sms = null;
    public $new_order_merchant_notification = null;

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
                return new OrdersResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Order){

                //  Transform the single instance
                return new OrderResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new order
     */
    public function createResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Verify permissions
            $this->createResourcePermission($user);

            //  Get the cart
            $cart = (new \App\Cart())->getResource($data['cart_id']);

            //  If the cart was found
            if( $cart ){

                //  Set the template
                $template = $data;

                /**
                 *  Create a new resource
                 */
                $this->order = $this->create($template)->fresh();

                //  If created successfully
                if ($this->order) {

                    //  Set the order number
                    $this->order->generateResourceNumber();

                    //  Set the order as the cart owner
                    $cart->setResourceOwner($this->order);

                    //  Generate the resource cart converted report
                    $cart->generateResourceConvertedReport();

                    //  If the cart has an instant cart id
                    if( $cart->instant_cart_id ){

                        //  Update the remaining instant cart stock quantity
                        $cart->updateRemainingInstantCartStockQuantity();

                    }else{

                        //  Update the remaining product stock quantity
                        $cart->updateRemainingProductStockQuantity();

                    }

                    /****************************
                     *  SET DELIVERY STATUS     *
                     * *************************/

                    //  Update the order status as "Undelivered"
                    $this->order->setDeliveryStatusToUndelivered();

                    //  Assign order to location
                    $this->order->assignResourceToLocation([
                        'location_id' => $cart->location_id
                    ]);

                    //  Refresh the instance to load the delivery line and active cart
                    $this->order = $this->order->fresh();

                    $this->order->createOrUpdateResourceCustomer($cart, $user);

                    //  Create a new delivery line resource
                    $this->order->createResourceDeliveryLine($data);

                    //  Generate the resource creation report
                    $this->order->generateResourceCreationReport();

                    /****************************
                     *  SET PAYMENT STATUS      *
                     * *************************/
                    if( isset($data['is_paid']) && $data['is_paid'] === true ){

                        //  Update the order status as "Paid"
                        $this->order->setPaymentStatusToPaid();

                    }else{

                        //  Update the order status as "Unpaid"
                        $this->order->setPaymentStatusToUnpaid();

                    }

                    //  Craft the merchant sms and notification
                    $this->order->craftNewOrderMerchantMessages($user);

                    /*************************************************
                     *  SEND FIREBASE CLOUD MESSAGING NOTIFICATIONS  *
                     * ***********************************************/

                    //  Send the new order merchant sms
                    $this->order->sendNewOrderMerchantMobileAppNotification($user);

                    /*************************************
                     *  SEND DELIVERY CONFIRMATION CODE  *
                     * **********************************/

                    //  Send the order delivery confirmation code sms
                    $this->order->sendDeliveryConfirmationCodeSms($user);

                    //  Send the new order merchant sms
                    $this->order->sendNewOrderMerchantSms($user);

                    //  Return a fresh instance
                    return $this->order->fresh();
                }

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method creates or updates the location customer
     */
    public function createOrUpdateResourceCustomer($cart = null, $user = null, $hasVerifiedDelivery = false)
    {
        try{

            //  If the cart was not provided
            if( $cart == null ){

                //  Get the order active cart
                $cart = $this->activeCart;

            }

            $received_location = $this->receivedLocations()->first();

            $data = [
                'user_id' => $user->id,
                'location_id' => $received_location->id,
            ];

            /*********************
             *  CALCULATIONS     *
             ********************/

            $total_coupons_used = $cart->total_coupons;
            $total_instant_carts_used = isset($cart->instant_cart_id) ? 1 : 0;
            //  $total_adverts_used = isset($cart->advert_id) ? 1 : 0;
            $total_orders_placed_by_customer = $this->submitted_by_store_user['status'] ? 0 : 1;
            $total_orders_placed_by_store = $this->submitted_by_store_user['status'] ? 1 : 0;
            $total_free_delivery_on_conversion = $cart->allow_free_delivery['status'] ? 1 : 0;

            $grand_total = $cart->grand_total['amount'];
            $sub_total = $cart->sub_total['amount'];
            $sale_discount_total = $cart->sale_discount_total['amount'];
            $coupon_total = $cart->coupon_total['amount'];
            $coupon_and_sale_discount_total = $cart->coupon_and_sale_discount_total['amount'];
            $delivery_fee = $cart->delivery_fee['amount'];
            $total_items = $cart->total_items;
            $total_unique_items = $cart->total_unique_items;

            if( $hasVerifiedDelivery ){

                //  Add the conversion totals
                $data = array_merge(
                    $data, [
                        'total_coupons_used_on_conversion' => $total_coupons_used,
                        'total_instant_carts_used_on_conversion' => $total_instant_carts_used,
                        //  'total_adverts_used_on_conversion' => $total_adverts_used
                        'total_orders_placed_by_customer_on_conversion' => $total_orders_placed_by_customer,
                        'total_orders_placed_by_store_on_conversion' => $total_orders_placed_by_store,
                        'total_free_delivery_on_conversion' => $total_free_delivery_on_conversion,

                        'grand_total_on_conversion' => $grand_total,
                        'sub_total_on_conversion' => $sub_total,
                        'sale_discount_total_on_conversion' => $sale_discount_total,
                        'coupon_total_on_conversion' => $coupon_total,
                        'coupon_and_sale_discount_total_on_conversion' => $coupon_and_sale_discount_total,
                        'delivery_fee_on_conversion' => $delivery_fee,
                        'total_items_on_conversion' => $total_items,
                        'total_unique_items_on_conversion' => $total_unique_items
                    ]
                );

            }else{

                //  Add the conversion totals
                $data = array_merge(
                    $data, [
                        'total_coupons_used_on_checkout' => $total_coupons_used,
                        'total_instant_carts_used_on_checkout' => $total_instant_carts_used,
                        //  'total_adverts_used_on_checkout' => $total_adverts_used
                        'total_orders_placed_by_customer_on_checkout' => $total_orders_placed_by_customer,
                        'total_orders_placed_by_store_on_checkout' => $total_orders_placed_by_store,
                        'total_free_delivery_on_checkout' => $total_free_delivery_on_conversion,

                        'grand_total_on_checkout' => $grand_total,
                        'sub_total_on_checkout' => $sub_total,
                        'sale_discount_total_on_checkout' => $sale_discount_total,
                        'coupon_total_on_checkout' => $coupon_total,
                        'coupon_and_sale_discount_total_on_checkout' => $coupon_and_sale_discount_total,
                        'delivery_fee_on_checkout' => $delivery_fee,
                        'total_items_on_checkout' => $total_items,
                        'total_unique_items_on_checkout' => $total_unique_items
                    ]
                );

            }

            //  Create / Update the location customer
            $customer = ( new \App\Customer() )->createResource($data, $user);

            //  Assign the customer to this order
            $this->update([
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a order creation report
     */
    public function generateResourceCreationReport()
    {
        //  Get the store with locations holding this order
        $store = \App\Store::with('locations')->whereHas('locations', function (Builder $query) {
            $query->whereHas('orders', function (Builder $query) {
                $query->where('orders.id', $this->id);
            });
        })->first();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource creation report
            ( new \App\Report() )->generateResourceCreationReport($this, $this->resourceReportMetadata(), $store->id, $location->id);

        }
    }

    /**
     *  This method generates a cart creation report
     */
    public function resourceReportMetadata($additionalMetadata = [])
    {
        $defaultMetadata = [
            'status_id' => $this->status_id,
            'payment_status_id' => $this->payment_status_id,
            'delivery_status_id' => $this->delivery_status_id,
            'delivery_verified' => $this->delivery_verified,
            'cart' => $this->activeCart->resourceReportMetadata()
        ];

        return array_merge($defaultMetadata, $additionalMetadata);
    }

    /**
     *  This method returns a list of orders
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

                //  Set the orders to this eloquent builder
                $orders = $builder;

            }else{

                //  Get the orders
                $orders = \App\Order::latest();

            }

            //  Filter the orders
            $orders = $this->filterResources($data, $orders);

            //  Return orders
            return $this->collectionResponse($data, $orders, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of order totals
     *
     *  Note: $builder is an instance of the eloquent builder. In this
     *  case the eloquent builder must represent an instance of orders
     */
    public function getResourceTotals($data = [], $builder)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the totals
            $totals = [
                'statuses' => [],
                'total' => $builder->count()
            ];

            //  Set the status filters to calculate the totals
            $filters = [
                'open', 'draft', 'archieved', 'cancelled',
                'paid', 'unpaid', 'refunded', 'failed',
                'delivered', 'undelivered'
            ];

            collect($filters)->map(function($filter) use (&$totals, $builder){

                /**
                 *  $filter = 'open' or 'draft' or 'archieved' ... e.t.c
                 *
                 *  $bulder = Eloquent Builder Instance e.g $location->orders()->latest()
                 *
                 *  We clone the builder object to have a new instance to use when filtering the orders.
                 *  If we do not clone, only one object instance will be used for every filter producing
                 *  incorrect results e.g The instance may be used to filter only orders with a status
                 *  of "paid" and return a few results. The same builder will then be used to filter
                 *  orders with a status of "delivered", however since we are using the same instance
                 *  it would have applied the previous filter of "paid", which means that the final
                 *  orders returned will need to be "paid" and "delivered". This gets worse as we
                 *  load more filters e.g It will look to return orders that must match every
                 *  status i.e "open", "draft", "archieved", "cancelled", e.t.c
                 */
                $totals['statuses'][$filter] = $this->filterResourcesByStatus($filter, clone $builder)->count();

            })->toArray();

            /**
             *  Return the totals
             *
             *  Example result
             *
             *  [
             *    "statuses" => [
             *       "open" => 1,
             *       "draft" => 0,
             *       "archieved" => 0,
             *       "cancelled" => 0,
             *
             *       "paid" => 0,
             *       "unpaid" => 1,
             *
             *       "refunded" => 0,
             *       "failed" => 0,
             *       "delivered" => 0,
             *       "undelivered" => 1
             *      ]

             *  ]
             */
            return $totals;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the orders by search or status
     */
    public function filterResources($data = [], $orders)
    {
        //  Filter by customer id before we can filter by search and status
        if ( isset($data['customer_id']) && !empty($data['customer_id']) ) {

            $orders = $this->filterResourcesByCustomerId($data, $orders);

        }

        //  If we need to search for specific orders
        if ( isset($data['search']) && !empty($data['search']) ) {

            $orders = $this->filterResourcesBySearch($data, $orders);

        }else{

            if ( isset($data['status']) && !empty($data['status']) ) {

                $orders = $this->filterResourcesByStatus($data, $orders);

            }

            $orders = $this->filterResourcesByRequireRating($data, $orders);

        }

        //  Return the orders
        return $orders;
    }

    /**
     *  This method filters the orders by search
     */
    public function filterResourcesBySearch($data = [], $orders)
    {
        //  Set the search term e.g "00123"
        $search_term = $data['search'] ?? null;

        //  Return searched orders otherwise original orders
        return empty($search_term) ? $orders : $orders->search($search_term);

    }

    /**
     *  This method filters the orders by customer id
     */
    public function filterResourcesByCustomerId($data = [], $orders)
    {
        //  Set the customer id
        $customer_id = $data['customer_id'] ?? null;

        //  Return orders that match the customer id
        return $orders->where('customer_id', $customer_id);

    }

    /**
     *  This method filters the orders by status
     */
    public function filterResourcesByStatus($data = [], $orders)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["open", "paid", "delivered", ...] or "open,paid,delivered, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "open,paid,delivered"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["open", "paid", "delivered"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["open", "paid", "delivered"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " unpaid " to "Unpaid"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $orders && count($statuses) ) {

            $general_statuses = collect($statuses)->filter(function ($value) {
                return ($value == 'Open' || $value == 'Draft' || $value == 'Archieved' || $value == 'Cancelled');
            });

            $payment_statuses = collect($statuses)->filter(function ($value) {
                return ($value == 'Paid' || $value == 'Unpaid' || $value == 'Refunded' || $value == 'Failed');
            });

            $delivery_statuses = collect($statuses)->filter(function ($value) {
                return ($value == 'Delivered' || $value == 'Undelivered');
            });

            if( count($general_statuses) ){
                $orders = $orders->whereHas('status', function (Builder $query) use ($general_statuses){
                    $query->whereIn('name', $general_statuses);
                });
            }

            if( count($payment_statuses) ){
                $orders = $orders->whereHas('paymentStatus', function (Builder $query) use ($payment_statuses){
                    $query->whereIn('name', $payment_statuses);
                });
            }

            if( count($delivery_statuses) ){
                $orders = $orders->whereHas('deliveryStatus', function (Builder $query) use ($delivery_statuses){
                    $query->whereIn('name', $delivery_statuses);
                });
            }

        }

        //  Return the orders
        return $orders;
    }

    /**
     *  This method filters the orders by those that require rating
     */
    public function filterResourcesByRequireRating($data = [], $orders)
    {
        if( isset($data['require_rating']) && !is_null($data['require_rating']) ) {

            //  Set the require_rating
            $require_rating = $data['require_rating'];

            //  If the require_rating is set to "true" or "1"
            if( in_array($require_rating, [true, 1, '1']) ){

                $orders = $orders->requireRating();

            }

        }

        //  Return the orders
        return $orders;
    }

    /**
     *  This method assigns the order to one or many locations
     */
    public function assignResourceToLocation($data, $is_shared = false)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the location id
            $location_id = $data['location_id'] ?? null;

            //  Set the location ids (If we want to bulk assign)
            $location_ids = $data['location_ids'] ?? null;

            //  Set the records
            $records = [];

            //  If we have a location id
            if( $location_id ){

                //  Set the record of order to location assignment
                $record = [
                    'order_id' => $this->id,
                    'is_shared' => $is_shared,
                    'location_id' => $location_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                //  Add the record
                array_push($records, $record);

            //  If we have locations
            }elseif($location_ids){

                //  Foreach location
                foreach($location_ids as $location_id){

                    //  If we have a location id
                    if( $location_id ){

                        //  Set the record of order to location assignment
                        $record = [
                            'order_id' => $this->id,
                            'is_shared' => $is_shared,
                            'location_id' => $location_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];

                        //  Add the record
                        array_push($records, $record);

                    }

                }

            }

            //  Delete previous shared locations
            DB::table('location_order')->where(['order_id' => $this->id, 'is_shared' => 1])->delete();

            //  If we have any records
            if( count($records) ){

                //  Assign order to location
                DB::table('location_order')->insert($records);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single order
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $order = \App\Order::where('id', $id)->first() ?? null;

            //  If exists
            if ($order) {

                //  Return order
                return $order;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the order received location
     */
    public function getResourceReceivedLocation()
    {
        try {

            //  Get the resource received location
            $location = $this->receivedLocations()->first();

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
     *  This method returns the order shared location
     */
    public function getResourceSharedLocations($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the shared location
            $locations = $this->sharedLocations();

            //  Return a list of order shared locations
            return (new \App\Location())->getResources($data, $locations, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the order store based on the received order
     */
    public function getResourceStore()
    {
        try {

            //  Get the resource received location store
            $store = $this->getResourceReceivedLocation()->store;

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
     *  This method updates the order shared locations
     */
    public function updateResourceSharedLocations($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have the location ids
            if( isset($data['location_ids']) ){

                //  Assign order to location (Set True to assign as shared resource)
                $this->assignResourceToLocation($data, true);

            }

            //  Return the order shared locations
            return $this->getResourceSharedLocations($data = [], $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a new order number
     */
    public function generateResourceNumber()
    {
        try {

            /*  Generate a unique order number.
            *  Get the order id, and Pad the left side with leading "0"
            *  e.g 123 = 00123, 1234 = 01234, 12345 = 12345
            */
            $order_number = str_pad($this->id, 5, 0, STR_PAD_LEFT);

            //  Set the unique order number
            $this->update(['number' => $order_number]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new order delivery line
     */
    public function createResourceDeliveryLine($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have delivery information
            if( isset($data['delivery']) ){

                //  Overide data with delivery data
                $data = $data['delivery'];

                //  If we have an address id provided
                if( isset($data['address']['id']) && !empty($data['address']['id']) ){

                    //  Get the first matching address
                    $address = $this->customer->addresses()->where('id', $data['address']['id'])->first();

                }else{

                    $address = null;

                }

                //  If the customer has a matching address resource
                if( $address ){

                    //  If we have any address related data
                    if( isset($data['address']['name']) || isset($data['address']['mobile_number']) ||
                        isset($data['address']['physical_address']) || isset($data['address']['type']) ){

                        //  Update the existing customer address resource
                        $address = $address->updateResource($data['address']);

                    }

                //  If the customer does not have any address resource
                }else{

                    //  Create a new customer address resource
                    $address = $this->order->customer->createResourceAddress($data['address']);

                }

                //  Merge the data with additional fields
                $data = array_merge($data, [

                    //  Set the address name on the data
                    'name' => $address->name,

                    //  Set the address mobile number on the data
                    'mobile_number' => $address->mobile_number,

                    //  Set the physical address on the data
                    'physical_address' => $address->physical_address,

                    //  Set the address type id on the data
                    'address_type_id' => $address->address_type_id,

                    //  Set the address id on the data
                    'address_id' => $address->id,

                    //  Set the order id on the data
                    'order_id' => $this->id

                ]);

                /**
                 *  Create new a delivery line resource
                 */
                return ( new \App\DeliveryLine() )->createResource($data);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function sendNewOrderMerchantMobileAppNotification() {

        //  Get the order locations
        $locations = $this->locations;

        //  Get the order location users
        $users = $this->users()->with('permissions')->get();

        //  If we have atleast one location and one user
        if( count($locations) && count($users) ){

            /**
             *  Extract the firebase device tokens of users that have the permissions to manage this order
             *
             *  RESULT:
             *
             *  [
             *      'fxOBPbwQTG2...',
             *      'nuiBopeNZP5...',
             *      'rvYYPUA4t2Q...'
             *  ]
             */
            $firebase_device_tokens = collect($users)->filter(function($user) use ($locations){

                /**
                 * $user->permissions = [
                 *      [
                 *          "id" => 1,
                 *          "name" => "locations.1.manage-orders,manage-coupons,manage-products,manage-customers,manage-instant-carts,manage-users,manage-reports,manage-settings",
                 *          "guard_name" => "web",
                 *          "created_at" => "2022-01-06T05:23:48.000000Z",
                 *          "updated_at" => "2022-01-06T05:23:48.000000Z",
                 *          "pivot" => [
                 *              "model_id" => 1,
                 *              "permission_id" => 1,
                 *              "model_type" => "user"
                 *          ]
                 *      ]
                 *  ]
                 *
                 *  Return users that have the permission to manage this order
                 */
                return collect($user->permissions)->contains(function($permission) use($locations) {

                    //  By default the user does not have the permissions to manage this order
                    $hasPermissions = false;

                    //  Check if this user has the permission to manage this order in any of the assigned locations
                    foreach ($locations as $location) {

                        $hasPermissions = Str::containsAll($permission->name, ['locations.'.$location->id]);

                    }

                    return $hasPermissions;
                });

            //  Extract the firebase device tokens from every user and flatten the results
            })->pluck('firebase_device_tokens')->flatten()->values()->toArray();

            //  If we have atleast 1 token
            if( count($firebase_device_tokens) ){

                //  Send the new order notification to the merchant
                $this->sendFirebaseCloudMessagingNotification($firebase_device_tokens, [
                    'notification' => [
                        'title' => 'New Order',
                        'body' => $this->new_order_merchant_notification,
                        'android_channel_id' => 'high_importance_channel',
                        'icon' => 'https://img.icons8.com/external-tal-revivo-color-tal-revivo/50/000000/external-web-hyperlink-with-url-for-navigating-to-new-page-text-color-tal-revivo.png'
                    ],
                ]);

            }

        }

    }

    /**
     *  This method sends the delivery confirmation code message to the customer
     */
    public function sendDeliveryConfirmationCodeSms($user = null)
    {
        try {

            /*******************************************
             * GENERATE THE DELIVERY CONFIRMATION CODE *
             *******************************************/

            $customer = $this->customer;

            //  Set the delivery reference name
            $customer_name = $customer->user->first_name;

            //  Set the delivery reference mobile number
            $customer_mobile_number = $customer->user->mobile_number;

            //  Get the existing delivery confirmation codes for orders placed by this customer
            $existing_confirmation_codes = $customer->orders()->whereNotNull('delivery_confirmation_code')->pluck('delivery_confirmation_code');

            //  Extract the first 4 digits and last 4 digits of the delivery confirmation codes
            $existing_confirmation_codes = collect($existing_confirmation_codes)->map(function($existing_confirmation_code){

                $first_4_characters = substr($existing_confirmation_code, 0, 4);

                $last_4_characters = substr($existing_confirmation_code, -4);

                return $first_4_characters . $last_4_characters;

            })->toArray();

            //  By default we assume that the delivery confirmation code exists
            $code_exists = true;

            while($code_exists == true) {

                //  Generate a random number
                $random_number = mt_rand(1, 99999999);

                /**
                 *  Get the random number, and Pad the left side with leading "0" so that we have a
                 *  total of eight digits e.g
                 *
                 *  123 = 00000123, 1234 = 00001234, 12345 = 00012345, 123456 = 00123456,
                 *  1234567 = 01234567, 12345678 = 12345678
                 */
                $random_eight_digit_number = str_pad($random_number, 8, 0, STR_PAD_LEFT);

                $code_exists = collect($existing_confirmation_codes)->contains($random_eight_digit_number);

            }

            //  Get the first 4 digits e.g 2447
            $first_4_characters = substr($random_eight_digit_number, 0, 4);

            //  Get the last 4 digits e.g 3795
            $last_4_characters = substr($random_eight_digit_number, -4);

            //  Merge the random digits with the customer mobile number e.g 2447728822393795
            $delivery_confirmation_code = $first_4_characters . $customer_mobile_number['number'] . $last_4_characters;

            //  Split the delivery code with dashes after every 4 characters
            $dashed_delivery_confirmation_code = join('-', str_split($delivery_confirmation_code, 4));

            //  Encrypt the delivery confirmation code
            //  $hashed_delivery_confirmation_code = bcrypt($delivery_confirmation_code);

            //  Set the delivery confirmation code
            $this->update(['delivery_confirmation_code' => $delivery_confirmation_code]);

            //  Craft the sms message
            $message =  trim('Hi '.$customer_name).', your delivery confirmation code '.
                       'for order #'.$this->number.' is ' .$dashed_delivery_confirmation_code.'. '.
                       'Share this code with your merchant only after you receive your order.';

            $type = 'Order delivery confirmation code';

            $data = [

                //  Set the type on the data
                'type' => $type,

                //  Set the message on the data
                'message' => $message,

                //  Set the mobile_number on the data
                'mobile_number' => $customer_mobile_number['number_with_code']

            ];

            //  Set the sms owning model
            $model = $this;

            //  Create a new sms and send
            return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the new order message to the merchant
     */
    public function craftNewOrderMerchantMessages($user = null)
    {
        try {

            //  Set the location that received this order
            $this->_location = $this->receivedLocations()->first();

            if( $this->_location ){

                //  Set the store that the location belongs to
                $this->_store = $this->_location->store;

                //  If this store supports sending merchant sms
                if( $this->_store->allow_sending_merchant_sms ){

                    //  Set the customer name otherwise to the billing name
                    $customer_name = $this->customer->user->first_name;

                    //  Set the customer mobile number otherwise to the billing mobile number
                    $customer_mobile_number = $this->customer->user->mobile_number['number'];

                    //  Set the main short code
                    $main_short_code = '*'.config('app.MAIN_SHORT_CODE').'#';

                    //  Set the main short code
                    $website_domain = env('MAIN_WEBSITE_DOMAIN');

                    //  Set the grand total
                    $grand_total = $this->activeCart->grand_total['currency_money'];

                    //  Craft the notification message
                    $this->new_order_merchant_notification = '0rder #'.$this->number.' received for '.$this->_store->name.' '.
                        'amount '.$grand_total.' from '. $customer_name. ' ('.$customer_mobile_number.')';

                    //  Craft the sms message
                    $this->new_order_merchant_sms = 'Order #'.$this->number.' received for '.$this->_store->name.' '.
                               'amount '.$grand_total.' from '. $customer_name. ' ('.$customer_mobile_number.'). '.
                               'Dial '.$main_short_code.' or download Bonako Dial2buy App.';

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the new order message to the merchant
     */
    public function sendNewOrderMerchantSms($user = null)
    {
        try {

            //  If this store supports sending merchant sms
            if( $this->_store->allow_sending_merchant_sms ){

                //  Set the store owner mobile number as the merchant mobile number
                $merchant_mobile_number = $this->_store->owner->mobile_number['number_with_code'];

                $type = 'New order alert';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $this->new_order_merchant_sms,

                    //  Set the mobile_number on the data
                    'mobile_number' => $merchant_mobile_number

                ];

                //  Set the sms owning model
                $model = $this;

                //  Create a new sms and send
                return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the new order message to the merchant
     */
    public function sendPaymentRequestSms($transaction = null, $user = null)
    {
        try {

            if( $transaction ){

                //  Get the order store
                $store = $this->getResourceStore();

                //  Set the order payment short code
                $payment_short_code = $transaction->paymentShortCode;

                //  Set the transaction payer name
                $payer = $transaction->payer;

                //  Set the order customer name
                $customer = $this->customer;

                //  If we have the store, payment short code, payer and customer information
                if( $store && $payment_short_code && $payer && $customer){

                    //  Set the transaction payer information
                    $payerId = $payer->id;
                    $payerName = $payer->first_name;
                    $payerMobileNumber = $payer->mobile_number['number_with_code'];

                    //  Set the order customer information
                    $customerId = $customer->id;
                    $customerName = $customer->first_name;

                    //  Craft the sms message
                    $message = $store->name.': Hi '.$payerName.', dial '. $payment_short_code->dialing_code.
                               ' to pay for order #'.$this->number . ($payerId == $customerId ? '' : ' placed by '+$customerName).
                               '. Expires after 24hrs.';

                    $type = 'Order payment request';

                    $data = [

                        //  Set the type on the data
                        'type' => $type,

                        //  Set the message on the data
                        'message' => $message,

                        //  Set the mobile_number on the data
                        'mobile_number' => $payerMobileNumber

                    ];

                    //  Set the sms owning model
                    $model = $this;

                    //  Create a new sms and send
                    return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sends the payment success sms to the customer
     */
    public function sendPaymentConfirmationSms($user = null)
    {
        try {

            //  Get the order store
            $store = $this->getResourceStore();

            //  Get the order customer
            $customer = $this->customer;

            //  Get the order active cart
            $active_cart = $this->activeCart;

            //  If we have the store, customer and active cart
            if( $store && $customer && $active_cart ){

                //	Set the payment date
                $payment_date = \Carbon\Carbon::now()->format('d M @ H:i');

                //  Set the grand total
                $grand_total = $active_cart->grand_total['currency_money'];

                //  Craft the sms message
                $message = $store->name.': Hi '.$customer->first_name.
                           ', payment successful for order #'.$this->number.
                           ' Amount '.$grand_total.', '.$payment_date;

                $type = 'Order payment';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $message,

                    //  Set the mobile_number on the data
                    'mobile_number' => $this->customer->user->mobile_number['number_with_code']

                ];

                //  Set the sms owning model
                $model = $this;

                //  Create a new sms and send
                return ( new \App\Sms() )->createResource($data, $model, $user)->sendSms();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function checkDeliveryConfirmationCodeValidity($data = [], $user)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->deliverResourceValidation($data);

            //  Verify permissions
            $this->deliverResourcePermission($user);

            //  Get the delivery confirmation code
            $delivery_confirmation_code = $data['delivery_confirmation_code'];

            //  Set the location id
            $location_id = isset($data['location_id']) ? $data['location_id'] : null;

            if( empty($location_id) ){

                throw ValidationException::withMessages([
                    'location_id' => 'The location id is required',
                ]);

            }else{

                $location = \App\Location::find($location_id);

                if( $location ){

                    $hasPermissions = Gate::allows('manage-orders', $location);

                    if( $hasPermissions == false ){

                        throw ValidationException::withMessages([
                            'message' => 'Not authourized. You do not have permissions to confirm the delivery code validity',
                        ]);

                    }

                }else{

                    throw ValidationException::withMessages([
                        'message' => 'The location does not exist',
                    ]);

                }

            }

            //  Find matching order
            $order = $location->orders()->searchDeliveryConfirmationCode($delivery_confirmation_code)->first();

            return response([
                'is_valid' => !empty($order),
                'order' => !empty($order) ? $order->convertToApiFormat() : null
            ], 200);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a new order number
     */
    public function deliverResource($data = [], $user)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->deliverResourceValidation($data);

            //  Verify permissions
            $this->deliverResourcePermission($user);

            //  Set the delivery confirmation code
            $delivery_confirmation_code = isset($data['delivery_confirmation_code']) ? $data['delivery_confirmation_code'] : null;

            //  Set the delivery confirmation code
            $verification_code = isset($data['verification_code']) ? $data['verification_code'] : null;

            //  Set the mobile number
            $mobile_number = isset($data['mobile_number']) ? $data['mobile_number'] : null;

            //  Set the location id
            $location_id = isset($data['location_id']) ? $data['location_id'] : null;

            if( empty($location_id) ){

                throw ValidationException::withMessages([
                    'location_id' => 'The location id is required',
                ]);

            }else{

                $location = \App\Location::find($location_id);

                if( $location ){

                    $hasPermissions = Gate::allows('manage-orders', $location);

                    if( $hasPermissions == false ){

                        throw ValidationException::withMessages([
                            'message' => 'Not authourized. You do not have permissions required to mark the order as delivered',
                        ]);

                    }

                }else{

                    throw ValidationException::withMessages([
                        'message' => 'The location does not exist',
                    ]);

                }

            }

            if( empty($delivery_confirmation_code) && empty($verification_code) ){

                throw ValidationException::withMessages([
                    'delivery_confirmation_code' => 'The delivery confirmation code is required if verification code is not provided',
                    'verification_code' => 'The mobile verification code is required if the delivery confirmation code is not provided'
                ]);

            }

            if( !empty($verification_code) && empty($mobile_number) ){

                throw ValidationException::withMessages([
                    'mobile_number' => 'The customer mobile number is required to verify delivery using the verification code',
                ]);

            }

            //  If the order is not delivered
            if( !$this->is_delivered ){

                $order = null;

                if( !empty($delivery_confirmation_code) ){

                    //  Find matching order
                    $order = $location->orders()->searchDeliveryConfirmationCode($delivery_confirmation_code)->first();

                }elseif( !empty($verification_code) ){

                    $response = (new \App\MobileVerification())->verifyMobileVerificationCode($mobile_number, $verification_code, 'order_delivery_confirmation');
                    $verification_code = $response['mobile_verification_record'];

                    if( isset($verification_code['metadata']['order_id']) ){

                        $order = $this->where('id', $verification_code['metadata']['order_id'])->first();

                    }else{

                        //  Throw a validation error
                        throw ValidationException::withMessages(['verification_code' => 'The mobile verification record is not related to any order']);

                    }

                }


                //  Check if we have a matching delivery confirmation code
                if( $order ){

                    //  Update the order
                    $this->update([
                        'delivery_verified_by_user_id' => $user->id,
                        'delivery_verified_by' => $user->name,
                        'delivery_verified_at' => Carbon::now(),
                        'delivery_confirmation_code' => null,
                        'delivery_verified' => true
                    ]);

                    //  Update the order status as "Delivered"
                    $this->setDeliveryStatusToDelivered();

                    //  If the order is not paid
                    if( !$this->is_paid ){

                        //  Update the order status as "Paid"
                        $this->setPaymentStatusToPaid();

                    }

                    //  Update the customer conversion totals
                    $this->createOrUpdateResourceCustomer(null, $this->customer->user, true);

                    //  Return a fresh instance
                    return $this->fresh();

                }else{

                    if( !empty($delivery_confirmation_code) ){

                        //  The delivery confirmation code is invalid. Throw a validation error
                        throw ValidationException::withMessages(['delivery_confirmation_code' => 'The delivery confirmation code "'.$delivery_confirmation_code.'" is not valid.']);

                    }

                    if( !empty($verification_code) ){

                        //  The delivery confirmation code is invalid. Throw a validation error
                        throw ValidationException::withMessages(['verification_code' => 'The verification code "'.$verification_code.'" is not valid.']);

                    }

                }

            }else{

                //  The order is already delivered. Throw a validation error
                throw ValidationException::withMessages(['delivery_confirmation_code' => 'The order has already been delivered.']);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a transaction and payment short code
     */
    public function sendResourcePaymentRequest($data = [], $user = null)
    {
        try {

            //  Generate a new transaction
            $transaction = $this->createResourceTransaction($data, $user);

            //  If we have a transaction
            if( $transaction ){

                //  Set the user that this payment shortcode will be reserved for
                $data = [

                    //  This is reserved for the user responsible to pay for this transaction
                    'reserved_for_user_id' => $transaction->payer_id

                ];

                //  Generate the transaction payment short code (CommonTraints)
                $transaction->generateResourcePaymentShortCode($data, $user);

                //  Load the generated shortcode on this transaction
                $transaction->load(['paymentShortCode']);

                //  Set the order status to pending
                $this->setPaymentStatusToPending();

                //  Reload the order transactions
                $this->load('transactions');

                //  If the merchant wants to send the customer the payment shortcode sms
                if( isset($data['send_customer_sms']) && !empty($data['send_customer_sms']) ){

                    /**************************************
                     * CHARGE THE MERCHANT FOR THE SMS    *
                     * BEFORE WE CAN SEND THE SMS. IF     *
                     * THE MERCHANT DOES NOT HAVE THE     *
                     * FUNDS, THEN DO NOT SEND SMS        *
                     *************************************/

                    //  Logic to charge the merchant before sending SMS
                    //  Create a transaction for the charged SMS
                    $canSendSms = false;

                    //  If the merchant was charged successfully
                    if( $canSendSms ){

                        //  Send the payment request sms
                        $this->sendPaymentRequestSms($transaction, $user);

                    }

                }

                //  Return the current transaction
                return $transaction;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method pays for an order
     */
    public function payResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the transaction id
            $transaction_id = $data['transaction_id'] ?? null;

            //  Get the matching transaction
            $transaction = $this->transactions()->where('id', $transaction_id)->first();

            //  If we have a matching transaction
            if( $transaction ){

                $paid_status_id = \App\Status::where(['name' => 'Paid', 'type' => 'transaction status'])->first()->id;

                //  Update the transaction status
                $transaction->update([

                    //  Set the "Paid" transaction status
                    'status_id' => $paid_status_id

                ]);

                //  Get the total amount to be paid
                $total_amount_to_be_paid = (float) $this->activeCart->grand_total['amount'];

                //  Calculate the total amount paid
                $total_transaction_amount_paid = $this->transactions()->where('status_id', $paid_status_id)->sum('amount');

                //  If the active cart grand total is equal to the sum of the transactions
                if( $total_amount_to_be_paid == $total_transaction_amount_paid ){

                    //  Set the order status to paid
                    $this->setPaymentStatusToPaid();

                }else{

                    //  Set the order status to partially paid
                    $this->setPaymentStatusToPartiallyPaid();

                }

                //  Send the payment confirmation sms
                $this->sendPaymentConfirmationSms($user);

                //  Return the current order instance
                return $this->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method returns the order transactions
     */
    public function getResourceTransactions($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the order transactions
            $transactions = $this->transactions();

            //  Return a list of order transactions
            return (new \App\Transaction())->getResources($data, $transactions, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new order transaction
     */
    public function createResourceTransaction($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            /**
             *  If we have the percentage rate (A percentage of this order amount to be paid, where the
             *  maximum percentage rate is 100), then we can calculate the amount to be paid using the
             *  percentage rate
             */

            //  Set the initial payment type
            $type = 'full order payment';

            //  Set the initial payment description
            $description = 'Full payment for order #'.$this->number;

            //  Set the Full amount
            $amount = $this->activeCart->grand_total['amount'];

            //  Set the percentage rate (where 100 represents a full order payment)
            $percentage_rate = 100;

            //  If we have the payer mobile number
            if( isset($data['payer_mobile_number']) && !empty($data['payer_mobile_number']) ){

                //  Extract the mobile number
                $mobileNumber = $data['payer_mobile_number'];

                //  Search the user matching the given mobile number
                $currUser = \App\User::searchMobile($mobileNumber, true)->first();

                //  If we have a user
                if( $currUser ){

                    //  Set the payer id to the given user
                    $payerId = $currUser->id;

                }

            }

            //  If we do not have the payer id set
            if( !isset($payerId) || empty($payerId) ){

                //  Set the payer id to the order customer user
                $payerId = $this->customer->user->id;

            }

            //  If we have a percentage rate provided
            if( isset($data['percentage_rate']) && !empty($data['percentage_rate']) ){

                //  Set the new percentage rate (where less than 100 represents a partial order payment)
                //  Convert the percentage rate to a floating number with 2 decimal places
                $percentage_rate = (int) $data['percentage_rate'];

                //  If the percentage rate is greater than 0 and less than 100, then this is a partial payment
                if($percentage_rate > 0 && $percentage_rate < 100){

                    //  Reset payment type
                    $type = 'partial order payment';

                    //  Reset description
                    $description = 'Partial payment for order #'.$this->number;

                    //  Calculate the new amount
                    $amount = ((float) $amount) * ($percentage_rate / 100);

                }

            }

            //  Merge the data with additional fields
            $data = array_merge($data, [

                //  Set the transaction type on the data
                'type' => $type,

                //  Set the transaction amount on the data
                'amount' => $amount,

                //  Set the transaction percentage rate on the data
                'percentage_rate' => $percentage_rate,

                //  Set the transaction payer id (user responsible to pay)
                'payer_id' => $payerId,

                //  Set the transaction description on the data
                'description' => $description,

                //  Set the default transaction status
                'status_id' => \App\Status::where(['name' => 'Pending', 'type' => 'transaction status'])->first()->id

            ]);

            //  Set the transaction owning model
            $model = $this;

            //  If we have the transaction_id
            if( isset($data['transaction_id']) && !empty($data['transaction_id']) ){

                //  Find transaction
                $transaction = \App\Transaction::find($data['transaction_id']);

                //  If transaction exists
                if( $transaction ){

                    //  Update an existing transaction
                    return $transaction->updateResource($data, $user);

                };

            }else{

                //  Create a new transaction
                return ( new \App\Transaction() )->createResource($data, $model);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to open
     */
    public function setStatusToOpen()
    {
        try {

            //  Set order status to "Open"
            $this->updateStatus('Open', 'order status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to archieved
     */
    public function setStatusToArchieved()
    {
        try {

            //  Set order status to "Archieved"
            $this->updateStatus('Archieved', 'order status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to draft
     */
    public function setStatusToDraft()
    {
        try {

            //  Set order status to "Draft"
            $this->updateStatus('Draft', 'order status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to cancelled
     */
    public function setStatusToCancelled()
    {
        try {

            //  Set order status to "Cancelled"
            $this->updateStatus('Cancelled', 'order status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to paid
     */
    public function setPaymentStatusToPaid()
    {
        try {

            //  Set order status to "Paid"
            $this->updateStatus('Paid', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to partially paid
     */
    public function setPaymentStatusToPartiallyPaid()
    {
        try {

            //  Set order status to "Partially Paid"
            $this->updateStatus('Partially Paid', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to unpaid
     */
    public function setPaymentStatusToPending()
    {
        try {

            //  Set order status to "Pending"
            $this->updateStatus('Pending', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to unpaid
     */
    public function setPaymentStatusToUnpaid()
    {
        try {

            //  Set order status to "Unpaid"
            $this->updateStatus('Unpaid', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to refunded
     */
    public function setPaymentStatusToRefunded()
    {
        try {

            //  Set order status to "Refunded"
            $this->updateStatus('Refunded', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status to failed
     */
    public function setPaymentStatusToFailed()
    {
        try {

            //  Set order status to "Failed"
            $this->updateStatus('Failed', 'order payment status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order delivery status to delivered
     */
    public function setDeliveryStatusToDelivered()
    {
        try {

            //  Set order status to "Delivered"
            $this->updateStatus('Delivered', 'order delivery status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order delivery status to undelivered
     */
    public function setDeliveryStatusToUndelivered()
    {
        try {

            //  Set order status to "Undelivered"
            $this->updateStatus('Undelivered', 'order delivery status');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method sets the order payment status
     */
    public function updateStatus( $name = null, $type = null )
    {
        try {

            //  Get the matching order status
            $status = \App\Status::where(['name' => $name, 'type' => $type])->first();

            //  If we have a matching status
            if( $status ){

                //  If we are updating the order status
                if( $type == 'order status'){

                    $this->update([
                        'status_id' => $status->id
                    ]);

                //  If we are updating the order payment status
                }elseif( $type == 'order payment status'){

                    $this->update([
                        'payment_status_id' => $status->id
                    ]);

                //  If we are updating the order delivery status
                }elseif( $type == 'order delivery status'){

                    $this->update([
                        'delivery_status_id' => $status->id
                    ]);

                    //  If this delivery status is set to "Delivered"
                    if( $status->name == 'Delivered' ){

                        //  Set order status to archieved
                        $this->setStatusToArchieved();

                    //  If this delivery status is set to "Undelivered"
                    }elseif( $status->name == 'Undelivered' ){

                        //  Set order status to open
                        $this->setStatusToOpen();

                    }

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', Order::class) === false) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method checks permissions for delivering an existing resource
     */
    public function deliverResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to deliver this resource
                if ($user->can('deliver', Order::class) === false) {

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

            ];

            //  Set validation messages
            $messages = [

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
     *  This method validates updating an existing resource
     */
    public function deliverResourceValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [
                'verification_code' => 'sometimes|required',
                'delivery_confirmation_code' => 'sometimes|required',
            ];

            //  Set validation messages
            $messages = [
                'verification_code.required' => 'The verification code is required',
                'delivery_confirmation_code.required' => 'The delivery confirmation code is required'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
