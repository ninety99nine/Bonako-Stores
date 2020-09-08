<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createProduct(Request $request)
    {
        //  Check if the user is authourized to create products
        if ($this->user && $this->user->can('create', Product::class)) {
            //  Create the product
            $product = (new Product())->initiateCreate($request);

            //  If the created successfully
            if ($product) {
                //  Return an API Readable Format of the Product Instance
                return $product->convertToApiFormat();
            }
        } else {
            //  Not Authourized
            return help_not_authorized();
        }
    }

    public function updateProduct(Request $request, $product_id)
    {
        //  Get the product
        $product = \App\Product::where('id', $product_id)->first() ?? null;

        //  Check if the product exists
        if ($product) {
            //  Check if the user is authourized to update the product
            if ($this->user && $this->user->can('update', $product)) {
                //  Update the product
                $updated = $product->update($request->all());

                //  If the update was successful
                if ($updated) {
                    //  Return an API Readable Format of the Product Instance
                    return $product->fresh()->convertToApiFormat();
                }
            } else {
                //  Not Authourized
                return help_not_authorized();
            }
        } else {
            //  Not Found
            return help_resource_not_fonud();
        }
    }

    public function updateProductArrangement(Request $request)
    {
        //  Get the specified location id
        $location_id = $request->input('location_id');
        
        //  Get the specified location
        $location = $location_id ? \App\Location::find($location_id) : null;

        //  Check if the location exists
        if ($location) {

            //  Check if the user is authourized to update location products
            if ($this->user->can('update', $location)) {

                /*  The request data will be an array in the form of:
                 * 
                 *  [
                 *      ["id"=>10, "arrangement"=> 1]
                 *      ["id"=>11, "arrangement"=> 2]
                 *      ["id"=>15, "arrangement"=> 3]
                 *      ...
                 *  ] 
                 * 
                 *  The "id" represents the product to add to assign to this location. The "arrangement"
                 *  is pivot data that will populate the  pivot table record to indicate the 
                 *  arrangement order of the products.
                 * 
                 *  We need to change the current structure. This is so that the saveMany() can 
                 *  also save the pivot data properly.
                 * 
                 *  Example of how "save" and "saveMany" work:
                 * 
                 *  $model->relationship()->save(new or existing model, array of pivot data, touch parent = true) (used on existing model)
                 *  $model->relationship()->saveMany(array of new or existing models, array of arrays with pivot data)
                 * 
                 */
                
                //  Get the request data
                $request_data = $request->input('product_arrangement');

                //  Get the products assigned to this location (Products must not be variations)
                $products_assigned = $location->products()->withPivot('id')->isNotVariation()->get();

                //  Get the old product arrangement records
                $old_assignment_records = DB::table('location_product')->where('location_id', $location->id)->get();

                //  Array to store each product model
                $product_arrangement_collection = [];
                
                //  Array to store each assigned record to delete
                $pivot_ids_to_remove = [];

                /** We use the unique() just so that we make sure that we remove  
                 *  any duplicate products e.g 2 products with "id" = 1
                 */
                foreach (collect($products_assigned)->unique('id') as $product) {

                    /** Get the matching product data from the request data 
                     *  ["id"=>10, "arrangement"=> 1]
                     */
                    $related_product = collect($request_data)->where('id', $product->id)->first();

                    if( $related_product ){

                        //  Foreach assignment record
                        foreach( $old_assignment_records as $old_assignment_record ){
                            
                            //  If the assignment record matches the product id
                            if( $old_assignment_record->product_id == $product->id ){

                                //  Return the assignment record id so that we can later delete this record
                                array_push($pivot_ids_to_remove, $old_assignment_record->id);

                            }

                        }

                        //  Get the details of product arrangement
                        array_push( $product_arrangement_collection, [
                            'product_id' => $product->id,
                            'location_id' => $location->id,
                            'created_at' => DB::raw('now()'),
                            'updated_at' => DB::raw('now()'),
                            'arrangement' => $related_product['arrangement'],
                        ] );

                    }

                }

                if( count($product_arrangement_collection) ){

                    //  Insert the new product arrangement
                    $new_records_inserted = DB::table('location_product')->insert($product_arrangement_collection);

                    //  If the products were assigned successfully
                    if( $new_records_inserted ){

                        //  Remove the old product arrangement records
                        $old_records_deleted = DB::table('location_product')->whereIn('id', $pivot_ids_to_remove)->delete();

                    }

                }

                //  Get the location products
                $products = $location->products()->paginate();

                //  Return an API Readable Format of the Product Instance
                return ( new \App\Product() )->convertToApiFormat($products);

            } else {

                //  Not Authourized
                return oq_api_not_authorized();

            }
        } else {
            //  Not Found
            return help_resource_not_fonud();
        }
    }

    public function getProduct($product_id)
    {
        //  Get the product
        $product = Product::where('id', $product_id)->first() ?? null;

        //  Check if the product exists
        if ($product) {
            //  Return an API Readable Format of the Product Instance
            return $product->convertToApiFormat();
        } else {
            //  Not Found
            return help_resource_not_fonud();
        }
    }

    public function getProductVariations(Request $request, $product_id)
    {
        $limit = $request->input('limit');

        //  Example url: /api/products/1/variations? variables=Type|2D,Time|20:30
        $variables = $request->input('variables') ?? '';

        $variables = explode(',', $variables);
        $variable_attributes = [];

        foreach ($variables as $variable) {
            $variable = trim($variable);
            $attributes = explode('|', $variable);

            $attribute_name = trim($attributes[0] ?? '');
            $attribute_value = trim($attributes[1] ?? '');

            if ($attribute_name && $attribute_value) {
                array_push($variable_attributes, ['name', $attribute_name], ['value', $attribute_value]);
            }
        }

        //  Get the product
        $product = \App\Product::where('id', $product_id)->first() ?? null;

        //  Get the product variations
        $variations = $product->variations();

        if (count($variable_attributes)) {
            foreach ($variable_attributes as $variable_attribute) {
                $variations = $variations->whereHas('variables', function (Builder $query) use ($variable_attribute) {
                    $query->where([$variable_attribute]);
                });
            }
        }

        //  Get the product variations
        $variations = $variations->with('variables')->paginate($limit) ?? null;

        //  Check if the product variations exist
        if ($variations) {
            //  Return an API Readable Format of the Product Instance
            return ( new \App\Product() )->convertToApiFormat($variations);
        } else {
            //  Not Found
            return help_resource_not_fonud();
        }
    }

    public function createProductVariations(Request $request, $product_id)
    {
        //  Get the product
        $product = Product::findOrFail($product_id);

        //  Check if the user is authourized to update the product variations
        if ($this->user->can('update', $product)) {
            //  Get the request data
            $request_data = $request->all();

            //  Generate the  product variations
            $product->initiateCreateVariations($variantAttributeInfo = $request_data);

            //  Get the product variations
            $variations = $product->variations()->paginate() ?? null;

            //  Return an API Readable Format of the Product Instance
            return ( new \App\Product() )->convertToApiFormat($variations);
        } else {
            //  Not Authourized
            return oq_api_not_authorized();
        }
    }
}
