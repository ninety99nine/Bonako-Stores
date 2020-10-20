<?php

namespace App\Traits;

use Cart;
use App\Store;
use App\Product;

trait MyCartTraits
{
    public $coupons = [];

    /*  getCartDetails() method:
     *
     *  This method is used to get the details of the cart. It requires that the items
     *  provided include the product id and quantity e.g
     * 
     *  $info['items'] = [ 
     *      ['id' => 1, 'quantity' => 3],
     *      ['id' => 2, 'quantity' => 5] 
     *      ...
     *  ]
     * 
     *  Coupons can also be provided as coupons codes, coupon id or coupon data.
     * 
     * 
     * 
     *  the store discounts and coupons if any. The items variable is an array of individual 
     *  items with an "id" and "quantity". The method will fetch all the items by "id" and 
     *  get their details as well as calculate the individual items and cart sub-totals, 
     *  grand-totals and discounts-totals. Finally the method will return all the 
     *  calculations and items.
     */
    public function getCartDetails($info = [])
    {
        try {

            //  Set the default values
            $info = array_merge([
                'items' => [], 
                'store_id' => null,
                'coupon_ids' => [],
                'coupon_codes' => [], 
                'store_coupons' => [],
                'delivery_fee' => null

            //  Overide the default values with specific details
            ], $info);

            //  Get the items
            $items = $info['items'];

            //  Get the store id
            $store_id = $info['store_id'];

            //  Get the coupon ids
            $coupon_ids = $info['coupon_ids'];

            //  Get the coupon codes
            $coupon_codes = $info['coupon_codes'];

            //  Get the delivery fee
            $delivery_fee = $info['delivery_fee'];

            //  Get the coupons
            $coupons = $info['store_coupons'];

            //  If the store id is provided
            if( $store_id != null  ){
                
                //  Get the store matching the store id
                $store = \App\Store::where('id', $store_id)->with('coupons')->first();
                
                //  If we have a store
                if( $store ){
                    
                    //  Return store coupons that do not already exist in the coupon collection
                    $store_coupons = collect($store->coupons)->filter(function($coupon) use ($coupons){

                        return !collect($coupons)->contains('id', $coupon->id);

                    })->toArray();

                    //  Get the store coupons (if any) and add them to the existing coupons
                    $coupons = array_merge($coupons, $store_coupons);

                }

            }

            //  Get the cart items
            $cart_items = $this->buildItems($items);

            //  Total of only the cart items combined
            $sub_total = 0;

            //  Total amount discounted because of coupons
            $coupon_total = 0;

            //  Total amount discounted because of items on sale
            $sale_discount_total = 0;

            //  The total of all the cart item costs and coupons
            $grand_total = 0;

            //  Foreach cart item
            foreach ($cart_items as $key => $item) {

                /*  Calculate the sub total (excludes discount)  */
                $sub_total += $item['sub_total'];

                /*  Calculate the total amount discounted because of items on sale  */  
                $sale_discount_total += $item['sale_discount'];

                /*  Calculate the grand total (includes discount)  */
                $grand_total += $item['grand_total'];

            }

            /*  Calculate the total amount discounted because of coupons  */  
            $coupon_total = $this->calcultateCoupons($grand_total, $coupons, $coupon_ids, $coupon_codes); 
            
            //  If we don't have a delivery fee
            if( $delivery_fee == null ){

                //  Calculate the grand total (The actual amount minus how much the customer saved using coupons)
                $grand_total = $grand_total - $coupon_total;

            //  If we have a delivery fee
            }else{

                $delivery_fee = intval($delivery_fee);

                //  Calculate the grand total (The actual amount minus how much the customer saved using coupons plus the delivery fee)
                $grand_total = $grand_total - $coupon_total + $delivery_fee;

            }

            /*  Calculate the coupon and discount total  */     
            $coupon_and_discount_total = $coupon_total + $sale_discount_total;

            $cartDetails = [
                'items' => $cart_items,
                'coupons' => $this->coupons,
                'number_of_items' => count($cart_items),
                'total_quantity_of_items' => $this->getTotalItemQuantity($cart_items),
                'items_summarized_array' => $this->getItemsSummarizedInArray($cart_items),
                'items_summarized_inline' => $this->getItemsSummarizedInline($cart_items),
                'sub_total' => $sub_total,
                'coupon_total' => $coupon_total,
                'sale_discount_total' => $sale_discount_total,
                'coupon_and_discount_total' => $coupon_and_discount_total,
                'delivery_fee' => $delivery_fee,
                'grand_total' => $grand_total
            ];

            //  Action was executed successfully
            return $cartDetails;



        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function calcultateCoupons($sub_total = 0 , $coupons = [], $coupon_ids = [], $coupon_codes = [])
    {
        try {

            $total = 0;

            //  If we don't have any coupons
            if( count($coupons) == 0 ){
                
                //  Return "0" as the total coupon amount applied
                return $total;

            }

            //  Foreach cart coupon, calculate the total cart coupon
            foreach ($coupons as $key => $coupon) {

                //  If this coupon is active
                if( $coupon['active'] ){

                    $isValid = true;
        
                    //	Check if we have a matching coupon id provided
                    $isValid = collect($coupon_ids)->contains(function ($coupon_id, $key) use ($coupon) {
        
                        //	Check if we have matching id
                        return ($coupon_id == $coupon['id']);
        
                    });
        
                    //  If the coupon did not match any given coupon id
                    if( !$isValid ){
        
                        //  If the coupon uses a specific code
                        if( $coupon['uses_code'] == true ){
            
                            //	Check if we have a matching coupon code provided
                            $isValid = collect($coupon_codes)->contains(function ($coupon_code, $key) use ($coupon) {
            
                                //	Check if we have matching codes
                                return ($coupon_code == $coupon['code']);
                                
                            });
            
                        }
        
                    }
        
                    //  If the coupon did not match any given coupon id or coupon code
                    if( !$isValid ){
        
                        //  If the coupon must always be applied regardless
                        if( $coupon['always_apply'] == true ){
                            
                            $isValid = true;
            
                        }
                        
                    }
        
                    //  If we can continue
                    if( $isValid ){
            
                        //	Check if we have already applied this coupon
                        $alreadyApplied = collect($this->coupons)->contains(function ($applied_coupon, $key) use ($coupon) {
        
                            //	Check if we have matching ids
                            return ($applied_coupon['id'] == $coupon['id']);
                            
                        });
        
                        //  If we haven't yet applied this coupon
                        if( !$alreadyApplied ){
                    
                            //  If its a percentage rate based coupon
                            if ($coupon['is_percentage_rate']) {
                
                                //  Calculate the percentage coupon amount and add to the total coupon
                                $total += $coupon['percentage_rate']/100 * $sub_total;
                
                            //  If its a flat rate based coupon
                            } elseif ($coupon['is_fixed_rate']) {
                
                                //  Add the fixed coupon to the total coupon
                                $total += $coupon['fixed_rate'];
                
                            }
        
                            //  Add this coupon as an applied coupon
                            array_push( $this->coupons, collect($coupon)->except(['store_id', 'created_at', 'updated_at']));
        
                        }
        
                    }

                }

            }  

            return $total;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    public function convertToMoney($amount = 0)
    {
        try {

            return number_format($amount, 2, '.', ',');

        } catch (\Exception $e) {

            throw($e);

        }   
    }

    public function buildItems($items)
    {
        try {

            /*  Item Structure
            *  
            *  Each item from the $items array should contain the item price
            *  as well as the item quantity.
            *
            *  $items = [
                    ['id'=>1, quantity=>2],
                    ['id'=>2, quantity=>3]
                ]
            */

            $cartItems = [];

            //  Lets get the ids of the items in the cart
            $itemIds = collect($items)->map(function ($item) {
                
                return $item['id'];

            });

            //  Lets get the items from the DB that are related to the cart items
            $selectedColumns = ['id', 'name', 'description', 'type', 'cost_per_item', 'unit_regular_price', 'unit_sale_price', 'sku', 'barcode'];
            $relatedItems = Product::select( $selectedColumns )->whereIn('id', $itemIds)->get();

            //  Foreach item
            foreach ($items as $key => $item) {

                //  Foreach related item
                foreach ($relatedItems as $key => $relatedItem) {

                    //  If the related item id and the cart item id match
                    if ($relatedItem['id'] == $item['id']) {

                        /*  Get the item quantity  */ 
                        $quantity =  $item['quantity'] ?? 1;

                        /*  Update the related item sub total  */   
                        $unit_price = $relatedItem['unit_price'];

                        /*  Get the related item sub total (based on the regular price)  */  
                        $sub_total = $relatedItem['unit_regular_price'] * $quantity;

                        /*  Get the related item sale discount  */
                        $sale_discount = $relatedItem['sale_discount'] * $quantity;

                        /*  Get the related item grand total (based on the regular/sale price)  */
                        $grand_total = $unit_price * $quantity;

                        /*  Build the cart item using the related item information  */
                        $cartItem = collect($relatedItem->toArray())->only([

                            /** Product Details  */
                            'id', 'name', 'description', 'type', 'cost_per_item', 'unit_regular_price', 'unit_sale_price',
                            'sku', 'barcode', 'stock_quantity', 'allow_stock_management', 'auto_manage_stock',
                            'variant_attributes', 'allow_variants', 'allow_downloads', 'show_on_store',
                            'is_new', 'is_featured', 'parent_product_id',

                            /** Product Attributes  */
                            'resource_type', 'unit_price', 'sale_discount'

                        ]);

                        /*  Update the details of the cart item to match its quantity  */
                        $cartItem['quantity'] = $quantity;
                        $cartItem['sub_total'] = $sub_total;
                        $cartItem['grand_total'] = $grand_total;
                        $cartItem['sale_discount'] = $sale_discount;

                        /*  Add the current cart item to the rest of the cart items  */
                        array_push($cartItems, $cartItem);
                    }
                }
            }

            return $cartItems;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /*  getTotalItemQuantity()
     *  
     * This method returns the total cart item quantity E.g 
     * 
     * If we have ["3x(Tomato)", "2x(Anion)", "1x(Garlic)"]
     * 
     * Then the total item quantity is "6"
     * 
     */
    public function getTotalItemQuantity($items)
    {
        try {

            $total_quantity = 0;

            if( count( $items ) ){

                foreach($items as $key => $item){
        
                    //  Get the item quantity and add to the total item quantity
                    $total_quantity = $total_quantity + intval($item['quantity']);
        
                }

            }

            return $total_quantity;

        } catch (\Exception $e) {

            throw($e);

        }
        
    }

    /*  getItemsSummarizedInArrayInArray()
     *  
     * This method returns the cart items listed in an array
     * showing each item with its name and quantity e.g:
     * ["3x(Tomato)", "2x(Anion)", "1x(Garlic)"]
     * 
     */
    public function getItemsSummarizedInArray($items)
    {
        try {

            $items_inline = [];

            foreach($items as $key => $item){

                //  Get the item quantity and name then add to array
                $items_inline[$key] = $item['quantity']."x(".ucfirst($item['name']).")";

            }

            return $items_inline;

        } catch (\Exception $e) {

            throw($e);

        }
        
    }

    /*  getItemsSummarizedInline()
     *  
     * This method returns the cart items listed in a single string
     * showing each item with its name and quantity separated with
     * a comma e.g "3x(Tomato), 2x(Anion), 1x(Garlic)"
     * 
     */
    public function getItemsSummarizedInline($items)
    {
        try {

            $items_inline = '';

            foreach($items as $item){

                //  Get the item quantity and name
                $items_inline .= $item['quantity']."x(".ucfirst($item['name']).")";
                
                //  Separate items using a comma
                $items_inline .= (next($items)) ? ', ' : '';

            }

            return $items_inline;

        } catch (\Exception $e) {

            throw($e);

        }
        
    }
}
