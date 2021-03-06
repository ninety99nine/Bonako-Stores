<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait CommonTraits
{
    /**
     *  This method generates a resource payment short code
     */
    public function generateResourcePaymentShortCode($user)
    {
        //  Set the model
        $model = $this;

        //  Generate and return payment short code
        return ( new \App\ShortCode() )->generatePaymentShortCode($model, $user)->convertToApiFormat();
    }
    /**
     *  This method generates a resource payment short code
     */
    public function generateResourceVisitShortCode($user)
    {
        //  Set the model
        $model = $this;

        //  Generate and return visit short code
        return ( new \App\ShortCode() )->generateVisitShortCode($model, $user)->convertToApiFormat();
    }

    /**
     *  This method expires the resource payment short code
     */
    public function expirePaymentShortCode()
    {
        try {

            //  Expire payment short code
            $this->paymentShortCode()->update([
                'expires_at' => Carbon::now()
            ]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method expires the resource short codes
     */
    public function expireShortCodes()
    {
        try {

            //  Expire short codes
            $this->shortCodes()->update([
                'expires_at' => Carbon::now()
            ]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

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

    /**
     *  This method validates fetching multiple resources
     */
    public function getResourcesValidation($data = [])
    {
        try {

            //  Set validation rules
            $rules = [
                'limit' => 'sometimes|required|numeric|min:1|max:100',
            ];

            //  Set validation messages
            $messages = [
                'limit.required' => 'Enter a valid limit containing only digits e.g 50',
                'limit.regex' => 'Enter a valid limit containing only digits e.g 50',
                'limit.min' => 'The limit attribute must be a value between 1 and 100',
                'limit.max' => 'The limit attribute must be a value between 1 and 100',
            ];

            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

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

    public function collectionResponse($data = [], $builder = null, $paginate = true, $convert_to_api_format = true)
    {
        try {

            //  Set the pagination limit e.g 15
            $limit = $data['limit'] ?? null;

            //  If we should paginate the builder
            if( $paginate === true ){

                //  Paginate builder
                $modified_builder = $builder->paginate($limit);

            //  If we should not paginate the builder
            }elseif( $paginate === false ){

                //  Get builder
                $modified_builder = $builder->get();

            //  If we should do nothing
            }elseif( $paginate === null ){

                //  Return builder
                return $builder;

            }

            //  If we should convert the builder to an API Readable Format
            if( $convert_to_api_format === true ){

                //  Get the model class name e.g "App\User"
                $class = get_class($this);

                //  Initialize a new model object e.g (new App\User)
                $model = (new $class);

                //  Convert to API Readable Format -
                return $model->convertToApiFormat($modified_builder);

            }else{

                //  Return builder
                return $modified_builder;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  Returns the currency and symbol
     *
     *  @param  string  $code e.g "BWP"
     */
    public function unpackCurrency($code = null)
    {
        try {

            $currencies = [
                'BWP' => 'P'
            ];

            $symbol = $currencies[$code] ?? '';

            return [
                'code' => $code,
                'symbol' => $symbol
            ];

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  Returns the amount in money format
     *
     *  @param  string  $currency e.g "BWP"
     *  @param  float     $amount e.g 5.00
     */
    public function convertToMoney($currency = null, $amount = 0)
    {
        try {

            //  Set the currency symbol
            $symbol = $currency['symbol'] ?? '';

            //  Convert amount to money format
            $money = number_format($amount, 2, '.', ',');

            return [
                'currency_money' => $symbol.$money,
                'money' => $money,
                'amount' => $amount
            ];

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
