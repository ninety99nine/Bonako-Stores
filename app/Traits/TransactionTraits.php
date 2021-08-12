<?php

namespace App\Traits;

use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\Transactions as TransactionsResource;

trait TransactionTraits
{
    public $transaction = null;

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
                return new TransactionsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\Transaction){

                //  Transform the single instance
                return new TransactionResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new transaction
     */
    public function createResource($data = [], $model = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Validate the data
            $this->createResourceValidation($data);

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
            $this->transaction = $this->create($template);

            //  If created successfully
            if ( $this->transaction ) {

                //  Generate a new transaction number
                $this->transaction = $this->transaction->generateResourceNumber();

                //  If we have an owning model
                if( $model ){

                    //  Update the transaction owner id and owner type
                    $this->transaction->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                    ]);

                }

                //  Generate the resource creation report
                $this->transaction->generateResourceCreationReport($model);

                //  Return a fresh instance
                return $this->transaction->fresh();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a list of transactions
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

                //  Set the transactions to this eloquent builder
                $transactions = $builder;

            }else{

                //  Get the transactions
                $transactions = \App\transaction::orderBy('arrangement');

            }

            //  Filter the transactions
            $transactions = $this->filterResources($data, $transactions);

            //  Return transactions
            return $this->collectionResponse($data, $transactions, $paginate, $convert_to_api_format);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method returns a single transaction
     */
    public function getResource($id)
    {
        try {

            //  Get the resource
            $transaction = \App\transaction::where('id', $id)->first() ?? null;

            //  If exists
            if ($transaction) {

                //  Return transaction
                return $transaction;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a transaction creation report
     */
    public function generateResourceCreationReport($model)
    {
        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceCreationReport($this, [
            'type' => $this->type,
            'amount' => $this->amount['amount'],
            'payment_method_id' => $this->payment_method_id
        ]);
    }

    /**
     *  This method generates a new transaction number
     */
    public function generateResourceNumber()
    {
        try {

            /**
             *  Generate a unique transaction number.
             *
             *  Get the transaction id, and Pad the left side with leading "0"
             *  e.g 123 = 00123, 1234 = 01234, 12345 = 12345
             */
            $number = str_pad($this->id, 5, 0, STR_PAD_LEFT);

            //  Set the unique transaction number
            $this->update(['number' => $number]);

            //  Return fresh instance
            return $this->fresh();

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
                /**
                 *  Note that the amount can be a Float or Array value that has the money format,
                 *  therefore we must make sure our validation can cater for both scenerios. For
                 *  now the validation only support a Float value so we are disabling it until
                 *  we can offer support for both data types.
                 */
                //  'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'payment_method_id' => 'required|regex:/^[0-9]+$/i'
            ];

            //  Set validation messages
            $messages = [
                'amount.required' => 'The amount is required to create a transaction',
                'amount.regex' => 'The amount must be a valid number e.g 100.00',

                'payment_method_id.required' => 'The payment method id is required to create a transaction',
                'payment_method_id.regex' => 'The payment method id must be a valid number e.g 1'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
