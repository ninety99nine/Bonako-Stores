<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Product;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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
        try{

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

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateProduct(Request $request, $product_id)
    {
        try{

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
                return help_resource_not_found();
            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getProduct($product_id)
    {
        try{

            //  Get the product
            $product = Product::where('id', $product_id)->first() ?? null;

            //  Check if the product exists
            if ($product) {
                //  Return an API Readable Format of the Product Instance
                return $product->convertToApiFormat();
            } else {
                //  Not Found
                return help_resource_not_found();
            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getProductVariations(Request $request, $product_id)
    {
        try{

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
                return help_resource_not_found();
            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function createProductVariations(Request $request, $product_id)
    {
        try{

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
                return help_not_authorized();
            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteProduct( Request $request, $product_id )
    {
        try{

            //  Get the product
            $product = \App\Product::where('id', $product_id)->first() ?? null;

            //  Check if the product exists
            if ($product) {

                //  Check if the user is authourized to permanently delete the product
                if ($this->user && $this->user->can('forceDelete', $product)) {

                    //  Delete the product
                    $product->delete();

                    //  Return nothing
                    return response()->json(null, 200);

                } else {

                    //  Not Authourized
                    return help_not_authorized();

                }

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }

    }
}
