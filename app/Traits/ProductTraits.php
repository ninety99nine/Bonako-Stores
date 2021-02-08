<?php

namespace App\Traits;

use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\Products as ProductsResource;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

    /** initiateCreate()
     * 
     *  This method creates a new product
     */
    public function initiateCreate($request)
    {
        try {

            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $validation_data = $request->validate([
                'name' => 'required',
            ]);

            //  Set the template
            $template = [
                'arrangement' => 1,
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'user_id' => auth('api')->user()->id,
                'active' => $request->input('active'),
                'store_id' => $request->input('store_id'),
                'location_id' => $request->input('location_id'),
                'description' => $request->input('description'),
                'cost_per_item' => $request->input('cost_per_item'),
                'unit_sale_price' => $request->input('unit_sale_price'),
                'unit_regular_price' => $request->input('unit_regular_price'),
                'variant_attributes' => $request->input('variant_attributes'),
                'allow_stock_management' => $request->input('allow_stock_management'),
                'auto_manage_stock' => $request->input('auto_manage_stock'),
                'stock_quantity' => $request->input('stock_quantity'),
                'barcode' => $request->input('barcode'),
                'sku' => $request->input('sku'),
            ];

            /*
             *  Create new a product, then retrieve a fresh instance
             */
            $this->product = $this->create($template)->fresh();

            //  If created successfully
            if ($this->product) {

                //  Rearrange the location products
                $this->rearrangeProducts();

                //  Return a fresh instance
                return $this->product;
            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function rearrangeProducts()
    {
        try {

            if ($this->request->input('location_id')) {
                
                //  Get the location we want to place this product
                $location = \App\Location::where('id', $this->request->input('location_id'))->first();

                //  Get the products that belong to this location except the new product
                $products = collect($location->products()->where('id', '!=', $this->product->id)->get())->toArray();
                
                $ids = [];
                $cases = [];
                $params = [];

                foreach ($products as $key => $product) {

                    $id = $product['id'];
                    $arrangement = ($key + 2);
    
                    $cases[] = "WHEN {$id} then ?";
                    $params[] = $arrangement;
                    $ids[] = $id;

                }

                $ids = implode(',', $ids);
                $cases = implode(' ', $cases);

                if (!empty($ids)) {
                    
                    DB::update("UPDATE products SET `arrangement` = CASE `id` {$cases} END WHERE `id` in ({$ids})", $params);
                
                }

            }

        } catch (\Exception $e) {

            throw($e);

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
}
