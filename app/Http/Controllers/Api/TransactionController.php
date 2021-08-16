<?php

namespace App\Http\Controllers\Api;

use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function updateTransaction(Request $request, $transaction_id)
    {
        try {

            //  Return the updated transaction
            return (new Transaction())->getResource($transaction_id)->updateResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

}
