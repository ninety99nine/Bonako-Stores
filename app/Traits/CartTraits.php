<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Cart as CartResource;
use App\Http\Resources\Carts as CartsResource;

trait CartTraits
{
    public $cart = null;
    public $_coupons = [];
    public $_total_items = 0;
    public $_total_coupons = 0;
    public $_total_unique_items = 0;
    public $_item_detected_changes = [];
    public $is_existing_customer = false;
    public $_allow_free_delivery = false;


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
    public function createResource($data = [], $model = null, $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Verify permissions
            $this->createResourcePermission($user);

            //  Check if we used an instant cart
            $used_existing_cart = isset($data['instant_cart_id']) && !empty(isset($data['instant_cart_id']));

            //  If we have an owning model
            if( $model ){

                //  Search for an existing cart matching the owner id and owner type for the given store location
                $this->cart = \App\Cart::where('owner_id', $model->id)->where('owner_type', $model->resource_type)->where('location_id', $data['location_id'])->first();

                //  If we have an existing cart
                if( $this->cart ){

                    if( isset($data['items']) && !empty($data['items']) ){

                        //  Update and return the existing cart
                        $this->cart = $this->cart->updateResource($data, $user);

                    }else{

                        //  Reset and return the existing cart
                        $this->cart = $this->cart->resetResource($user);

                    }

                    //  Generate the resource location product existence report
                    $this->cart->generateLocationProductExistenceReport();

                    //  Generate the resource location coupon existence report
                    $this->cart->generateLocationCouponExistenceReport();

                    return $this->cart;

                }

            }

            //  Build the cart basket
            $cart_basket = $this->buildCartBasket($data, $user);

            //  Merge the existing data with the new data
            $template = array_merge($data, collect($cart_basket)->only($this->getFillable())->toArray());

            /**
             *  Create a new resource
             */
            $this->cart = $this->create($template);

            //  If created successfully
            if ( $this->cart ) {

                //  If we have an owning model
                if( $model ){

                    //  Update the cart owner id and owner type
                    $this->cart->setResourceOwner($model);

                }

                //  Create the cart item line resources
                $this->cart->createResourceItemLines($cart_basket);

                //  Create the cart counpon line resources
                $this->cart->createResourceCouponLines($cart_basket);

                //  Generate the resource location product existence report
                $this->cart->generateLocationProductExistenceReport();

                //  Generate the resource location coupon existence report
                $this->cart->generateLocationCouponExistenceReport();

                //  Generate the resource creation report
                $this->cart->generateResourceCreationReport($used_existing_cart);

                //  Return a fresh instance
                return $this->cart->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a cart creation report
     */
    public function generateResourceCreationReport($used_existing_cart = false)
    {
        $store = $this->getResourceStoreWithLocations();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource creation report
            ( new \App\Report() )->generateResourceCreationReport($this, $this->resourceReportMetadata([
                'used_existing_cart' => $used_existing_cart
            ]), $store->id, $location->id);

        }
    }

    /**
     *  This method generates a cart recovered report
     */
    public function generateResourceRecoveredReport($used_existing_cart = false)
    {
        $store = $this->getResourceStoreWithLocations();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource recovered report
            ( new \App\Report() )->generateResourceRecoveredReport($this, $this->resourceReportMetadata([
                'used_existing_cart' => $used_existing_cart
            ]), $store->id, $location->id);

        }
    }

    /**
     *  This method generates a cart converted report
     */
    public function generateResourceConvertedReport()
    {
        $store = $this->getResourceStoreWithLocations();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource converted report
            ( new \App\Report() )->generateResourceConvertedReport($this, $this->resourceReportMetadata(), $store->id, $location->id);

        }
    }

    /**
     *  This method generates a product existence report. This
     *  basically checks if location contained any products
     *  after we retrieved a cart.
     */
    public function generateLocationProductExistenceReport()
    {
        $store = $this->getResourceStoreWithLocations();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource product existence report
            $location->generateResourceProductExistenceReport();

        }
    }

    /**
     *  This method generates a coupon existence report. This
     *  basically checks if location contained any coupon
     *  after we retrieved a cart.
     */
    public function generateLocationCouponExistenceReport()
    {
        $store = $this->getResourceStoreWithLocations();

        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource coupon existence report
            $location->generateResourceCouponExistenceReport();

        }
    }

    /**
     *  This method returns the cart store
     */
    public function getResourceStoreWithLocations()
    {
        try{

            //  Get the store with locations holding this cart
            return \App\Store::with('locations')->whereHas('locations', function (Builder $query) {
                $query->whereHas('carts', function (Builder $query) {
                    $query->where('carts.id', $this->id);
                });
            })->first();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a cart creation report
     */
    public function resourceReportMetadata($additionalMetadata = [])
    {
        $defaultMetadata = [
            'instant_cart_id' => $this->instant_cart_id,
            'sub_total' => $this->sub_total['amount'],
            'coupon_total' => $this->coupon_total['amount'],
            'sale_discount_total' => $this->sale_discount_total['amount'],
            'coupon_and_sale_discount_total' => $this->coupon_and_sale_discount_total['amount'],
            'delivery_fee' => $this->delivery_fee['amount'],
            'grand_total' => $this->grand_total['amount'],
            'total_items' => $this->total_items,
            'total_unique_items' => $this->total_unique_items,
            'total_coupons' => $this->total_coupons,
            'allow_free_delivery' => $this->allow_free_delivery['status'],
            'owner_type' => $this->owner_type,
        ];

        return array_merge($defaultMetadata, $additionalMetadata);
    }

    /**
     *  This method generates a cart converted report
     */
    public function generateResourceAbandonedReport($abandoned_datetime)
    {
        /** The setEagerLoads([]) helps us to removed the default relationships that are
         *  eager loaded on the store. When trying to eager load "myActiveSubscription",
         *  the system fails since it requores an authenticated user. Since this method
         *  is called by the system when running background processes, we do not have
         *  an authenticated user and this causes issues. We need to simply reject
         *  the default eager loaded relationships and only request the "locations"
         *
         *  Set $with = [ ... ] to [] on the Store Model
         *
         */
        $store = \App\Store::setEagerLoads([])->with('locations')->whereHas('locations', function (Builder $query) {
            $query->whereHas('carts', function (Builder $query) {
                $query->where('carts.id', $this->id);
            });
        })->first();


        //  Foreach store location
        foreach( $store->locations as $location ){

            //  Generate the resource creation report
            ( new \App\Report() )->generateResourceAbandonedReport($this, [
                'instant_cart_id' => $this->instant_cart_id,
                'sub_total' => $this->sub_total['amount'],
                'coupon_total' => $this->coupon_total['amount'],
                'sale_discount_total' => $this->sale_discount_total['amount'],
                'coupon_and_sale_discount_total' => $this->coupon_and_sale_discount_total['amount'],
                'delivery_fee' => $this->delivery_fee['amount'],
                'grand_total' => $this->grand_total['amount'],
                'total_items' => $this->total_items,
                'total_unique_items' => $this->total_unique_items,
                'total_coupons' => $this->total_coupons,
                'allow_free_delivery' => $this->allow_free_delivery['status'],
                'owner_type' => $this->owner_type,
            ], $store->id, $location->id, [
                'created_at' => $abandoned_datetime,
                'updated_at' => $abandoned_datetime
            ]);

        }
    }

    /**
     *  This method marks a cart as abandoned
     */
    public function markResourceAsAbandoned()
    {
        /**
         *  Calculate the time of abandonment
         *  This should be 24 hours since the cart was last updated
         */
        $abandoned_datetime = Carbon::parse($this->updated_at)->addHours(24)->format('Y-m-d H:i:s');

        //  Set the abandoned status to true
        $this->update([
            'abandoned_status' => '1'
        ]);

        //  Generate the resource abandoned report
        $this->generateResourceAbandonedReport($abandoned_datetime);
    }

    /**
     *  This method updates an existing cart
     */
    public function updateResource($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Validate the data
            $this->updateResourceValidation($data);

            //  Build the cart basket
            $cart_basket = $this->buildCartBasket($data, $user);

            //  Merge the existing data with the new data
            $template = array_merge(
                collect($data)->only($this->getFillable())->toArray(),
                collect($cart_basket)->only($this->getFillable())->toArray()
            );

            //  Set the original owner of this resource
            $template['owner_id'] = $this->owner_id;
            $template['owner_type'] = $this->owner_type;

            //  Make sure the cart is not abandoned
            $template['abandoned_status'] = '0';

            //  If the cart was previously abandoned
            if( $this->abandoned_status['status'] ){

                //  Check if we used an instant cart
                $used_existing_cart = isset($data['instant_cart_id']) && !empty(isset($data['instant_cart_id']));

                //  Generate the resource recovered report
                $this->generateResourceRecoveredReport($used_existing_cart);

            }

            /**
             *  Update the resource details
             */
            $updated = $this->update($template);

            //  If updated successfully
            if ($updated) {

                //  Update the cart item line resources
                $this->updateResourceItemLines($cart_basket);

                //  Update the cart counpon line resources
                $this->updateResourceCouponLines($cart_basket);

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
     *  This method updates the existing cart using already
     *  existing item lines and coupon lines
     */
    public function refreshResource($user = null)
    {
        try {

            //  Extract the cart item ids
            $items = collect($this->itemLines)->map(function($itemLine){
                return [
                    'id' => $itemLine['product_id'],
                    'quantity' => $itemLine['original_quantity']
                ];
            })->toArray();

            //  Extract the cart coupon ids
            $coupons = collect($this->couponLines)->map(function($couponLine){
                return [
                    'id' => $couponLine['coupon_id'],
                    'code' => $couponLine['code']
                ];
            })->toArray();

            //  Set data for cart basket
            $data = [
                'items' => $items,
                'coupons' => $coupons,
                'location_id' => $this->location_id,
                'total_refreshes' => ++$this->total_refreshes,
                'delivery_fee' => $this->delivery_fee['amount'],
                'allow_free_delivery' => $this->allow_free_delivery['status'],
                'instant_cart_id' => $this->instant_cart_id
            ];

            return $this->updateResource($data, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method resets the existing cart
     */
    public function resetResource($user = null)
    {
        try {

            //  Set data for cart basket
            $data = [
                'items' => [],
                'coupons' => [],
                'delivery_fee' => 0,
                'allow_free_delivery' => false,
                'location_id' => $this->location_id,
                'total_resets' => ++$this->total_resets,
                'instant_cart_id' => null
            ];

            return $this->updateResource($data, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new cart
     */
    public function cloneResource($model = null)
    {
        try {

            //  Set the cart to clone
            $cart_to_clone = $this;

            //  Set the template with the resource fields allowed
            $template =  collect($this)->only($cart_to_clone->getFillable())->toArray();

            /**
             *  Create a new resource
             */
            $this->cart = $this->create($template);

            /******************************
             *  CLONE CART ITEM LINES     *
             ******************************/

            //  Get the cart to clone item lines
            $item_lines = collect($cart_to_clone->itemLines)->map(function($item_line){

                $item_line = collect($item_line)->only( (new \App\ItemLine)->getFillable() )->toArray();

                //  Restructure item line
                $item_line['is_free'] = $item_line['is_free']['status'];
                $item_line['currency'] = $item_line['currency']['code'];
                $item_line['unit_regular_price'] = $item_line['unit_regular_price']['amount'];
                $item_line['unit_sale_price'] = $item_line['unit_sale_price']['amount'];
                $item_line['unit_price'] = $item_line['unit_price']['amount'];
                $item_line['unit_sale_discount'] = $item_line['unit_sale_discount']['amount'];
                $item_line['sub_total'] = $item_line['sub_total']['amount'];
                $item_line['sale_discount_total'] = $item_line['sale_discount_total']['amount'];
                $item_line['grand_total'] = $item_line['grand_total']['amount'];

                //  Update the owning cart
                $item_line['cart_id'] = $this->cart->id;

                return $item_line;


            })->toArray();

            //  Assign item lines to cart
            DB::table('item_lines')->insert($item_lines);

            /********************************
             *  CLONE CART COUPON LINES     *
             ********************************/

            //  Get the cart to clone coupon lines
            $coupon_lines = collect($cart_to_clone->couponLines)->map(function($coupon_line){

                $coupon_line = collect($coupon_line)->only( (new \App\CouponLine)->getFillable() )->toArray();

                //  Restructure coupon line
                $coupon_line['apply_discount'] = $coupon_line['apply_discount']['status'];
                $coupon_line['activation_type'] = ((strtolower($coupon_line['activation_type']['type']) == 'always apply') ? 1 : 0);
                $coupon_line['allow_free_delivery'] = $coupon_line['allow_free_delivery']['status'];
                $coupon_line['currency'] = $coupon_line['currency']['code'];
                $coupon_line['discount_rate_type'] = substr(strtolower($coupon_line['discount_rate_type']['name']), 0, 1) == 'p' ? 'p' : 'p';
                $coupon_line['fixed_rate'] = $coupon_line['fixed_rate']['amount'];
                $coupon_line['allow_discount_on_minimum_total'] = $coupon_line['allow_discount_on_minimum_total']['status'];
                $coupon_line['discount_on_minimum_total'] = $coupon_line['discount_on_minimum_total']['amount'];
                $coupon_line['allow_discount_on_total_items'] = $coupon_line['allow_discount_on_total_items']['status'];
                $coupon_line['allow_discount_on_total_unique_items'] = $coupon_line['allow_discount_on_total_unique_items']['status'];
                $coupon_line['allow_discount_on_start_datetime'] = $coupon_line['allow_discount_on_start_datetime']['status'];
                $coupon_line['allow_discount_on_end_datetime'] = $coupon_line['allow_discount_on_end_datetime']['status'];
                $coupon_line['allow_usage_limit'] = $coupon_line['allow_usage_limit']['status'];

                //  Update the owning cart
                $coupon_line['cart_id'] = $this->cart->id;

                return $coupon_line;

            })->toArray();

            //  Assign coupon lines to cart
            DB::table('coupon_lines')->insert($coupon_lines);

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

                //  Return a fresh instance
                return $this->cart->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method deletes a single cart
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
     *  This method returns a list of carts
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

                //  Set the carts to this eloquent builder
                $carts = $builder;

            }else{

                //  Get the carts
                $carts = \App\Cart::latest();

            }

            //  Filter the carts
            $carts = $this->filterResources($data, $carts);

            //  Return carts
            return $this->collectionResponse($data, $carts, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the carts by search or status
     */
    public function filterResources($data = [], $carts)
    {
        //  If we need to search for specific carts
        if ( isset($data['search']) && !empty($data['search']) ) {

            $carts = $this->filterResourcesBySearch($data, $carts);

        }elseif ( isset($data['status']) && !empty($data['status']) ) {

            $carts = $this->filterResourcesByStatus($data, $carts);

        }

        //  Return the carts
        return $carts;
    }

    /**
     *  This method filters the carts by search
     */
    public function filterResourcesBySearch($data = [], $carts)
    {
        //  Set the search term e.g "123"
        $search_term = $data['search'] ?? null;

        //  Return searched carts otherwise original carts
        return empty($search_term) ? $carts : $carts->search($search_term);

    }

    /**
     *  This method filters the carts by status
     */
    public function filterResourcesByStatus($data = [], $carts)
    {
        //  Set the statuses to an empty array
        $statuses = [];

        //  Set the status filters e.g ["active", "inactive", ...] or "active,inactive, ..."
        $status_filters = $data['status'] ?? $data;

        //  If the filters are provided as String format e.g "active,inactive"
        if( is_string($status_filters) ){

            //  Set the statuses to the exploded Array ["active", "inactive"]
            $statuses = explode(',', $status_filters);

        }elseif( is_array($status_filters) ){

            //  Set the statuses to the given Array ["active", "inactive"]
            $statuses = $status_filters;

        }

        //  Clean-up each status filter
        foreach ($statuses as $key => $status) {

            //  Convert " active " to "Active"
            $statuses[$key] = ucfirst(strtolower(trim($status)));
        }

        if ( $carts && count($statuses) ) {

            if( in_array('Active', $statuses) ){

                $carts = $carts->active();

            }elseif( in_array('Inactive', $statuses) ){

                $carts = $carts->inActive();

            }

            if( in_array('Free delivery', $statuses) ){

                $carts = $carts->offersFreeDelivery();

            }

        }

        //  Return the carts
        return $carts;
    }

    /**
     *  This method returns a single cart
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $cart = \App\Cart::where('id', $id)->first() ?? null;

            //  If exists
            if ($cart) {

                //  Return cart
                return $cart;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method updates the remaining product stock quantity
     *  in relation to the cart item line quantities
     */
    public function updateRemainingProductStockQuantity()
    {
        //  Set the item lines
        $item_lines = $this->itemLines;

        //  Set the products
        $products = collect($item_lines)->map(function($item_line){

            //  Extract the item line product
            $product = $item_line->product;

            //  If we have a product
            if( !empty($product) ){

                //  Set the quantity on the product
                $product['quantity'] = $item_line['quantity'];

            }

            return $product;

        });

        //  Filter the products
        $products = collect($products)->filter(function($product){

            //  If we have a product
            if( !empty($product) ){

                //  Only return products that support automatic stock management
                return ($product->allow_stock_management && $product->auto_manage_stock);

            }

            return false;

        });

        //  Update the remaining stock quantity of each product
        foreach ($products as $product) {

            $id = $product['id'];
            $quantity = $product['quantity'];
            $stock_quantity = $product['stock_quantity']['value'];
            $remaining_stock_quantity = ($stock_quantity - $quantity) > 0 ? ($stock_quantity - $quantity) : 0;

            //  Update the product stock quantity
            DB::table('products')->where('id', $id)->update(['stock_quantity' => $remaining_stock_quantity]);

        }

        //  Return the cart instance
        return $this;
    }

    /**
     *  This method updates the remaining instant cart stock quantity
     *  in relation to the cart item line quantities
     */
    public function updateRemainingInstantCartStockQuantity()
    {
        /**
         *  Retrieve the instant cart linked to the order cart. Note that
         *  some orders do not have a linked instant cart.
         */
        $instant_cart = $this->instantCart;

        //  If this order has a linked instant cart
        if( $instant_cart ){

            //  If we allow stock management
            if( $instant_cart['allow_stock_management']['status'] ){

                //  Extract the stock quantity value
                $stock_quantity = $instant_cart['stock_quantity']['value'];

                //  Reduce the stock quantity by 1
                $stock_quantity = ($stock_quantity - 1) > 0 ? ($stock_quantity - 1) : 0;

                //  Update the remaining stock quantity
                $instant_cart->update([
                    'stock_quantity' => $stock_quantity
                ]);

            }

        }
    }

    /**
     *  This method compiles the cart template by assembling
     *  information collected from other sources.
     */
    public function compileResourceTemplate($cart_basket = [])
    {
        try {

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method builds a detailed cart on the fly using the supplied
     *  items, coupons, delivery fees and location information.
     */
    public function buildCartBasket($data = [], $user = null)
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

            //  Set the instant cart id
            $instant_cart_id = $data['instant_cart_id'] ?? null;

            //  Set the allow free delivery otherwise default to original value
            $this->_allow_free_delivery = $data['allow_free_delivery'] ?? $this->_allow_free_delivery;

            //  Set the delivery fee
            $delivery_fee = $data['delivery_fee'] ?? 0;

            //  Set the location coupons
            $location_coupons = [];

            //  Set the currency
            $currency = null;

            //  If the location id is provided
            if( $location_id != null ){

                //  Get the location matching the location id
                $location = \App\Location::where('id', $location_id)->with('coupons')->first();

                //  Set the check for existing customer
                $this->is_existing_customer = $location->findCustomerByUserId($user->id)->exists();

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

            /**
             *  Calculate total unique items
             *
             *  Note that we initiated "_total_unique_items" instead of "total_unique_items"
             *  since the "total_unique_items" already exists on the Cart Model as a
             *  database column name. Therefore this will avoid any conflicts
             */
            $this->_total_unique_items = count($cart_items);

            /**
             *  Calculate total items
             *
             *  Note that we initiated "_total_items" instead of "total_items"
             *  since the "total_items" already exists on the Cart Model as a
             *  database column name. Therefore this will avoid any conflicts
             */
            $this->_total_items = $this->getTotalItemQuantity($cart_items);

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
            if( $delivery_fee == 0 || $this->_allow_free_delivery ){

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
                'coupons' => $this->_coupons,
                'total_items' => $this->_total_items,
                'total_unique_items' => $this->_total_unique_items,
                'total_coupons' => $this->_total_coupons,
                'items_summarized_array' => $this->getItemsSummarizedInArray($cart_items),
                'items_summarized_inline' => $this->getItemsSummarizedInline($cart_items),
                'sub_total' => $this->convertToMoney($currency, $sub_total),
                'coupon_total' => $this->convertToMoney($currency, $coupon_total),
                'sale_discount_total' => $this->convertToMoney($currency, $sale_discount_total),
                'coupon_and_sale_discount_total' => $this->convertToMoney($currency, $coupon_and_sale_discount_total),
                'allow_free_delivery' => $this->_allow_free_delivery,
                'delivery_fee' => $this->convertToMoney($currency, $delivery_fee),
                'grand_total' => $this->convertToMoney($currency, $grand_total),
                'currency' => $currency,
                'detected_changes' => $this->_item_detected_changes,
                'location_id' => $location_id,
                'instant_cart_id' => $instant_cart_id,
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
            $related_products = \App\Product::whereIn('id', $item_ids)->get();

            //  If we have a Eloquent cart instance e.g If we are updating
            if( !empty($this->id) ){

                //  Get the cart item lines (if any)
                $item_lines = $this->itemLines;

            }else{

                $item_lines = [];

            }

            //  Foreach item
            foreach ($items as $item) {

                //  Get the cart item line (If we are updating a cart)
                $matching_item_lines = collect($item_lines)->filter(function($item_line) use($item) {

                    return $item_line->product_id == $item['id'];

                });

                //  Set the matching item line
                $item_line = $matching_item_lines->count() ? $matching_item_lines->first() : null;

                //  Get the related product (If we are updating a cart)
                $matching_related_products = collect($related_products)->filter(function($related_product) use($item) {

                    return $related_product->id == $item['id'];

                });

                //  Set the matching related product
                $related_product = $matching_related_products->count() ? $matching_related_products->first() : null;

                //  If we have a related product
                if ( $related_product ) {

                    //  Set the quantity otherwise
                    $quantity =  $item['quantity'];

                    //  Set the original quantity
                    $original_quantity = $quantity;

                    //  Set the stock quantity
                    $stock_quantity = $related_product['stock_quantity']['value'];

                    //  If the original quantity is not provided
                    if( empty($original_quantity) ){

                        //  Default to "1"
                        $quantity = 1;

                    }

                    //  If we do not have stock
                    if( $related_product['has_stock']['type'] == 'no_stock' ){

                        //  Default to "0"
                        $quantity = 0;

                    }

                    //  If we have limited stock
                    if( $related_product['has_stock']['type'] == 'has_stock' && ($original_quantity > $stock_quantity) ){

                        //  Default to available stock quantity
                        $quantity = $stock_quantity;

                    }

                    //  Set the unit price
                    $unit_price = $related_product['unit_price']['amount'];

                    //  Set the sub total (based on the unit regular price and quantity)
                    $sub_total = $related_product['unit_regular_price']['amount'] * $quantity;

                    //  Set the sale discount (based on the sale discount and quantity)
                    $sale_discount_total = $related_product['unit_sale_discount']['amount'] * $quantity;

                    //  Set the grand total (based on the unit price and quantity)
                    $grand_total = $unit_price * $quantity;

                    //  Set the cart item (Extract only selected fields)
                    $cart_item = collect($related_product)->only([

                        /*  Product Details  */
                        'id', 'name', 'description',
                        'is_free', 'unit_regular_price', 'unit_sale_price',
                        'sku', 'barcode',

                        /*  Product Attributes  */
                        'unit_price', 'unit_sale_discount'

                    ]);

                    //  Set the item original quantity
                    $cart_item['original_quantity'] = $original_quantity;

                    //  Reset the item cancellation status
                    $cart_item = $this->resetCancellationStatus($cart_item);

                    //  Reset the item detected changes
                    $cart_item = $this->resetDetectedChanges($cart_item);

                    //  If we do not have stock
                    if( $related_product['has_stock']['type'] == 'no_stock' ){

                        $no_stock_message = $related_product['name'].' removed because it sold out';

                        //  Record cart item change detected
                        $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'no_stock', $no_stock_message);

                        //  Record cart item cancellation status change
                        $cart_item = $this->setCancellationStatus($cart_item, true, $no_stock_message);

                    //  If we have limited stock
                    }elseif( $related_product['has_stock']['type'] == 'has_stock' && ($original_quantity > $stock_quantity) ){

                        $limited_stock_message = $original_quantity.'x('.$related_product['name'].') reduced to ('.$quantity.') because of limited stock';

                        //  Record cart item change detected
                        $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'limited_stock', $limited_stock_message);

                        //  Record cart item cancellation status change
                        $cart_item = $this->setCancellationStatus($cart_item, false);

                    }elseif( $related_product['has_price']['status'] == false ){

                        $no_price_message = $original_quantity.'x('.$related_product['name'].') removed because it has no price';

                        //  Record cart item change detected
                        $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'no_price', $no_price_message);

                        //  Record cart item cancellation status change
                        $cart_item = $this->setCancellationStatus($cart_item, true, $no_price_message);

                    //  If we have a stock now but the line item did not have stock
                    }elseif( $item_line ){

                        //  If the line item did not have stock but now we have new stock
                        $new_stock_from_no_stock = collect($item_line->detected_changes)->contains(function($item_line_detected_change) use ($related_product){

                            return ($item_line_detected_change['type'] == 'no_stock' && $related_product['has_stock']['type'] != 'no_stock');

                        });

                        //  If the line item was reduced due to limited stock but now we have enough stock
                        $new_stock_from_limited_stock = collect($item_line->detected_changes)->contains(function($item_line_detected_change) use ($related_product, $original_quantity, $stock_quantity){

                            return ($item_line_detected_change['type'] == 'limited_stock' && ($related_product['has_stock']['type'] == 'has_stock' && ($original_quantity <= $stock_quantity)));

                        });

                        if( $new_stock_from_no_stock ){

                            $new_stock_message = $quantity.'x('.$related_product['name'].') added because of new stock';

                            //  Record cart item change detected
                            $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'new_stock', $new_stock_message);

                            //  Record cart item cancellation status change
                            $cart_item = $this->setCancellationStatus($cart_item, false);

                        }elseif( $new_stock_from_limited_stock ){

                            $new_stock_message = $item_line->quantity.'x('.$related_product['name'].') increased to ('.$original_quantity.') because of new stock';

                            //  Record cart item change detected
                            $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'new_stock', $new_stock_message);

                            //  Record cart item cancellation status change
                            $cart_item = $this->setCancellationStatus($cart_item, false);

                        }

                        //  If the line item did not have a price but now we have new price
                        $new_price_from_no_price = collect($item_line->detected_changes)->contains(function($item_line_detected_change) use ($related_product){

                            return ($item_line_detected_change['type'] == 'no_price' && $related_product['has_price']['status'] == true);

                        });

                        //  If the line item has a different price from the current price
                        $new_price_from_old_price = $item_line['unit_price']['amount'] != $related_product['unit_price']['amount'];


                        if( $new_price_from_no_price ){

                            $new_price_message = $quantity.'('.$related_product['name'].') added with new price '.$related_product['unit_price']['currency_money'];

                            //  Record cart item change detected
                            $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, 'new_price', $new_price_message);

                            //  Record cart item cancellation status change
                            $cart_item = $this->setCancellationStatus($cart_item, false);

                        }elseif( $new_price_from_old_price ){

                            $inflation = $item_line['unit_price']['amount'] < $related_product['unit_price']['amount'] ? 'increased' : 'reduced';

                            //  If the item line was on sale but the sale ended
                            if( $item_line['on_sale']['status'] && !$related_product['on_sale']['status'] ){

                                $new_price_message = $related_product['name'].' price '.$inflation.' from '.$item_line['unit_price']['currency_money'].' to '.$related_product['unit_price']['currency_money'].' (Sale ended)';

                                if( $inflation == 'increased' ){

                                    $type = 'old_to_new_price_increase_without_sale';

                                }else{

                                    $type = 'old_to_new_price_decrease_without_sale';

                                }

                            //  If the item line was not on sale but the sale started
                            }elseif( !$item_line['on_sale']['status'] && $related_product['on_sale']['status'] ){

                                $new_price_message = $related_product['name'].' price '.$inflation.' from '.$item_line['unit_price']['currency_money'].' to '.$related_product['unit_price']['currency_money'].' (On sale)';

                                if( $inflation == 'increased' ){

                                    $type = 'old_to_new_price_increase_with_sale';

                                }else{

                                    $type = 'old_to_new_price_decrease_with_sale';

                                }

                            //  If we had no sale related changes
                            }else{

                                $new_price_message = $related_product['name'].' price '.$inflation.' from '.$item_line['unit_price']['currency_money'].' to '.$related_product['unit_price']['currency_money'];

                                if( $inflation == 'increased' ){

                                    $type = 'old_to_new_price_increase';

                                }else{

                                    $type = 'old_to_new_price_decrease';

                                }
                            }

                            //  Record cart item change detected
                            $cart_item = $this->recordItemDetectedChange($cart_item, $item_line, $type, $new_price_message);

                            //  Record cart item cancellation status change
                            $cart_item = $this->setCancellationStatus($cart_item, false);

                        }
                    }

                    //  Update the cart item with additional fields
                    $cart_item['quantity'] = $quantity;
                    $cart_item['sub_total'] = $this->convertToMoney($related_product->currency, $sub_total);
                    $cart_item['grand_total'] = $this->convertToMoney($related_product->currency, $grand_total);
                    $cart_item['sale_discount_total'] = $this->convertToMoney($related_product->currency, $sale_discount_total);

                    //  Add the current cart item to the rest of the cart items
                    array_push($cart_items, $cart_item);
                }
            }

            return $cart_items;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function resetDetectedChanges($cart_item)
    {
        $cart_item['detected_changes'] = [];

        return $cart_item;
    }

    public function resetCancellationStatus($cart_item)
    {
        $cart_item['is_cancelled'] = false;
        $cart_item['cancellation_reason'] = null;

        return $cart_item;
    }

    public function setCancellationStatus($cart_item, $status = true, $cancellation_reason = '')
    {
        $cart_item['is_cancelled'] = $status;
        $cart_item['cancellation_reason'] = $cancellation_reason;

        return $cart_item;
    }

    public function recordItemDetectedChange($cart_item, $item_line, $type, $message)
    {
        //  Convert the detected changes into an array
        $detected_changes = collect($cart_item['detected_changes'])->toArray();

        $detected_change = [
            'type' => $type,
            'message' => $message,
            'notified_user' => false
        ];

        //  If we have an item line that is related to this item
        if( $item_line ){

            //  Check if the user has already been notified about this detected change
            $detected_change['notified_user'] = collect($item_line->detected_changes)->contains(function($item_line_detected_change) use ($detected_change){

                /**
                 *  If this item line has a detected change that matches the current detected change
                 *  then the user has already been notified, therefore we must update the status by
                 *  setting it to true otherwise default to false.
                 */
                return ($item_line_detected_change['type'] == $detected_change['type']);

            });

        }

        //  Record change
        array_push($detected_changes, $detected_change);

        //  Set change
        $cart_item['detected_changes'] = $detected_changes;

        //  Set change for cart
        $this->_item_detected_changes = array_merge($this->_item_detected_changes, $detected_changes);

        return $cart_item;
    }

    /**
     *  This method calculates the total coupon discount that
     *  must be applied on the grand total provided.
     */
    public function calcultateCoupons($grand_total = 0 , $location_coupons = [], $customer_coupons = [])
    {
        try {

            $total = 0;

            //  If we don't have any location coupons
            if( count($location_coupons) == 0 ){

                //  Return "0" as the total coupon amount applied
                return $total;

            }else{

                //  Capture location coupons that must always be applied (Add to the customers list of coupons)
                $customer_coupons = collect(array_merge(collect($location_coupons)->filter(function($location_coupon) {

                    //  If we must always apply this location coupon
                    return ($location_coupon['activation_type']['type'] == 'always apply');

                })->toArray(), $customer_coupons))->unique('id');

            }

            //  Foreach location coupon
            foreach ($location_coupons as $location_coupon) {

                //  If this location coupon is active
                if( $location_coupon['active']['status'] ){

                    //  Set the "Is valid" variable
                    $is_valid = true;

                    //  Increment the total coupons applied
                    $this->_total_coupons += 1;

                    //	Check if we have a matching coupon provided
                    $is_valid = collect($customer_coupons)->contains(function ($coupon) use ($location_coupon, $grand_total){

                        //  If the location coupon is applied using a specific code
                        if( $location_coupon['activation_type']['type'] == 'use code' ){

                            //	Check if we have a matching code
                            $is_valid = isset($coupon['code']) && ($coupon['code'] == $location_coupon['code']);

                            //  If not valid return false
                            if( !$is_valid ) return false;

                        }else{

                            //	Check if we have a matching id
                            $is_valid = ($coupon['id'] == $location_coupon['id']);

                            //  If not valid return false
                            if( !$is_valid ) return false;

                        }

                        $discount_on_minimum_total = $location_coupon['discount_on_minimum_total']['amount'];
                        $allow_discount_on_minimum_total = $location_coupon['allow_discount_on_minimum_total']['status'];

                        //  If this coupon allows a discount on a minimum total
                        if( $allow_discount_on_minimum_total && $grand_total < $discount_on_minimum_total ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_total_items = $location_coupon['discount_on_total_items'];
                        $allow_discount_on_total_items = $location_coupon['allow_discount_on_total_items']['status'];

                        //  If this coupon allows a discount on minimum total items
                        if( $allow_discount_on_total_items && $this->_total_items < $discount_on_total_items ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_total_unique_items = $location_coupon['discount_on_total_unique_items'];
                        $allow_discount_on_total_unique_items = $location_coupon['allow_discount_on_total_unique_items']['status'];

                        //  If this coupon allows a discount on minimum total unique items
                        if( $allow_discount_on_total_unique_items && $this->_total_unique_items < $discount_on_total_unique_items ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_start_datetime = $location_coupon['discount_on_start_datetime'];
                        $allow_discount_on_start_datetime = $location_coupon['allow_discount_on_start_datetime']['status'];

                        //  If this coupon allows a discount only if the start time is reached
                        if( $allow_discount_on_start_datetime && !(\Carbon\Carbon::parse($discount_on_start_datetime)->isPast()) ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_end_datetime = $location_coupon['discount_on_end_datetime'];
                        $allow_discount_on_end_datetime = $location_coupon['allow_discount_on_end_datetime']['status'];

                        //  If this coupon allows a discount only if the end time is not reached
                        if( $allow_discount_on_end_datetime && \Carbon\Carbon::parse($discount_on_end_datetime)->isPast() ){

                            //  Return false since its not valid
                            return false;

                        }

                        $usage_limit = $location_coupon['usage_limit'];
                        $usage_quantity = $location_coupon['usage_quantity'];
                        $allow_usage_limit = $location_coupon['allow_usage_limit']['status'];

                        //  If this coupon allows a discount only if the usage limit is not reached
                        if( $allow_usage_limit && $usage_quantity < $usage_limit  ){

                            //  Return false since its not valid
                            return false;

                        }







                        $discount_on_times = $location_coupon['discount_on_times'];
                        $allow_discount_on_times = $location_coupon['allow_discount_on_times']['status'];

                        //  If this coupon allows a discount only on specific times
                        if( $allow_discount_on_times && in_array(Carbon::now()->format('H'), $discount_on_times) === false  ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_days_of_the_week = $location_coupon['discount_on_days_of_the_week'];
                        $allow_discount_on_days_of_the_week = $location_coupon['allow_discount_on_days_of_the_week']['status'];

                        //  If this coupon allows a discount only on specific days of the week
                        if( $allow_discount_on_days_of_the_week && in_array(Carbon::now()->format('l'), $discount_on_days_of_the_week) === false  ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_days_of_the_month = $location_coupon['discount_on_days_of_the_month'];
                        $allow_discount_on_days_of_the_month = $location_coupon['allow_discount_on_days_of_the_month']['status'];

                        //  If this coupon allows a discount only on specific days of the month
                        if( $allow_discount_on_days_of_the_month && in_array(Carbon::now()->format('d'), $discount_on_days_of_the_month) === false  ){

                            //  Return false since its not valid
                            return false;

                        }

                        $discount_on_months_of_the_year = $location_coupon['discount_on_months_of_the_year'];
                        $allow_discount_on_months_of_the_year = $location_coupon['allow_discount_on_months_of_the_year']['status'];

                        //  If this coupon allows a discount only on specific months of the year
                        if( $allow_discount_on_months_of_the_year && in_array(Carbon::now()->format('F'), $discount_on_months_of_the_year) === false  ){

                            //  Return false since its not valid
                            return false;

                        }

                        $allow_discount_on_new_customer = $location_coupon['allow_discount_on_new_customer']['status'];

                        //  If this coupon allows a discount only if the user is a new customer
                        if( $allow_discount_on_new_customer && $this->is_existing_customer === true  ){

                            //  Return false since its not valid
                            return false;

                        }

                        $allow_discount_on_existing_customer = $location_coupon['allow_discount_on_existing_customer']['status'];

                        //  If this coupon allows a discount only if the user is an existing customer
                        if( $allow_discount_on_existing_customer && $this->is_existing_customer === false  ){

                            //  Return false since its not valid
                            return false;

                        }

                        //  At this point the coupon is valid
                        return true;

                    });

                    //  If we can continue
                    if( $is_valid ){

                        //	Check if we have already applied this coupon
                        $already_applied = collect($this->_coupons)->contains(function ($applied_coupon) use ($location_coupon) {

                            //	Check if we have matching ids
                            return ($applied_coupon['id'] == $location_coupon['id']);

                        });

                        //  If we haven't yet applied this coupon
                        if( $already_applied == false ){

                            //  Check if this coupon offers free delivery
                            if ($location_coupon['allow_free_delivery']['status']) {

                                //  Make an update for free delivery
                                $this->_allow_free_delivery = true;

                            }

                            $apply_discount = $location_coupon['apply_discount']['status'];

                            $discount_rate_type = $location_coupon['discount_rate_type']['type'];

                            //  If we allow discounts and its a percentage rate based coupon
                            if ($apply_discount && $discount_rate_type == 'Percentage') {

                                //  Set the percentage rate
                                $percentage_rate = $location_coupon['percentage_rate'];

                                /** Calculate the percentage coupon discount and add to the total
                                 *  fixed rate cannot be greater than the grand total.
                                 */
                                $total += ($percentage_rate / 100 * $grand_total);

                            //  If we allow discounts and its a fixed rate based coupon
                            } elseif ($apply_discount && $discount_rate_type == 'Fixed') {

                                //  Set the fixed rate
                                $fixed_rate = $location_coupon['fixed_rate']['amount'];

                                /** Add the fixed coupon discount to the total. Note that the
                                 *  fixed rate cannot be greater than the grand total.
                                 */
                                $total += ($fixed_rate <= $grand_total ? $fixed_rate : $grand_total);

                            }

                            //  Add this coupon as an applied coupon
                            array_push( $this->_coupons, collect($location_coupon)->except(['location_id', 'created_at', 'updated_at']));

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
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', Cart::class) === false) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

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
            if( isset($data['items']) && is_array($data['items']) ){

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
     *  This method updates existing cart item lines
     */
    public function updateResourceItemLines($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have the data items
            if( isset($data['items']) && is_array($data['items']) ){

                //  Set the items
                $items = $data['items'];

                //  If all items were removed
                if( count($items) == 0 ){

                    //  Delete all item lines
                    $this->itemLines()->delete();

                }else{

                    //  Get the cart item lines
                    $itemLines = $this->itemLines;

                    //  Foreach item
                    foreach ($items as $item) {

                        //  Get the existing item line that matches this item
                        $itemLine = collect($itemLines)->filter(function($itemLine) use ($item) {

                            return $itemLine->product_id == $item['id'];

                        })->first();

                        //  If we have a matched item line
                        if( $itemLine ){

                            //  Convert the data of the current item to an Array
                            $data = collect($item)->toArray();

                            /**
                             *  Update item line resource
                             */
                            $itemLine->updateResource($data);

                        //  If we don't have a matched item line
                        }else{

                            //  Create the cart item line resource
                            $this->createResourceItemLines([
                                'items' => [ $item ]
                            ]);

                        }

                    }

                    //  Return only item lines that do not exist anymore
                    $removedItemLines = collect($itemLines)->filter(function($itemLine) use ($items) {
                        $itemLineExists = collect($items)->contains(function($item) use ($itemLine) {
                            return $itemLine->product_id == $item['id'];
                        });

                        return ($itemLineExists == false);
                    });

                    //  Foreach item line that was removed
                    foreach ($removedItemLines as $removedItemLine) {

                        /**
                         *  CANCEL THE ITEM ON THE CART
                         *
                         *  We do not delete since the USSD can have (2) items in the
                         *  cart, but it will have to send the first update with only (1 item), then the second
                         *  update with both the (2 items). On the first update we do not want to delete item
                         *  #2 just because it was not mentioned on the first update, since we know that it
                         *  will be mentioned on the second udpate. Therefore we must temporarily cancel
                         *  item #2, then later on the second update it will be uncancelled if it is
                         *  still valid for use on the cart.
                         */
                        $removedItemLine->update([
                            'is_cancelled' => true,
                            'cancellation_reason' => 'Removed from cart'
                        ]);

                    }

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
            if( isset($data['coupons']) && is_array($data['coupons']) ){

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
     *  This method updates existing cart coupon lines
     */
    public function updateResourceCouponLines($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  If we have the data coupons
            if( isset($data['coupons']) && is_array($data['coupons']) ){

                //  Set the coupons
                $coupons = $data['coupons'];

                //  Get the cart coupon lines
                $couponLines = $this->couponLines;

                //  Foreach coupon
                foreach ($coupons as $coupon) {

                    //  Get the existing coupon line that matches this coupon
                    $couponLine = collect($couponLines)->filter(function($couponLine) use ($coupon) {

                        return $couponLine->coupon_id == $coupon['id'];

                    })->first();

                    //  If we have a matched coupon line
                    if( $couponLine ){

                        //  Convert the data of the current coupon to an Array
                        $couponArray = collect($coupon)->toArray();

                        //  Convert the coupon line to an Array
                        $couponLineArray = collect($couponLine)->toArray();

                        //  Merge and replace the coupon line data with the new data
                        $data = array_merge($couponLineArray, $couponArray);

                        /**
                         *  Update coupon line resource
                         */
                        $couponLine->updateResource($data);


                    //  If we don't have a matched coupon line
                    }else{

                        //  Create the cart coupon line resource
                        $this->createResourceCouponLines([
                            'coupons' => [ $coupon ]
                        ]);

                    }

                }

                //  Return only coupon lines that do not exist anymore
                $removedCouponLines = collect($couponLines)->filter(function($couponLine) use ($coupons) {
                    $couponLineExists = collect($coupons)->contains(function($coupon) use ($couponLine) {
                        return $couponLine->coupon_id == $coupon['id'];
                    });

                    return !$couponLineExists;
                });

                //  Foreach coupon line that was removed
                foreach ($removedCouponLines as $removedCouponLine) {

                    //  Delete this coupon line since it does not exist anymore
                    $removedCouponLine->delete();

                }

                //  If all coupons were removed
                if( count($coupons) == 0 ){

                    //  Delete all coupon lines
                    $this->couponLines()->delete();

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
