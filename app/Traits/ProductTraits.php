<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\Products as ProductsResource;

trait ProductTraits
{
    public $product = null;
    public $request = null;

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
                return new ProductsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Product){

                //  Transform the single instance
                return new ProductResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new product
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

            //  If the current authenticated user is a Super Admin and the "user_id" is provided
            if( auth('api')->user()->isSuperAdmin() && isset($data['user_id']) ){

                //  Set the "user_id" provided as the user responsible for owning this resource
                $template['user_id'] = $data['user_id'];

            }else{

                //  Set the current authenticated user as the user responsible for owning this resource
                $template['user_id'] = auth('api')->user()->id;

            }

            /**
             *  Create a new resource
             */
            $this->product = $this->create($template)->fresh();

            //  If created successfully
            if ($this->product) {

                //  Re-arrange location products
                $this->product->assignResourceToLocations($data);

            }

            //  Return a fresh instance
            return $this->product->fresh();

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method updates an existing product
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

                //  Re-arrange location products
                $this->assignResourceToLocations($data);

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
     *  This method returns a list of products
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

                //  Set the products to this eloquent builder
                $products = $builder;

            }else{

                //  Get the products
                $products = \App\Product::latest();

            }

            //  Filter the products
            $products = $this->filterResources($data, $products);

            //  Return products
            return $this->collectionResponse($data, $products, $paginate, $convert_to_api_format);

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
                'visibility' => [],
                'total' => $builder->count()
            ];

            //  Set the status filters to calculate the totals
            $filters = [
                'visible', 'invisible'
            ];

            collect($filters)->map(function($filter) use (&$totals, $builder){

                $data['visible'] = ($filter === 'visible') ? 1 : 0;

                $totals['visibility'][$filter] = $this->filterResourcesByVisibility($data, clone $builder)->count();

            })->toArray();

            /**
             *  Return the totals
             *
             *  Example result
             *
             *  [
             *    "visibility" => [
             *       "visible" => 1,
             *       "invisible" => 0
             *      ]

             *  ]
             */
            return $totals;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method filters the products by search or status
     */
    public function filterResources($data = [], $products)
    {
        //  If we need to search for specific products
        if ( isset($data['search']) && !empty($data['search']) ) {

            $products = $this->filterResourcesBySearch($data, $products);

        }else{

            //  Filter products by visibility
            $products = $this->filterResourcesByVisibility($data, $products);

            //  Filter products by promotion (On sale)
            $products = $this->filterResourcesByOnSale($data, $products);

        }

        //  Return the products
        return $products;
    }

    /**
     *  This method filters the products by search
     */
    public function filterResourcesBySearch($data = [], $products)
    {
        //  Set the search term e.g "00123"
        $search_term = $data['search'] ?? null;

        //  Return searched products otherwise original products
        return empty($search_term) ? $products : $products->search($search_term);

    }

    /**
     *  This method filters the products by visibility
     */
    public function filterResourcesByVisibility($data = [], $products)
    {
        if( isset($data['visible']) && !is_null($data['visible']) ) {

            //  Set the visible
            $visible = $data['visible'];

            //  If the visible is set to "true" or "1"
            if( in_array($visible, [true, 1, '1']) ){

                $products = $products->visible();

            //  If the visible is set to "false" or "0"
            }elseif( in_array($visible, [false, 0, '0']) ){

                $products = $products->inVisible();

            }

        }

        //  Return the products
        return $products;
    }

    /**
     *  This method filters the products by sale
     */
    public function filterResourcesByOnSale($data = [], $products)
    {
        if( isset($data['on_sale']) && !is_null($data['on_sale']) ) {

            //  Set the onsale
            $on_sale = $data['on_sale'];

            //  If the on_sale is set to "true" or "1"
            if( in_array($on_sale, [true, 1, '1']) ){

                $products = $products->onSale();

            //  If the on_sale is set to "false" or "0"
            }elseif( in_array($on_sale, [false, 0, '0']) ){

                $products = $products->notOnSale();

            }

        }

        //  Return the products
        return $products;
    }

    /**
     *  This method returns a single product
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $product = \App\Product::where('id', $id)->first() ?? null;

            //  If exists
            if ($product) {

                //  Return product
                return $product;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the product variations
     */
    public function getResourceVariations($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the product variations
            $variations = $this->variations();

            //  Return a list of product variations
            return (new \App\Product())->getResources($data, $variations, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates the product variations
     */
    public function createResourceVariations($data = [], $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceVariationsValidation($data);

            //  Verify permissions
            $this->updateResourcePermission($user);

            //  Set the product variant attributes and allow variants
            $this->setVariantAttributes($data);

            //  Set the product variant attributes and allow variants
            $variant_attribute_matrix = $this->buildVariationsMatrix($data);

            return $this->generateVariationsAndVariants($data, $variant_attribute_matrix, $user);

        } catch (\Exception $e) {

            throw($e);

        }

    }

    public function setVariantAttributes($data = [])
    {
        /**
         *  Sample Structure of $data
         *
         * [
         *    [
         *      'name' => 'Color'
         *      'values' => ["Red", "Green", "Blue"]
         *    ],
         *    [
         *      'name' => 'Size'
         *      'values' => ["L", "M", 'SM']
         *    ]
         * ]
         *
         *  Update Explanation
         *
         *  -   Set the product variant attributes
         *  -   Set allow variants to true
         *
         */
        return $this->update([
            'variant_attributes' => $data,
            'allow_variants' => true,
        ]);

    }

    public function buildVariationsMatrix($data = [])
    {
        /**
         *  Sample Structure of $data
         *
         * [
         *    [
         *      'name' => 'Color'
         *      'values' => ["Red", "Green", "Blue"]
         *    ],
         *    [
         *      'name' => 'Size'
         *      'values' => ["L", "M", 'SM']
         *    ]
         * ]
         *
         *  $variant_attribute_values:
         *
         */

        //  Filter variant attribute with values
        $variant_attributes = collect($data)->filter(function ($variant_attribute) {

            //  Set the variant attribute values e.g ["Red", "Green", "Blue"]
            $variant_attribute_values = $variant_attribute['values'];

            //  Filter variant attribute that are not empty
            return is_array($variant_attribute_values) && !empty($variant_attribute_values);

        });

        /**
         *  Sample Structure of $variant_attribute_values
         *
         *  [
         *      ["Red", "Green", "Blue"],
         *      ["L", "M", 'SM']
         *  ]
         *
         *  Set the variant attribute values
         */
        $variant_attribute_values = collect($variant_attributes)->map(function ($variant_attribute) {

            //  Set the variant attribute values e.g ["Red", "Green", "Blue"]
            return $variant_attribute['values'];

        });

        //  If we have any variant attribute values
        if( count($variant_attribute_values) ){

            /**
             *  Sample Structure of $variant_attribute_matrix
             *
             * [
             *    ["Red","L"],
             *    ["Red","M"],
             *    ["Red","SM"],
             *    ["Green","L"],
             *    ["Green","M"],
             *    ["Green","SM"],
             *    ["Blue","L"],
             *    ["Blue","M"],
             *    ["Blue","SM"]
             * ]
             *
             *  Cross join the variant attribute values into an Matrix
             */
            $variant_attribute_matrix = Arr::crossJoin(...$variant_attribute_values);

            //  Return the variant attribute matrix
            return $variant_attribute_matrix;

        }

        return [];
    }

    public function generateVariationsAndVariants($data, $variant_attribute_matrix, $user)
    {
        //  If we have any generated variations
        if (count($variant_attribute_matrix)) {

            //  Set the templates
            $templates = [];

            //  Foreach variant attribute matrix
            foreach ($variant_attribute_matrix as $key => $options) {

                /**
                 *  If the main product is called "Summer Dress" and the
                 *  $options = ["Small", "Blue", "Cotton"]
                 *
                 *  Then the variation product is named using both the parent
                 *  product name and the variation options. For example:
                 *
                 *  "Summer Dress (Small, Blue, Cotton)"
                 *
                 */
                $name = $this->name.' ('.ucwords(trim(is_array($options) ? implode(', ', $options) : $options)).')';

                /**
                 *  The $template variable represents structure of the product variation
                 */
                $template = [
                    'name' => $name,
                    'description' => $this->description,
                    'show_description' => $this->show_description,
                    'visible' => true,
                    'product_type_id' => $this->product_type_id,
                    'parent_product_id' => $this->id,
                    'user_id' => $user->id,
                ];

                //  Add the template to the rest of the templates
                array_push($templates, $template);

            }

            //  Get existing variations and their respective variables
            $existing_variations = $this->variations()->with('variables')->get();

            $excluded_variation_ids = [];

            //  Foreach existing variation
            foreach ($existing_variations as $existing_variation) {

                /**
                 *  Extract the values of the current variation variables e.g
                 *
                 *  $existing_values = ['Blue', 'M'] or
                 *  $existing_values = ['Red', 'L']
                 */
                $existing_values = collect($existing_variation['variables'])->map(function ($variable) {

                    /**
                     *  Sample Structure of $variable
                     *
                     *  [
                     *      'name' => 'Color'
                     *      'value' => 'Red'
                     *  ]
                     */
                    return $variable['value'];

                });

                //  Foreach variant attribute matrix
                foreach ($variant_attribute_matrix as $key => $new_values) {

                    /**
                     *  Get the variable values for the new product variation
                     *
                     *  $new_values = ['Blue', 'M'] or
                     *  $new_values = ['Red', 'L'].
                     *
                     *  Note that in this case it is possible that the $new_values have more
                     *  values or less values than the existing values
                     *
                     *  $new_values = ['Blue', 'M', 'Cotton'] but $existing_values = ['Blue', 'Cotton']
                     *
                     *  or
                     *
                     *  $new_values = ['Red', 'L', 'Cotton'] but $existing_values = ['Red', 'L']
                     *
                     *  However we are checking if we can find an exact match to prove that the variation
                     *  already exists so that we do not delete it
                     */
                    $new_values = collect($new_values);

                    //  Get the variable values of the existing product variation
                    $diff = $new_values->diff($existing_values);

                    /**
                     *  If we don't have a difference, then we should not delete the existing variations
                     *  and we should not create a new variation
                     */
                    if ($diff->count() == 0) {

                        //  Do not delete the existing variation
                        array_push($excluded_variation_ids, $existing_variation['id']);

                        //  Do not create a new variation (Remove template)
                        unset($templates[$key]);
                        $templates = array_values($templates);

                        //  Do not create a new variation
                        unset($variant_attribute_matrix[$key]);
                        $variant_attribute_matrix = array_values($variant_attribute_matrix);

                    }
                }
            }

            /**
             *  Delete previous product variations (except variations that match the excluded ids)
             */
            $deleted_product_variations = $this->variations()->whereNotIn('id', $excluded_variation_ids)->forceDelete();

            /**
             *  Create the new product variations
             */
            $created_variations = $this->insert($templates);

            //  Get the created product variations
            $created_variations = $this->variations()->whereNotIn('id', $excluded_variation_ids)->get();

            /**
             *  We need a $variants array to store all the variants for each
             *  product variation we just created.
             */
            $variants = [];

            //  Foearch product variation
            foreach ($created_variations as $x => $created_variation) {

                //  Get the product variation id
                $variation_product_id = $created_variation['id'];

                /**
                 *  Get the generated variations. Sometimes each generated
                 *  variation can be a single element or an array. We need
                 *  to check for either case.
                 *
                 *  A single element example:
                 *
                 *   ~ Small
                 *
                 *  An array example:
                 *
                 *   ~ ["Small", "Blue", "Cotton"]
                 */

                /**
                 *  $variant_attribute_matrix[$x] is an Array e.g:
                 *
                 *   ~ ["Small", "Blue", "Cotton"] or
                 *   ~ ["Small", "Blue", "Nylon"]  or
                 *   ~ ["Small", "Red", "Cotton"]
                 *   e.t.c ...
                 *
                 *  This means we had multiple variant attributes e.g
                 *
                 *   ~ "Size" with options "Small", "Medium", "Large", e.t.c
                 *   ~ "Color" with options "Blue", "Red", e.t.c
                 *   ~ "Material" with options "Cotton", "Nylon", e.t.c
                 *   e.t.c
                 */
                foreach ($variant_attribute_matrix[$x] as $y => $variant_attribute_value) {

                    /** We can get the name of the variable attribute that each
                     *  $generated_variation belongs to e.g
                     *
                     *  name = Size, name = Color, e.t.c.
                     */
                    $variant_attribute_name = $data[$y]['name'];

                    /* Final result must be variants with details showing the variant
                     *  attribute name, the option value and product it e.g =>
                     *
                     *  [ name => "Size", value => "Small", product_id => 82 ]
                     *  [ name => "Color", value => "Blue", product_id => 82 ]
                     *  [ name => "Material", value => "Cotton", product_id => 82 ]
                     *  [ name => "Size", value => "Small", product_id => 83 ]
                     *  [ name => "Color", value => "Blue", product_id => 83 ]
                     *  [ name => "Material", value => "Nylon", product_id => 83 ]
                     *  e.t.c
                     *
                     *  We push each one into the $variants array for storage
                     */
                    array_push($variants, [
                        'name' => $variant_attribute_name,      //  E.g Size / Color / Material
                        'value' => $variant_attribute_value,    //  E.g Small / Blue / Cotton
                        'product_id' => $variation_product_id,  //  E.g 10
                    ]);
                }
            }

            if (count($variants)) {

                //  Create the variants of the variations
                $created_product_variants = DB::table('variables')->insert($variants);

            }

            //  Get the variations
            return $this->getResourceVariations($data = [], true, true);
        }
    }

    /** initiateUpdate() method =>
     *
     *  This method is used to create new product variations
     *  for the current product. The $variantAttributeInfo
     *  variable represents the variable attribute names
     *  and options from the dataset provided.
     */
    public function initiateCreateVariations($variantAttributeInfo = null)
    {
        try {
            if ($variantAttributeInfo) {
                /*
                 *  Set the allow variants to true and update the product variant attributes
                 */
                $updatedProduct = $this->update([
                    'variant_attributes' => $variantAttributeInfo,
                    'allow_variants' => true,
                ]);

                /** Structure Of Variables
                 *
                 ***  $variantAttributeInfo:.
                 *
                 * [
                 *    [
                 *      'name' => 'Color'
                 *      'values' => ["Red", "Green", "Blue"]
                 *    ],
                 *    [
                 *      'name' => 'Size'
                 *      'values' => ["L", "M", 'SM']
                 *    ]
                 * ]
                 *
                 *
                 ***  $variant_attribute_values:
                 *
                 * [
                 *    ["Red", "Green", "Blue"],
                 *    ["L", "M", 'SM']
                 * ]
                 *
                 ***  $variant_attribute_matrix:
                 *
                 * [
                 *    ["Red","L"],
                 *    ["Red","M"],
                 *    ["Red","SM"],
                 *    ["Green","L"],
                 *    ["Green","M"],
                 *    ["Green","SM"],
                 *    ["Blue","L"],
                 *    ["Blue","M"],
                 *    ["Blue","SM"]
                 * ]
                 */

                //  Get the variant attribute values
                $variant_attribute_values = collect($variantAttributeInfo)->map(function ($variant_attribute) {
                    return $variant_attribute['values'];
                });

                //  Cross join the variant attribute values into an Matrix
                $variant_attribute_matrix = Arr::crossJoin(...$variant_attribute_values);

                //  If we have any generated variations
                if (count($variant_attribute_matrix)) {
                    $templates = [];

                    foreach ($variant_attribute_matrix as $key => $variation_options) {
                        /*
                         *  If the main product is called "Summer Dress" and the
                         *  $variation_options=["Small", "Blue", "Cotton"]
                         *
                         *  Then the variation product is named using both the parent
                         *  product name and the variation options. For example:
                         *
                         *  "Summer Dress (Small, Blue, Cotton)"
                         *
                         *  If the parent product id is 65 then this variation poduct
                         *  will have an sku in the format
                         *
                         *  "65_small_blue_cotton"
                         */

                        $sku = $this->id.'_'.strtolower(trim(is_array($variation_options) ? implode('_', $variation_options) : $variation_options));

                        $name = $this->name.' ('.ucwords(trim(is_array($variation_options) ? implode(', ', $variation_options) : $variation_options)).')';

                        /*
                         *  The $template variable represents structure of the product variation
                         */
                        $template = [
                            'sku' => $sku,
                            'name' => $name,
                            'active' => $this->active,
                            'type' => $this->type ?? null,
                            'store_id' => $this->store_id,
                            'parent_product_id' => $this->id,
                            'description' => $this->description,
                            'user_id' => auth('api')->user()->id,
                        ];

                        array_push($templates, $template);
                    }

                    //  Get existing variations
                    $existing_variations = $this->variations()->with('variables')->get();

                    $excluded_variation_ids = [];

                    foreach ($existing_variations as $key => $existing_variation) {
                        /** Return values of the current variation variables e.g:
                         *
                         *  $variable_values = ['Blue', 'M'] or $variable_values = ['Red', 'L'].
                         */
                        $existing_values = collect($existing_variation['variables'])->map(function ($variable) {
                            return $variable['value'];
                        });

                        foreach ($variant_attribute_matrix as $key => $new_values) {
                            /** Get the variable values for the new product variation
                             *
                             *  $new_values = ['Blue', 'M'] or $new_values = ['Red', 'L'].
                             *
                             *  Note that in this case it is possible that the $new_values have more
                             *  values or less values
                             *
                             *  $new_values = ['Blue', 'M', 'Cotton'] or $new_values = ['Red', 'L', 'Cotton']
                             *
                             *  However we are checking if we can find an exact match to prove that the variation
                             *  already exists so that we do not delete it
                             */
                            $new_values = collect($new_values);

                            //  Get the variable values of the existing product variation
                            $diff = $new_values->diff($existing_values);

                            /* If we don't have a difference, then we should not delete the existing variations
                                *  and we should not create a new variation
                                */
                            if ($diff->count() == 0) {
                                //  Do not delete the existing variation
                                array_push($excluded_variation_ids, $existing_variation['id']);

                                //  Do not create a new variation
                                unset($templates[$key]);
                                $templates = array_values($templates);
                                unset($variant_attribute_matrix[$key]);
                                $variant_attribute_matrix = array_values($variant_attribute_matrix);
                            }
                        }
                    }

                    /*
                     *  Delete all previous variations (except variations that match the excluded ids)
                     */
                    $deletedProductVariations = $this->variations()->whereNotIn('id', $excluded_variation_ids)->forceDelete();

                    /*
                     *  Create the new variations
                     */
                    $createdProductVariations = $this->insert($templates);

                    //  Get the created variations
                    $created_variations = $this->variations()->whereNotIn('id', $excluded_variation_ids)->get();

                    /** We need a $variants array to store all the variants for each
                     *  product variation we just created.
                     */
                    $variants = [];

                    //  Foearch product variation
                    foreach ($created_variations as $x => $created_variation) {
                        //  Get the product variation id
                        $product_id = $created_variation['id'];

                        /* Get the generated variations. Sometimes each generated
                         *  variation can be a single element or an array. We need
                         *  to check for either case.
                         *
                         *  A single element example:
                         *
                         *   ~ Small
                         *
                         *  An array example:
                         *
                         *   ~ ["Small", "Blue", "Cotton"]
                         */

                        /** $variant_attribute_matrix[$x] is array e.g:
                         *   ~ ["Small", "Blue", "Cotton"] or
                         *   ~ ["Small", "Blue", "Nylon"] or
                         *   ~ ["Small", "Red", "Cotton"] or
                         *   e.t.c ...
                         *
                         *  This means we had multiple variant attributes e.g
                         *   ~ "Size" with options "Small", "Medium", "Large",
                         *   ~ "Color" with options "Blue", "Red",
                         *   ~ "Material" with options "Cotton", "Nylon",
                         *   e.t.c
                         */
                        foreach ($variant_attribute_matrix[$x] as $y => $variant_attribute_value) {
                            /** We can get the name of the variable attribute that each
                             *  $generated_variation belongs to e.g:
                             *  name = Size, name = Color, e.t.c.
                             */
                            $variant_attribute_name = $variantAttributeInfo[$y]['name'];

                            /* Final result make be variants with details showing the variant
                             *  attribute name, the option value and product it e.g =>
                             *
                             *  [ name => "Size", value => "Small", product_id => 82 ]
                             *  [ name => "Color", value => "Blue", product_id => 82 ]
                             *  [ name => "Material", value => "Cotton", product_id => 82 ]
                             *  [ name => "Size", value => "Small", product_id => 83 ]
                             *  [ name => "Color", value => "Blue", product_id => 83 ]
                             *  [ name => "Material", value => "Nylon", product_id => 83 ]
                             *  e.t.c
                             *
                             *  We push each one into the $variants array for storage
                             */
                            array_push($variants, [
                                'name' => $variant_attribute_name,      //  E.g Size / Color / Material
                                'value' => $variant_attribute_value,    //  E.g Small / Blue / Cotton
                                'product_id' => $product_id,            //  E.g 10
                            ]);
                        }
                    }

                    if (count($variants)) {
                        //  Create the variants of the variations
                        $createdProductVariats = DB::table('variables')->insert($variants);
                    }

                    //  Get the variations
                    $created_variations = $this->variations()->get();

                    return $created_variations;
                }
            }


        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns the product locations
     */
    public function getResourceLocations($data = [], $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Get the product locations
            $locations = $this->locations();

            //  Return a list of product locations
            return (new \App\Location())->getResources($data, $locations, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method assigns the product to one or many locations.
     *  The method also arranges the current product as well as
     *  existing products in proper order for each location.
     */
    public function assignResourceToLocations($data)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Set the location id
            $location_id = $data['location_id'] ?? null;

            //  Set the location ids
            $location_ids = $data['location_ids'] ?? [];

            if( $location_id ){

                array_push($location_ids, $location_id);

            }

            //  Get the locations matching the given ids
            $locations = \App\Location::whereIn('id', $location_ids)
                                      ->with('products')
                                      ->distinct()
                                      ->get();

            //  Set the arrangements
            $arrangements = [];

            //  Foreach of the locations
            foreach ($locations as $location) {

                //  Set the product instance arrangement
                $arrangement = 1;

                //  Set this product instance as the first product arrangement
                $arrangement = $this->getResourceArrangementTemplate($this, $location, $arrangement);

                //  Add this arrangement to the list of arrangements
                array_push($arrangements, $arrangement);

                //  Filter products that do not match the current product
                $products = collect($location->products)->filter(function($product) {
                    return $product->id !== $this->id;
                });

                //  Foreach of the location products
                foreach ($products as $key => $product) {

                    //  Set the location product arrangement
                    $arrangement = ($key + 2);

                    //  Set the location product as the next product arrangement
                    $arrangement = $this->getResourceArrangementTemplate($product, $location, $arrangement);

                    //  Add this arrangement to the list of arrangements
                    array_push($arrangements, $arrangement);

                }

            }

            //  If we have any arrangements
            if( count($arrangements) ){

                //  Set the product ids
                $product_ids = collect($arrangements)->map(function($arrangement){
                    return $arrangement['product_id'];
                });

                //  Delete previously assigned location products
                DB::table('product_allocations')->whereIn('product_id', $product_ids)->delete();

                //  Assign products to locations
                DB::table('product_allocations')->insert($arrangements);

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates the product to location assignment record template.
     *  This record identifies the product as well as the location where the
     *  product is assigned and its quantity. Note that the quantity value
     *  is used for product allocations to model resources such as instant
     *  carts and is not relevant for product allocations to locations.
     */
    public function getResourceArrangementTemplate($product, $location, $arrangement)
    {
        //  Set the product arrangement template
        $template = [
            'quantity' => null,
            'product_id' => $product->id,
            'arrangement' => $arrangement,

            'owner_id' => $location->id,
            'owner_type' => $location->resource_type,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        //  Return the arrangement template
        return $template;

    }

    /**
     *  This method deletes a product
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
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', Product::class) === false) {

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
     *  This method validates creating resource variations
     */
    public function createResourceVariationsValidation($data = [])
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
     *  This method verifies if the user is the admin of the location
     */
   public function isAdmin($resource = null)
   {
       try{

            //  Retrieve the User ID
            $user = ($resource instanceof \App\User) ? $resource : \App\User::find($resource);

            //  Check if the user is an admin to any linked location
            if( $user ){

                return $user->locations()->wherePivot('type', 'admin')->whereHas('products', function (Builder $query) {
                    $query->where('id', $this->id);
                })->exists();

            }

            return false;

       } catch (\Exception $e) {

           throw($e);

       }
   }

}
