<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Order as OrderResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Orders as OrdersResource;

trait OrderTraits
{
    public $order = null;

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

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            //  If the current authenticated user is a Super Admin and the "customer_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['customer_id']) ){

                //  Set the "customer_id" provided as the user responsible for owning this resource
                $template['customer_id'] = $data['customer_id'];

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['customer_id'] = auth('api')->user()->id;

            }

            /**
             *  Create a new resource
             */
            $this->order = $this->create($template)->fresh();

            //  If created successfully
            if ($this->order) {

                //  Set the order number
                $this->order->generateResourceNumber();

                //  Update the order status as "Unpaid"
                $this->order->setPaymentStatusToUnpaid();

                //  Update the order status as "Undelivered"
                $this->order->setDeliveryStatusToUndelivered();

                //  Assign order to location
                $this->order->assignResourceToLocation($data);

                //  Create a new cart resource
                $this->order->createResourceCart($data);

                //  Create a new delivery line resource
                $this->order->createResourceDeliveryLine($data);

                //  Refresh the instance to load the delivery line
                $this->order = $this->order->fresh();

                //  Send the order delivery confirmation code sms
                $this->order->sendDeliveryConfirmationCodeSms($user);

                //  Send the new order merchant sms
                $this->order->sendNewOrderMerchantSms($user);

            }

            //  Return a fresh instance
            return $this->order->fresh();

        } catch (\Exception $e) {

            throw($e);

        }

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

            //  Return locations
            return (new \App\Location())->collectionResponse($data, $locations, $paginate, $convert_to_api_format);

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

            //  Update the resource
            $this->order = $this->fresh();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new order cart
     */
    public function createResourceCart($data = [])
    {
        try {

            //  Set the cart owning model
            $model = $this;

            /**
             *  Create new a cart resource
             */
            $cart = ( new \App\Cart() )->createResource($data, $model);

            //  Set order carts to be inactive
            $this->carts()->update(['active' => false]);

            //  Set new cart to be active
            $cart->update(['active' => true]);

            //  Return the cart resource
            return $cart;

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

    /**
     *  This method sends the delivery confirmation code message to the customer
     */
    public function sendDeliveryConfirmationCodeSms($user = null)
    {
        try {

            /*******************************************
             * GENERATE THE DELIVERY CONFIRMATION CODE *
             *******************************************/

            //  Generate 6 digit mobile delivery confirmation code
            $six_digit_random_number = mt_rand(100000, 999999);

            //  Encrypt the delivery confirmation code
            $code = bcrypt($six_digit_random_number);

            //  Set the delivery confirmation code
            $this->update(['delivery_confirmation_code' => $code]);

            //  Craft the sms message
            $message = 'Hi '.$this->deliveryLine->name.', your delivery confirmation code '.
                       'for order #'.$this->number.' is ' .$six_digit_random_number.'. '.
                       'Share this code with your merchant only after you receive your order.';

            $type = 'Order delivery confirmation code';

            $data = [

                //  Set the type on the data
                'type' => $type,

                //  Set the message on the data
                'message' => $message,

                //  Set the mobile_number on the data
                'mobile_number' => $user->mobile_number

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
    public function sendNewOrderMerchantSms($user = null)
    {
        try {

            //  Set the location that received this order
            $location = $this->receivedLocations()->first();

            //  Set the store that the location belongs to
            $store = $location->store;

            //  If this store supports sending merchant sms
            if( $store->allow_sending_merchant_sms ){

                //  Set the store owner name as the merchant name
                $merchant_name = $store->owner->first_name;

                //  Set the store owner mobile number as the merchant mobile number
                $merchant_mobile_number = $store->owner->mobile_number;

                //  Set the customer name otherwise to the billing name
                $customer_name = $this->customer->first_name;

                //  Set the customer mobile number otherwise to the billing mobile number
                $customer_mobile_number = $this->customer->mobile_number;

                //  Set the main short code
                $main_short_code = '*'.env('MAIN_SHORT_CODE').'#';

                //  Set the main short code
                $website_domain = env('MAIN_WEBSITE_DOMAIN');

                //  Set the grand total
                $grand_total = $this->activeCart->currency->symbol . $this->convertToMoney($this->activeCart->grand_total);

                //  Craft the sms message
                $message = 'Hi '.$merchant_name.', order #'.$this->number.' received for '.$store->name.' '.
                           'amount '.$grand_total.' from '. $customer_name. ' ('.$customer_mobile_number.'). '.
                           'Dial '.$main_short_code.' or visit '.$website_domain.' to view order.';

                $type = 'New order alert';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $message,

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
    public function sendPaymentRequestSms($user = null)
    {
        try {

            //  Get the order store
            $store = $this->getResourceStore();

            //  Set the order payment short code
            $payment_short_code = $this->paymentShortCode;

            //  If we have the store and payment short code
            if( $store && $payment_short_code ){

                //  Craft the sms message
                $message = $store->name.': Hi '.$this->customer->first_name.', dial '.
                           $payment_short_code->dialing_code.' to pay for order #'.$this->number.
                           '. Expires after 24hrs.';

                $type = 'Order payment request';

                $data = [

                    //  Set the type on the data
                    'type' => $type,

                    //  Set the message on the data
                    'message' => $message,

                    //  Set the mobile_number on the data
                    'mobile_number' => $this->customer->mobile_number

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
            $delivery_confirmation_code = $data['delivery_confirmation_code'];

            //  If the order is not delivered
            if( !$this->isDelivered() ){

                //  Check if we have a matching delivery confirmation code
                if( Hash::check($delivery_confirmation_code, $this->delivery_confirmation_code) ){

                    //  Update the order
                    $this->update([
                        'delivery_verified_at' => Carbon::now(),
                        'delivery_confirmation_code' => null,
                        'delivery_verified' => true
                    ]);

                    //  Update the order status as "Delivered"
                    $this->setDeliveryStatusToDelivered();


                    //  If the order is not paid
                    if( !$this->isPaid() ){

                        //  Update the order status as "Paid"
                        $this->setPaymentStatusToPaid();

                    }

                    //  Return a fresh instance
                    return $this->fresh();

                }else{

                    //  The delivery confirmation code is invalid. Throw a validation error
                    throw ValidationException::withMessages(['delivery_confirmation_code' => 'The delivery confirmation code "'.$delivery_confirmation_code.'" is not valid.']);

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
     *  This method generates a store payment short code
     */
    public function sendResourcePaymentRequest($user = null)
    {
        try {
            
            //  Generate the payment short code
            $this->generateResourcePaymentShortCode($user);

            //  Set the order status to pending
            $this->setPaymentStatusToPending();

            //  Reload the payment short code
            $this->load('paymentShortCode');

            //  Send the payment request sms 
            $this->sendPaymentRequestSms($user);

            //  Return the current order instance
            return $this;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates an order payment short code
     */
    public function generateResourcePaymentShortCode($user = null)
    {
        try {

            $data = [

                //  Set the action on the data
                'action' => 'payment'

            ];

            //  Set the short code owning model
            $model = $this;

            /**
             *  Create new a short code resource
             */
            return ( new \App\ShortCode() )->createResource($data, $model, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns true/false if the order is delivered
     */
    public function isDelivered()
    {
        try {

            return $this->deliveryStatus->name === 'Delivered' ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns true/false if the order is paid
     */
    public function isPaid()
    {
        try {

            return $this->paymentStatus->name === 'Paid' ? true : false;

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
                'delivery_confirmation_code' => 'required',
            ];

            //  Set validation messages
            $messages = [
                'limit.required' => 'The delivery_confirmation_code attribute is required'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
