<?php

namespace App\Traits;

use Twilio;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Sms as SmsResource;
use App\Http\Resources\Smses as SmsesResource;
use Illuminate\Validation\ValidationException;

trait SmsTraits
{
    public $sms = null;

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
                return new SmsesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Sms){

                //  Transform the single instance
                return new SmsResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new sms
     */
    public function createResource($data = [], $model = null, $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Verify permissions
            $this->handlePermissions('CREATE', $user);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            /**
             *  Create a new resource
             */
            $this->sms = $this->create($template);

            //  If created successfully
            if ($this->sms) {

                //  If we have an owning model
                if( $model ){

                    //  Update the sms origin id and origin type
                    $this->sms->update([
                        'origin_id' => $model->id,
                        'origin_type' => $model->resource_type,
                    ]);

                }

                //  Return an API Readable Format
                return $this->sms;

            }

        } catch (\Exception $e) {

            throw($e);

        }

    }

    public function sendSms($message = null, $mobile_number = null)
    {
        try {

            /*************************************
             * SEND AN SMS TO THE MOBILE NUMBER  *
             ************************************/

            //  Get message
            $message = $this->message ?? $this->message;

            //  Get mobile number
            $mobile_number = $this->mobile_number ?? $this->mobile_number;

            /***
             *  Send an SMS to the user
             *
             *  Twilio requires that the number must have the "+" prefix
             *  before the number e.g +26771234567
             */
            //$response = Twilio::message('+'.$mobile_number, $message);

            //  Update the SMS record status
            $this->update(['status' => 'success']);

            //return $response;


        } catch (\Exception $e) {

            //  Update the SMS record status
            $this->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            //  throw($e);

        }
    }

    public function handlePermissions($type, $user = null)
    {
        //  By default the user is allowed
        $hasPermission = true;

        //  If creating a resource
        if( $type == 'CREATE' ) {

            //  Check if the user is authourized to create the resource
            if (!$user || !$user->can('create', Sms::class)) {

                $hasPermission = false;

            }

        }

        //  If does not have permission
        if( $hasPermission == false ){

            //  Return "Not Authourized" Error
            return help_not_authorized();

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
                'message' => 'required|string|min:1|max:160',
                'mobile_number' => 'required|regex:/^[0-9]+$/i'
            ];

            //  Set validation messages
            $messages = [
                'message.required' => 'The message is required e.g My message',
                'message.string' => 'The message must be a valid string e.g My message',
                'message.min' => 'The message must be atleast 1 character long',
                'message.max' => 'The message must not be more than 160 characters long',

                'mobile_number.required' => 'Enter a valid mobile number containing only digits e.g 26771234567',
                'mobile_number.regex' => 'Enter a valid mobile number containing only digits e.g 26771234567'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
