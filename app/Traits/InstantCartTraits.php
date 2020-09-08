<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\InstantCart as InstantCartResource;
use App\Http\Resources\InstantCarts as InstantCartsResource;

trait InstantCartTraits
{
    public $request = null;
    public $instant_cart = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($instant_carts = null)
    {
        if( $instant_carts ){
                
            //  Transform the multiple instances
            return new InstantCartsResource($instant_carts);

        }else{
            
            //  Transform the single instance
            return new InstantCartResource($this);

        }
    }

    /*  This method creates a new instant_cart
     */
    public function initiateCreate( $request )
    {   
        //  Set the request variable
        $this->request = $request;
        
        $store_id = $request->input('store_id') ?? null;
        $coupon_ids = $request->input('coupon_ids') ?? [];
        $product_ids = $request->input('product_ids') ?? [];
        $location_id = $request->input('location_id') ?? null;

        //  Set the template
        $template = [

            /*  Basic Info  */
            'code' => null,
    
            /*  Status  */
            'active' => $request->input('active'),

            /*  Store Info  */
            'store_id' => $store_id,

            /*  Location Info  */
            'location_id' => $location_id
            
        ];

        try {
            
            /*
             *  Create new a instant_cart, then retrieve a fresh instance
             */
            $this->instant_cart = $this->create($template)->fresh();

            //  If created successfully
            if ($this->instant_cart) {

                //  Set the instant_cart number
                $this->instant_cart->setInstantCartCode();

                //  Foreach coupon id provided
                foreach ($coupon_ids as $key => $coupon_id) {
                    
                }

                //  Return a fresh instance
                return $this->instant_cart;

            }

        } catch (\Exception $e) {

            //  Throw a validation error
            throw ValidationException::withMessages(['general' => $e->getMessage()]);
            
        }
    }
    
    /*  setInstantCartCode()
     *
     *  This method creates a unique instant_cart code using the instant_cart id.
     *  It does this by padding the unique instant_cart id with leading zero's
     *  "0" so that the instant_cart code is always atleast 5 digits long
     */
    public function setInstantCartCode()
    {
        /*  Generate a unique instant_cart code.
         *  Get the instant_cart id, and Pad the left side with leading "0"
         *  e.g 123 = 00123, 1234 = 01234, 12345 = 12345
         */
        $code = str_pad($this->id, 5, 0, STR_PAD_LEFT);

        //  Set the unique instant_cart number
        $this->update(['code' => $code]);
    }
    
}
