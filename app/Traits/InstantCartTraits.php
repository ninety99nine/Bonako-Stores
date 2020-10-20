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

    /*  This method creates a new instant cart
     */
    public function initiateCreate( $request )
    {   
        try {

            //  Set the request variable
            $this->request = $request;

            $store_id = $request->input('store_id') ?? null;
            $location_id = $request->input('location_id') ?? null;

            //  Set the template
            $template = [

                /*  Basic Info  */
                'name' => $request->input('name'),
                'description' => $request->input('description'),
        
                /*  Status  */
                'active' => $request->input('active'),

                /*  Store Info  */
                'store_id' => $store_id,

                /*  Location Info  */
                'location_id' => $location_id
                
            ];
            
            /*
             *  Create new a instant_cart, then retrieve a fresh instance
             */
            $this->instant_cart = $this->create($template)->fresh();

            //  If created successfully
            if ($this->instant_cart) {

                //  Set the instant_cart number
                $this->instant_cart->setInstantCartCode();

                //  Assign products to the instant cart
                $this->assignProductsToInstantCart();

                //  Assign coupons to the instant cart
                $this->assignCouponsToInstantCart();

                //  Return a fresh instance
                return $this->instant_cart->fresh()->load(['products', 'coupons']);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /*  This method updates an existing instant cart
     */
    public function initiateUpdate( $request )
    {   
        try {

            //  Get the current instant cart instance
            $this->instant_cart = $this;

            //  Set the request variable
            $this->request = $request;

            $id = $request->input('id') ?? null;
            $store_id = $request->input('store_id') ?? null;
            $location_id = $request->input('location_id') ?? null;

            //  Set the template
            $template = [

                /*  Basic Info  */
                'name' => $request->input('name'),
                'description' => $request->input('description'),
        
                /*  Status  */
                'active' => $request->input('active'),

                /*  Store Info  */
                'store_id' => $store_id,

                /*  Location Info  */
                'location_id' => $location_id
                
            ];
            
            /*
             *  Create new a instant_cart, then retrieve a fresh instance
             */
            $updated = $this->instant_cart->update($template);

            //  If created successfully
            if ($updated) {

                //  Assign products to the instant cart
                $this->assignProductsToInstantCart();

                //  Assign coupons to the instant cart
                $this->assignCouponsToInstantCart();

                //  Return a fresh instance
                return $this->instant_cart->fresh()->load(['products', 'coupons']);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    
    /*  setInstantCartCode()
     *
     *  This method creates a unique instant_cart code using the instant_cart id.
     *  It does this by adding "0" to the isntant cart id
     */
    public function setInstantCartCode()
    {
        try {

            //  Set the unique instant_cart code
            $this->update(['code' => '0'.$this->id]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function assignProductsToInstantCart()
    {
        try {

            $products = $this->request->input('products') ?? [];
            
            if( count($products) ){

                $products_to_add = [];

                foreach ($products as $key => $product) {

                    $product_id = $product['id'];
                    $product_quantity = $product['quantity'];

                    //  Associate the product with the instant cart
                    array_push($products_to_add,
                    [
                        'arrangement' => ($key + 1),
                        'product_id' => $product_id,
                        'owner_type' => 'instant_cart',
                        'quantity' => $product_quantity,
                        'owner_id' => $this->instant_cart->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                }

                //  Delete previous products allocated to this instant cart
                DB::table('product_allocations')->where('owner_id', $this->instant_cart->id)->where('owner_type', 'instant_cart')->delete();
                
                //  Allocate new products to this instant cart
                DB::table('product_allocations')->insert($products_to_add);

            }
        

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function assignCouponsToInstantCart()
    {
        try {

            $coupon_ids = $this->request->input('coupon_ids') ?? [];
            
            if( count($coupon_ids) ){

                $coupons_to_add = [];

                foreach ($coupon_ids as $key => $coupon_id) {

                    //  Associate the coupon with the instant cart
                    array_push($coupons_to_add,
                    [
                        'coupon_id' => $coupon_id,
                        'owner_type' => 'instant_cart',
                        'owner_id' => $this->instant_cart->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                }

                //  Delete previous coupons allocated to this instant cart
                DB::table('coupon_allocations')->where('owner_id', $this->instant_cart->id)->where('owner_type', 'instant_cart')->delete();
                
                //  Allocate new coupons to this instant cart
                DB::table('coupon_allocations')->insert($coupons_to_add);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    
}
