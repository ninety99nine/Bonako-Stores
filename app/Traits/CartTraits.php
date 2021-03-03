<?php

namespace App\Traits;
use App\Http\Resources\Cart as CartResource;
use App\Http\Resources\Carts as CartsResource;

trait CartTraits
{
    public $cart = null;
    public $coupons = [];

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
                return new CartsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Cart){

                //  Transform the single instance
                return new CartResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new cart
     */
    public function createResource($data = [], $model = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Build the cart basket
            $cart_basket = $this->buildCartBasket($data);

            //  Compile the resource template
            $template = $this->compileResourceTemplate($cart_basket);

            /**
             *  Create a new resource
             */
            $this->cart = $this->create($template);

            //  If created successfully
            if ( $this->cart ) {

                //  If we have an owning model
                if( $model ){

                    //  Update the cart owner id and owner type
                    $this->cart->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                    ]);

                }

                //  Create the cart item line resources
                $this->cart->createResourceItemLines($cart_basket);

                //  Create the cart counpon line resources
                $this->cart->createResourceCouponLines($cart_basket);

                //  Return a fresh instance
                return $this->cart->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method compiles the cart template by assembling
     *  information collected from other sources.
     */
    public function compileResourceTemplate($cart_basket = [])
    {
        try {

            //  Set the total amount
            $cart_basket['sub_total'] = $cart_basket['sub_total']['amount'];

            //  Set the coupon total amount
            $cart_basket['coupon_total'] = $cart_basket['coupon_total']['amount'];

            //  Set the sale discount total amount
            $cart_basket['sale_discount_total'] = $cart_basket['sale_discount_total']['amount'];

            //  Set the sale discount total amount
            $cart_basket['coupon_and_sale_discount_total'] = $cart_basket['coupon_and_sale_discount_total']['amount'];

            //  Set the delivery fee amount
            $cart_basket['delivery_fee'] = $cart_basket['delivery_fee']['amount'];

            //  Set the grand total amount
            $cart_basket['grand_total'] = $cart_basket['grand_total']['amount'];

            //  Set the currency code
            $cart_basket['currency'] = $cart_basket['currency']['code'];

            //  Return the template with the resource fields allowed
            return collect($cart_basket)->only($this->getFillable())->toArray();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /** 
     *  This method builds a detailed cart on the fly using the supplied 
     *  items, coupons, delivery fees and location information.
     */
    public function buildCartBasket($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the items
            $items = $data['items'] ?? [];

            //  Set the coupons
            $coupons = $data['coupons'] ?? [];

            //  Set the location id
            $location_id = $data['location_id'] ?? null;

            //  Set the allow free delivery
            $allow_free_delivery = $data['allow_free_delivery'] ?? false;

            //  Set the delivery fee
            $delivery_fee = $data['delivery_fee'] ?? 0;

            //  Set the location coupons
            $location_coupons = [];

            //  Set the currency
            $currency = null;

            //  If the location id is provided
            if( $location_id != null  ){

                //  Get the location matching the location id
                $location = \App\Location::where('id', $location_id)->with('coupons')->first();

                //  If we have a location
                if( $location ){

                    //  Update the location coupons
                    $location_coupons = collect($location->coupons)->toArray();

                    //  Update the currency
                    $currency = $location->currency;

                }

            }

            //  Get the cart items
            $cart_items = $this->buildBasketItems($items);

            //  Total of only the cart items combined
            $sub_total = 0;

            //  Total amount discounted because of coupons
            $coupon_total = 0;

            //  Total amount discounted because of items on sale
            $sale_discount_total = 0;

            //  The total of all the cart item costs and coupons
            $grand_total = 0;

            //  Foreach cart item
            foreach ($cart_items as $item) {

                //  Calculate the sub total (Excludes sale discount)
                $sub_total += $item['sub_total']['amount'];

                //  Calculate the total amount discounted because of items on sale
                $sale_discount_total += $item['sale_discount_total']['amount'];

                //  Calculate the grand total (Includes sale discount)
                $grand_total += $item['grand_total']['amount'];

            }

            //  Calculate the total amount discounted because of coupons
            $coupon_total = $this->calcultateCoupons($grand_total, $location_coupons, $coupons);

            //  If we don't have a delivery fee or we allow free delivery
            if( $delivery_fee == 0 || $allow_free_delivery ){

                /** Calculate the grand total (The actual amount minus how much the customer saved using coupons).
                 *  Remember that the grand total already applied the sale discounts.
                 */
                $grand_total = $grand_total - $coupon_total;
                
                //  Set the delivery fee to Zero (In the case that we allow free delivery)
                $delivery_fee = 0;

            //  If we have a delivery fee
            }else{

                $delivery_fee = intval($delivery_fee);

                /** Calculate the grand total (The actual amount minus how much the customer saved using coupons
                 *  plus the delivery fee). Remember that the grand total already applied the sale discounts.
                 */
                $grand_total = $grand_total - $coupon_total + $delivery_fee;

            }

            //  Calculate the coupon and discount total
            $coupon_and_sale_discount_total = $coupon_total + $sale_discount_total;

            return [
                'items' => $cart_items,
                'coupons' => $this->coupons,
                'total_unique_items' => count($cart_items),
                'total_items' => $this->getTotalItemQuantity($cart_items),
                'items_summarized_array' => $this->getItemsSummarizedInArray($cart_items),
                'items_summarized_inline' => $this->getItemsSummarizedInline($cart_items),
                'sub_total' => $this->convertToMoney($currency, $sub_total),
                'coupon_total' => $this->convertToMoney($currency, $coupon_total),
                'sale_discount_total' => $this->convertToMoney($currency, $sale_discount_total),
                'coupon_and_sale_discount_total' => $this->convertToMoney($currency, $coupon_and_sale_discount_total),
                'allow_free_delivery' => $allow_free_delivery,
                'delivery_fee' => $this->convertToMoney($currency, $delivery_fee),
                'grand_total' => $this->convertToMoney($currency, $grand_total),
                'location_id' => $location_id,
                'currency' => $currency
            ];

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /** 
     *  This method searches for the supplied items and returns
     *  detailed profiles of each item with information on the
     *  item price and totals in relation to the quantity.
     */
    public function buildBasketItems($items = [])
    {
        try {

            /*  Sample Item Structure
             *
             *  Each item of the $items Array should contain 
             *  the item id and item quantity
             *  
             *  Example:
             *
             *  $items = [
             *       ['id'=>1, quantity=>2],
             *       ['id'=>2, quantity=>3]
             *   ]
             */
            $cart_items = [];

            //  Extract the item ID's
            $item_ids = collect($items)->map(function ($item) {
                
                return $item['id'];

            })->toArray();

            //  Get product items that match the given item ID's
            $related_items = \App\Product::whereIn('id', $item_ids)->get();

            //  Foreach item
            foreach ($items as $item) {

                //  Foreach related item
                foreach ($related_items as $related_item) {

                    //  If the related item id and the cart item id match
                    if ($related_item['id'] == $item['id']) {

                        //  Set the quantity
                        $quantity =  $item['quantity'] ?? 1;

                        //  Set the unit price
                        $unit_price = $related_item['unit_price']['amount'];

                        //  Set the sub total (based on the unit regular price and quantity)
                        $sub_total = $related_item['unit_regular_price']['amount'] * $quantity;

                        //  Set the sale discount (based on the sale discount and quantity)
                        $sale_discount_total = $related_item['unit_sale_discount']['amount'] * $quantity;

                        //  Set the grand total (based on the unit price and quantity)
                        $grand_total = $unit_price * $quantity;

                        //  Set the cart item (Extract only selected fields)
                        $cart_item = collect($related_item)->only([

                            /*  Product Details  */
                            'id', 'name', 'description', 
                            'is_free', 'unit_regular_price', 'unit_sale_price', 
                            'sku', 'barcode', 

                            /*  Product Attributes  */
                            'unit_price', 'unit_sale_discount'

                        ]);

                        //  Update the cart item with additional fields
                        $cart_item['quantity'] = $quantity;
                        $cart_item['sub_total'] = $this->convertToMoney($related_item->currency, $sub_total);
                        $cart_item['grand_total'] = $this->convertToMoney($related_item->currency, $grand_total);
                        $cart_item['sale_discount_total'] = $this->convertToMoney($related_item->currency, $sale_discount_total);

                        //  Add the current cart item to the rest of the cart items
                        array_push($cart_items, $cart_item);
                    }
                }
            }

            return $cart_items;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /** 
     *  This method calculates the total coupon discount that
     *  must be applied on the grand total provided.
     */
    public function calcultateCoupons($grand_total = 0 , $location_coupons = [], $coupons = [])
    {
        try {

            $total = 0;

            //  If we don't have any location coupons
            if( count($location_coupons) == 0 ){

                //  Return "0" as the total coupon amount applied
                return $total;

            }

            //  Foreach location coupon
            foreach ($location_coupons as $location_coupon) {

                //  If this location coupon is active
                if( $location_coupon['active'] ){

                    //  Set the "Is valid" variable
                    $is_valid = true;

                    //  If the location coupon must not always be applied
                    if( !$location_coupon['always_apply'] ){

                        //	Check if we have a matching coupon provided
                        $is_valid = collect($coupons)->contains(function ($coupon) use ($location_coupon) {

                            //  If the location coupon is applied using a specific code
                            if( $location_coupon['uses_code'] == true ){

                                //	Check if we have a matching code
                                return ($coupon['code'] == $location_coupon['code']);

                            }

                            //	Check if we have a matching id
                            return ($coupon['id'] == $location_coupon['id']);

                        });

                    }

                    //  If we can continue
                    if( $is_valid ){

                        //	Check if we have already applied this coupon
                        $already_applied = collect($this->coupons)->contains(function ($applied_coupon) use ($location_coupon) {

                            //	Check if we have matching ids
                            return ($applied_coupon['id'] == $location_coupon['id']);

                        });

                        //  If we haven't yet applied this coupon
                        if( !$already_applied ){

                            //  If its a percentage rate based coupon
                            if ($location_coupon['is_percentage_rate']['status']) {

                                //  Set the percentage rate
                                $percentage_rate = $location_coupon['percentage_rate'];
                                
                                /** Calculate the percentage coupon discount and add to the total
                                 *  fixed rate cannot be greater than the grand total.
                                 */
                                $total += ($percentage_rate / 100 * $grand_total);

                            //  If its a flat rate based coupon
                            } elseif ($location_coupon['is_fixed_rate']['status']) {

                                //  Set the fixed rate
                                $fixed_rate = $location_coupon['fixed_rate']['amount'];

                                /** Add the fixed coupon discount to the total. Note that the 
                                 *  fixed rate cannot be greater than the grand total.
                                 */
                                $total += ($fixed_rate <= $grand_total ? $fixed_rate : $grand_total);

                            }

                            //  Add this coupon as an applied coupon
                            array_push( $this->coupons, collect($location_coupon)->except(['location_id', 'created_at', 'updated_at']));

                        }

                    }

                }

            }

            /** Return the total coupon discount. Note that the total
             *  discount cannot be greater than the grand total.
             */
            return ($total <= $grand_total ? $total : $grand_total);

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /** 
     *  This method returns the total item quantity. 
     *  Assuming that the items are as follows:
     * 
     *  ["3x(Tomato)", "2x(Anion)", "1x(Garlic)"]
     *
     *  Then the total item quantity is "6"
     */
    public function getTotalItemQuantity($items = [])
    {
        try {
        
            //  Set the total quantity to Zero
            $total_quantity = 0;

            //  If we have items
            if( count( $items ) ){

                //  Foreach item
                foreach($items as $item){

                    //  Calculate the item quantity and add to the total item quantity
                    $total_quantity = $total_quantity + intval($item['quantity']);

                }

            }

            return $total_quantity;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /** 
     *  This method returns the cart items listed in an Array
     *  showing each item with its name and quantity e.g:
     *  ["3x(Tomato)", "2x(Anion)", "1x(Garlic)"]
     */
    public function getItemsSummarizedInArray($items = [])
    {
        try {

            //  Set the items inline to an empty Array
            $items_inline = [];

            //  Foreach item
            foreach($items as $key => $item){

                //  Get the item quantity and name then add to array
                $items_inline[$key] = $item['quantity']."x(".ucfirst($item['name']).")";

            }

            return $items_inline;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /** 
     *  This method returns the cart items listed in a single String
     *  showing each item with its name and quantity separated with
     *  a comma e.g "3x(Tomato), 2x(Anion), 1x(Garlic)"
     */
    public function getItemsSummarizedInline($items = [])
    {
        try {

            //  Set the items inline to an empty String
            $items_inline = '';

            //  Foreach item
            foreach($items as $item){

                //  Get the item quantity and name then join to the string
                $items_inline .= $item['quantity']."x(".ucfirst($item['name']).")";

                //  Separate items using a comma
                $items_inline .= (next($items)) ? ', ' : '';

            }

            return $items_inline;

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
     *  This method creates new cart item lines
     */
    public function createResourceItemLines($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have the data items
            if( isset($data['items']) && !empty($data['items']) ){

                //  Set the items
                $items = $data['items'];

                //  Foreach item
                foreach ($items as $item) {
                    
                    //  Set the data as the current item data
                    $data = collect($item)->toArray();

                    //  Merge the data with additional fields
                    $data = array_merge($data, [

                        //  Set the product id
                        'product_id' => $item['id'],

                        //  Set the cart id
                        'cart_id' => $this->id

                    ]);

                    /**
                     *  Create new a item line resource
                     */
                    ( new \App\ItemLine() )->createResource($data);

                }

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates new cart coupon lines
     */
    public function createResourceCouponLines($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have the coupons
            if( isset($data['coupons']) && !empty($data['coupons']) ){

                //  Set the coupons
                $coupons = $data['coupons'];

                //  Foreach coupon
                foreach ($coupons as $coupon) {
                    
                    //  Set the data as the current coupon data
                    $data = collect($coupon)->toArray();

                    //  Merge the data with additional fields
                    $data = array_merge($data, [

                        //  Set the coupon id
                        'coupon_id' => $coupon['id'],

                        //  Set the cart id
                        'cart_id' => $this->id

                    ]);

                    /**
                     *  Create new a coupon line resource
                     */
                    ( new \App\CouponLine() )->createResource($data);

                }

            }

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

}
