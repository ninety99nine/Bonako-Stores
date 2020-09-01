<?php

namespace App\Traits;

use DB;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\Products as ProductsResource;

trait ProductTraits
{
    public $product = null;
    public $request = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($products = null)
    {
        if( $products ){
                
            //  Transform the multiple instances
            return new ProductsResource($products);

        }else{
            
            //  Transform the single instance
            return new ProductResource($this);

        }
    }

    /*  This method creates a new product
     */
    public function initiateCreate( $request )
    {   
        //  Set the request variable
        $this->request = $request;

        //  Validate the request
        $validation_data = $request->validate([
            'name' => 'required'
        ]);

        //  Set the template
        $template = [
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'user_id' => auth('api')->user()->id,
            'online' => $request->input('online'),
            'store_id' => $request->input('store_id'),
            'description' => $request->input('description'),
            'cost_per_item' => $request->input('cost_per_item'),
            'unit_sale_price' => $request->input('unit_sale_price'),
            'unit_regular_price' => $request->input('unit_regular_price')
        ];

        try {
            
            /*
             *  Create new a product, then retrieve a fresh instance
             */
            $this->product = $this->create($template)->fresh();

            //  If created successfully
            if ($this->product) {

                $this->assignProductToLocation();

                //  Return a fresh instance
                return $this->product;

            }

        } catch (\Exception $e) {

            //  Throw a validation error
            throw ValidationException::withMessages(['general' => $e->getMessage()]);
            
        }
    }

    public function assignProductToLocation()
    {
        if( $this->request->input('location_id') ){

            //  Get the location we want to place this product
            $location = \App\Location::where('id', $this->request->input('location_id') )->first();

            //  Get all the current product allocations by order of arrangement
            $product_allocations = DB::table('location_product')->where('location_id', $location->id)->orderBy('arrangement')->get();

            //  Create a new product allocation and add the product we just created as the first on the list
            $new_product_allocations[] = [
                'arrangement' => 1,                   
                'created_at' => DB::raw('now()'),                       
                'updated_at' => DB::raw('now()'),
                'product_id' => $this->product->id,                
                'location_id' => $location->id
            ];

            /*  Get all the current product allocations and add them in their original
             *  order but arranged after the new product we just created. Since the
             *  $key starts from 0, 1, 2, 3 ... we need to add "1" to allow 
             *  increments starting from 1, 2, 3 ...
             */
            foreach ($product_allocations as $key => $product_allocation) {
                $new_product_allocations[] = [
                    'arrangement' => ($key + 2),                   
                    'created_at' => DB::raw('now()'),                       
                    'updated_at' => DB::raw('now()'),              
                    'location_id' => $location->id,
                    'product_id' => $product_allocation->product_id, 
                ];
            }

            //  Delete all the current product allocations
            DB::table('location_product')->where('location_id', $location->id)->delete();

            //  Insert all the product allocations in their updated order of arrangement
            DB::table('location_product')->insert($new_product_allocations);

        }
    }
    
}
