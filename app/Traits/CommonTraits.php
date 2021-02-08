<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait CommonTraits
{
    /**
     *  This method sets or creates a new Request Object
     */
    public function extractRequestData($resource = [])
    {
        //  If the resource is a valid Request Object
        if( ($resource instanceof \Illuminate\Http\Request) ){

            //  Return the Request Object data
            return $resource->all();

        //  If the resource is a valid non-empty Array
        }elseif( is_array($resource) && !empty($resource) ){

            //  Return the Array
            return $resource;

        }else{

            //  Return an empty Array
            return [];

        }
    }

    public function resourceValidation($data = [], $rules = [], $messages = [])
    {
        try {

            //  Validate request
            $validator = Validator::make($data, $rules, $messages);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function collectionResponse($data = [], $collection = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Set the pagination limit e.g 15
            $limit = $data['limit'] ?? null;

            //  If we should paginate the collection
            if( $paginate === true ){

                //  Paginate collection
                $modified_collection = $collection->paginate($limit);

            }else{

                //  Get collection
                $modified_collection = $collection->get();

            }

            //  If we should convert the collection to an API Readable Format
            if( $convert_to_api_format === true ){

                //  Get the model class name e.g "App\User"
                $class = get_class($this);

                //  Initialize a new model object e.g (new App\User)
                $model = (new $class);
                
                //  Convert to API Readable Format - 
                return $model->convertToApiFormat($modified_collection);

            }else{

                //  Return collection
                return $modified_collection;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }
    /*
     *  Returns the amount in money format
     */
    public function convertToMoney($amount = 0)
    {
        try {

            return number_format($amount, 2, '.', ',');

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /*
     *  Returns the resource type
     */
    public function getResourceTypeAttribute()
    {
        return strtolower(Str::snake(class_basename($this)));
    }
}
