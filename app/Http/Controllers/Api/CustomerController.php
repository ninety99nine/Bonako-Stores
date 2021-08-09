<?php

namespace App\Http\Controllers\Api;

use App\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createCustomer(Request $request)
    {
        try {

            //  Create and return the customer
            return (new Customer())->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateCustomer(Request $request, $customer_id)
    {
        try {

            //  Return the updated customer
            return (new Customer())->getResource($customer_id)->updateResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getCustomer($customer_id)
    {
        try {

            //  Return the customer
            return (new Customer())->getResource($customer_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteCustomer($customer_id)
    {
        try {

            //  Delete the customer
            return (new Customer())->getResource($customer_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getCustomerOrders(Request $request, $customer_id)
    {
        try {

            //  Return a list of customer orders
            return (new Customer())->getResource($customer_id)->getResourceOrders($request, $this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
