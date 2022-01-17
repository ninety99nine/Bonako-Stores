<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait CommonTraits
{
    /**
     *  This method updates the existing cart using already
     *  existing item lines and coupon lines
     */
    public function setResourceOwner($model)
    {
        $this->update([
            'owner_id' => $model->id,
            'owner_type' => $model->resource_type,
        ]);
    }
    /**
     *  This method updates the existing cart using already
     *  existing item lines and coupon lines
     */
    public function sendFirebaseCloudMessagingNotification($firebase_device_tokens = [], $remote_message = [])
    {
        try {

            /**
             *  $remote_message structure:
             *  -------
             *
             *  [
             *      'notification' => [
             *          'title': "Weather Warning!",
             *          'body': "A new weather warning has been issued for your location.",
             *          'imageUrl': "https://my-cdn.com/extreme-weather.png",
             *      ],
             *      [
             *          'data' => [
             *              'order': [ ... ],
             *              ...
             *          ],
             *      ]
             *  ]
             */
            if( is_array($firebase_device_tokens) && !empty($firebase_device_tokens) ){

                $method = 'POST';
                $url = config('app.FIREBASE_CLOUD_MESSAGING_URL');
                $serverKey = config('app.FIREBASE_SERVICE_ACCOUNT_SERVER_KEY');
                //  $tokens = ['fxOBPbwQTG2-nuiBopeNZP:APA91bE84NSXOslH_jSa45bAPrvYYPUA4t2Qm8PGpeXj1rqt00NEkZ6n7kDz5dO-I93-oMK-qyEhw5i8rRT9OJXDfyRKNWB0lJ9tt12yswNd7ZuNr9qLtd4WPI1j--8buoRGiSpdbYaX'];

                //  Declare the Request Options Array
                $request_options = [];

                //  Set the Request Headers
                $request_options['headers'] = [
                    'Authorization' => 'key='.$serverKey,
                    'Content-Type' => 'application/json',
                ];

                //  Set the Request Post Data
                $request_options['json'] = [
                    "registration_ids" => $firebase_device_tokens,
                    "notification" => $remote_message['notification'] ?? null,
                    "data" => $remote_message['data'] ?? null,
                ];

                //  Disable Request SSL certificate validation
                $request_options['verify'] = false;

                //  Create a new Http Guzzle Client
                $httpClient = new \GuzzleHttp\Client();

                //  Perform the Http request to send the Cloud Messaging Notifications
                $httpClient->request($method, $url, $request_options);

            }

        } catch (\Throwable $th) {

            //  Handle the throwable error

        }
    }

    /**
     *  This method generates a resource payment short code
     */
    public function generateResourcePaymentShortCode($data = [], $user)
    {
        //  Set the model
        $model = $this;

        //  Generate and return payment short code
        return ( new \App\ShortCode() )->generatePaymentShortCode($data, $model, $user)->convertToApiFormat();
    }

    /**
     *  This method generates a resource payment short code
     */
    public function generateResourceVisitShortCode($data = [], $user)
    {
        //  Set the model
        $model = $this;

        //  Generate and return visit short code
        return ( new \App\ShortCode() )->generateVisitShortCode($data, $model, $user)->convertToApiFormat();
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
