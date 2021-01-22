<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\Transactions as TransactionsResource;

trait TransactionTraits
{
    public $request = null;
    public $transaction = null;

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($transactions = null)
    {
        if( $transactions ){

            //  Transform the multiple instances
            return new TransactionsResource($transactions);

        }else{

            //  Transform the single instance
            return new TransactionResource($this);

        }
    }

    /**
     *  This method creates a new transaction
     */
    public function createResource($request, $model = null)
    {
        try {

            //  Set the request variable
            $this->request = $request;

            //  Validate the request
            $this->handleValidation('CREATE');

            /**
             *  Retrieve the request values
             */
            $type = $request->input('type') ?? null;
            $amount = $request->input('amount') ?? null;
            $description = $request->input('description') ?? null;
            $payment_method_id = $request->input('payment_method_id') ?? null;

            //  Set the template
            $template = [

                /*  Basic Info  */
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'user_id' => auth('api')->user()->id,
                'payment_method_id' => $payment_method_id

            ];

            /**
             *  Create new a transaction, then retrieve a fresh instance
             */
            $this->transaction = $this->create($template)->fresh();

            //  If created successfully
            if ( $this->transaction ) {

                //  Generate a new transaction
                $this->generateTransactionNumber();

                //  If we have an owning model
                if( $model ){

                    //  Update the owning model
                    $model->update([
                        'transaction_id' => $this->transaction->id
                    ]);

                }

                //  Get a fresh instance
                $transaction = $this->transaction->fresh();

                //  Return an API Readable Format
                return $transaction->convertToApiFormat();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function generateTransactionNumber()
    {
        try {

            /** Generate a unique transaction number.
             *
             *  Get the transaction id, and Pad the left side with leading "0"
             *  e.g 123 = 00123, 1234 = 01234, 12345 = 12345
             */
            $number = str_pad($this->transaction->id, 5, 0, STR_PAD_LEFT);

            //  Set the unique transaction number
            $this->transaction->update(['number' => $number]);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function handleValidation($type)
    {
        try {

            if( $type == 'CREATE' ){

                $rules = [
                    'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                    'payment_method_id' => 'required|regex:/^[0-9]+$/i'
                ];

                $messages = [
                    'amount.required' => 'The amount is required to create a transaction',
                    'amount.regex' => 'The amount must be a valid number e.g 100.00',

                    'payment_method_id.required' => 'The payment method id is required to create a transaction',
                    'payment_method_id.regex' => 'The payment method id must be a valid number e.g 1'
                ];

            }

            //  Validate request
            $validator = Validator::make($this->request->all(), $rules, $messages);

            //  If the validation failed
            if ($validator->fails()) {

                //  Throw Validation Exception with validation errors
                throw ValidationException::withMessages(collect($validator->errors())->toArray());

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
