<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        try {

            //  Get the specified user's id
            $user_id = $request->route('user_id');

            //  If we have a specified user id
            if ($user_id) {

                //  Get the specified user using the provided user id
                $this->user = \App\User::find($user_id) ?? null;

                //  Check if the user exists
                if (!$this->user) {

                    //  Not Found
                    return help_resource_not_found();

                }

            } else {

                //  Get the authenticated user
                $this->user = auth('api')->user();

            }

        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getUser()
    {
        try {
            //  Check if the current auth user is authourized to view this user resource
            if (auth('api')->user()->can('view', $this->user)) {

                //  Return an API Readable Format of the User Instance
                return $this->user->convertToApiFormat();

            } else {

                //  Not Authourized
                return help_not_authorized();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStores(Request $request)
    {
        try {

            //  Return a list of stores
            return $this->user->getResourceStores($request);


        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStore($store_id)
    {
        try {

            //  Return a single store
            return $this->user->getResourceStore($store_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStoreLocations(Request $request, $store_id)
    {
        try {

            //  Return a list of locations
            return $this->user->getResourceStoreLocations($request, $store_id);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStoreLocation($store_id, $location_id)
    {
        try {

            //  Return a single location
            return $this->user->getResourceStoreLocation($store_id, $location_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStoreLocationPermissions($store_id, $location_id)
    {
        try {

            //  Return a single location
            return $this->user->getResourceStoreLocationPermissions($store_id, $location_id);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserStoreDefaultLocation($store_id)
    {
        try {

            //  Return a single location
            return $this->user->getResourceDefaultLocation($store_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateUserStoreDefaultLocation(Request $request, $store_id)
    {
        try {

            //  Update the default location
            return $this->user->updateResourceDefaultLocation($request, $store_id);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserAddresses(Request $request)
    {
        try {

            //  Return a list of stores
            return $this->user->getResourceAddresses($request);


        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function createUserAddress(Request $request)
    {
        try {

            //  Return created user address
            return $this->user->createResourceAddress($request)->convertToApiFormat();


        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getUserSubscriptions(Request $request)
    {
        try {

            //  Return a list of subscriptions
            return $this->user->getResourceSubscriptions($request);


        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateUser(Request $request, $id)
    {
    }

    public function destroyUser($id)
    {
    }

    public function checkMobileAccountExistence(Request $request, $mobile_number)
    {
        if ($mobile_number) {
            $account = \App\User::where('mobile_number', $mobile_number)->first();

            if ($account) {
                //  Return true that the account exists
                return response()->json(['account_exists' => true], 200);
            }
        }

        //  Return false that the account does not exist
        return response()->json(['account_exists' => false], 200);
    }

    public function searchUsersByMobileNumber(Request $request)
    {
        $mobile_number = $request->input('mobile_number') ?? null;

        if ($mobile_number) {

            $user = \App\User::searchMobile($mobile_number, true)->first();

            return [
                'exists' => $user ? true : false,
                'user' => $user ? $user->convertToApiFormat() : null
            ];

        }
    }
}
