<?php

namespace App\Traits;

use App\Http\Resources\DeliveryLine as DeliveryLineResource;

trait DeliveryLineTraits
{
    public $request = null;
    public $delivery_line = null;

    /**
     *  This method transforms a single model instance
     */
    public function convertToApiFormat($collection = null)
    {
        try {

            //  Transform the single instance
            return new DeliveryLineResource($this);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new delivery line
     */
    public function createResource($data = [])
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the template with the resource fields allowed
            $template = collect($data)->only($this->getFillable())->toArray();

            /**
             *  Create a new resource
             */
            $this->delivery_line = $this->create($template);

            //  If created successfully
            if ( $this->delivery_line ) {

                //  Return delivery line
                return $this->delivery_line;

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
}
