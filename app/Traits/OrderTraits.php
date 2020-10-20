<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Order as OrderResource;
use App\Http\Resources\Orders as OrdersResource;

trait OrderTraits
{
    public $order = null;
    public $request = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($orders = null)
    {
        if( $orders ){
                
            //  Transform the multiple instances
            return new OrdersResource($orders);

        }else{
            
            //  Transform the single instance
            return new OrderResource($this);

        }
    }

    /** initiateCreate() method  
     *   
     *  This method creates a new order
     */
    public function initiateCreate( $request )
    {   
        try {
                
            //  Set the request variable
            $this->request = $request;
            
            $products = $request->input('products') ?? null;
            $store_id = $request->input('store_id') ?? null;
            $location_id = $request->input('location_id') ?? null;
            $coupon_codes = $request->input('coupon_codes') ?? null;
            $delivery_fee = $request->input('delivery_fee') ?? null;
            $customer_info = $request->input('customer_info') ?? null;
            $delivery_info = $request->input('delivery_info') ?? null;
            $checkout_method = $request->input('checkout_method') ?? null;
            $payment_status = $request->input('payment_status') ?? null;
            
            $info = [
                'items' => $products,
                'store_id' => $store_id,
                'coupon_codes' => $coupon_codes,
                'delivery_fee' => $delivery_fee
            ];

            //  Get the store matching the store id
            $store = \App\Store::where('id', $store_id)->first();

            //  Get the cart details
            $cart = ( new \App\MyCart() )->getCartDetails($info);

            //  Set the template
            $template = [

                /*  Basic Info  */
                'number' => null,
                'currency' => $store->currency,
                'created_date' => Carbon::now()->format('Y-m-d H:i:s'),

                /*  Item Info  */
                'item_lines' => $cart['items'] ?? null,

                'coupon_lines' => $cart['coupons'] ?? null,

                /*  Cart Info  */
                'sub_total' => $cart['sub_total'] ?? 0,
                'coupon_total' => $cart['coupon_total'] ?? 0,
                'discount_total' => $cart['discount_total'] ?? 0,
                'coupon_and_discount_total' => $cart['coupon_and_discount_total'] ?? 0,
                'delivery_fee' => $cart['delivery_fee'] ?? 0,
                'grand_total' => $cart['grand_total'] ?? 0,

                /*  Customer Info  */
                'customer_info' => [
                    'first_name' => $customer_info['first_name'],
                    'last_name' => $customer_info['last_name'],
                    'mobile_number' => $customer_info['mobile_number']
                ],

                /*  Delivery Info  */
                'delivery_info' => [
                    'type' => $delivery_info['type'],     // e.g 'deliver_to_me', 'pickup_myself'
                    'deliver_to_me' => [
                        'physical_address' => $delivery_info['deliver_to_me']['physical_address'],
                        'destination' => $delivery_info['deliver_to_me']['destination'],
                        'time' => $delivery_info['deliver_to_me']['time'],
                        'day' => $delivery_info['deliver_to_me']['day'],
                    ],
                    'pickup_myself' => [
                        'destination' => $delivery_info['pickup_myself']['destination'],
                        'time' => $delivery_info['pickup_myself']['time'],
                        'day' => $delivery_info['pickup_myself']['day'],
                    ]
                ],

                /*  Checkout Info  */
                'checkout_method' => $checkout_method,

                /*  Store Info  */
                'store_id' => $store->id ?? null,

                /*  Location Info  */
                'location_id' => $location_id ?? null
            ];
            
            /*
             *  Create new a order, then retrieve a fresh instance
             */
            $this->order = $this->create($template)->fresh();

            //  If created successfully
            if ($this->order) {

                //  Set the order number
                $this->order->setOrderNumber();

                //  If the order payment status is set to unpaid
                if( $payment_status == 'unpaid'){

                    //  Set the order payment status to "Unpaid"
                    $this->order->setStatusToUnpaid();

                }

                //  Return a fresh instance
                return $this->order;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }



    /*  initiateFulfillment() method
     *
     *  This method is used to create order fulfillment
     *  of the current order items. The $orderInfo holds
     *  additional order fulfillment details if any.
     */
    public function initiateFulfillment( $orderInfo = null )
    {
        try {

            $fulfilled_item_lines = [];
            $unfulfilled_item_lines = $this->unfulfilled_item_lines;

            //  If we have item lines already provided then this means we want to fulfill specific item lines
            if( isset($orderInfo['item_lines']) && !empty($orderInfo['item_lines']) ){

                //  Foreach specified item line
                foreach($orderInfo['item_lines'] as $fulfilled_item_line){

                    if( count($unfulfilled_item_lines) ){

                        //  Foreach unfulfilled order item line
                        foreach( $unfulfilled_item_lines as $unfulfilled_item_line ){
    
                            //  Lets check if the current unfulfilled line item matches the current specified item line
                            if( $unfulfilled_item_line['id'] == $fulfilled_item_line['id'] ){
    
                                if( intval($fulfilled_item_line['quantity']) != 0){
    
                                    //  The quantity we want to fulfil cannot be more than the actual quantity available for fulfillment
                                    $hasValidQuantity = intval($fulfilled_item_line['quantity']) <= intval($unfulfilled_item_line['quantity']);
        
                                    $quantity =  $hasValidQuantity ? intval($fulfilled_item_line['quantity']) : intval($unfulfilled_item_line['quantity']);
        
                                    $fulfilled_item_line['quantity'] = $quantity;
    
                                }
    
                            }
    
                        }

                    }
                            
                    array_push($fulfilled_item_lines, $fulfilled_item_line);

                }

            }

            if( !empty($fulfilled_item_lines) ){

                //  Create a new fulfillment
                $fulfillment = ( new \App\Fulfillment() )->create([

                    //  Fulfillment notes 
                    'notes' => $orderInfo['notes'] ?? null,
    
                    //  Fulfillment item lines
                    'item_lines' => $fulfilled_item_lines,

                    'recipient_info' => [
                        
                        //  Recipient name 
                        'name' => $orderInfo['name'] ?? null,
                        
                        //  Recipient mobile number
                        'mobile_number' => $orderInfo['mobile_number'] ?? null

                    ],

                    'order_id' => $this->id
    
                ]);

                //  If the fulfillment was created successfully
                if ($fulfillment) {

                    //  Update fulfilment status
                    $this->updateFulfilmentStatus();

                }

                return true;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    

    public function updateFulfilmentStatus()
    {
        try {

            $orderInstance = $this->fresh();

            //  If the quantity of fulfilled item lines is zero (0)
            if( $orderInstance->quantity_of_fulfilled_item_lines == 0 ){

                //  Mark as unfulfilled
                $status = 'unfulfilled';

            //  If the quantity of unfulfilled item lines is zero (0)
            }elseif( $orderInstance->quantity_of_unfulfilled_item_lines == 0 ){

                //  Mark as fully fulfilled
                $status = 'fulfilled';

            //  Otherwise
            }else{

                //  Mark as partially fulfilled
                $status = 'partially fulfilled';

            }

            //  Update the fulfillment status
            $orderInstance->update([

                'fulfillment_status' => $status

            ]);

        } catch (\Exception $e) {

            throw($e);

        } 
    }

    /*  setOrderNumber()
     *
     *  This method creates a unique order number using the order id.
     *  It does this by padding the unique order id with leading zero's
     *  "0" so that the order number is always atleast 5 digits long
     */
    public function setOrderNumber()
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

    public function setStatusToPaid()
    {
        try {

            //  Set order status to "Paid"
            $this->updatePaymentStatus( $status = 'paid' );

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function setStatusToUnpaid()
    {
        try {

            //  Set order status to "Unpaid"
            $this->updatePaymentStatus( $status = 'unpaid' );

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function setStatusToFailedPayment()
    {
        try {

            //  Set order status to "Failed Payment"
            $this->updatePaymentStatus( $status = 'failed payment' );

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function updatePaymentStatus( $status = null )
    {
        try {

            //  If we do not have a status already defined (Usually when updating an existing order)
            if( $status == null ){

                //  Get a fresh instance of this order
                $orderInstance = $this->fresh();

                //  If the quantity of paid item lines is zero (0)
                if( $orderInstance->quantity_of_paid_item_lines == 0 ){

                    //  Mark as unpaid
                    $status = 'unpaid';

                //  If the quantity of unpaid item lines is zero (0)
                }elseif( $orderInstance->quantity_of_unpaid_item_lines == 0 ){

                    //  Mark as fully paid
                    $status = 'paid';

                //  Otherwise
                }else{

                    //  Mark as partially paid
                    $status = 'partially paid';

                }

            //  If we have a status already defined (Usually when creating a new order)
            }else{

                //  Get the current instance of the order
                $orderInstance = $this;
                
            }

            //  Update the payment status
            $orderUpdatedStatus = $orderInstance->update([

                'payment_status' => $status

            ]);

            //  If the payment status was updated successfully
            if( $orderUpdatedStatus ){

                //  If the current order instance has a general status of draft
                if( $orderInstance->status['name'] == 'Draft' ){

                    //  Update the general status by setting the order status from draft to open status
                    $orderInstance->updateStatus();

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function updateStatus( $status = 'open' )
    {
        try {

            //  Update the general order status
            return $this->update([

                'status' => $status

            ]);

        } catch (\Exception $e) {

            throw($e);

        }
    }
    
}
