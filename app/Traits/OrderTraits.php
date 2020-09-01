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

    /*  This method creates a new order
     */
    public function initiateCreate( $request )
    {   
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

        try {
            
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

            //  Throw a validation error
            throw ValidationException::withMessages(['general' => $e->getMessage()]);
            
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
        /*  Generate a unique order number.
         *  Get the order id, and Pad the left side with leading "0"
         *  e.g 123 = 00123, 1234 = 01234, 12345 = 12345
         */
        $order_number = str_pad($this->id, 5, 0, STR_PAD_LEFT);

        //  Set the unique order number
        $this->update(['number' => $order_number]);
    }

    public function setStatusToPaid()
    {
        //  Set order status to "Paid"
        $this->updatePaymentStatus( $status = 'paid' );
    }

    public function setStatusToUnpaid()
    {
        //  Set order status to "Unpaid"
        $this->updatePaymentStatus( $status = 'unpaid' );
    }

    public function setStatusToFailedPayment()
    {
        //  Set order status to "Failed Payment"
        $this->updatePaymentStatus( $status = 'failed payment' );
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

            //  Return the error
            return oq_api_notify_error('Query Error', $e->getMessage(), 404);
            
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

            //  Return the error
            return oq_api_notify_error('Query Error', $e->getMessage(), 404);
            
        }  
    }
    
}
