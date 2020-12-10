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
                    return help_resource_not_fonud();
                }
            } else {
                //  Get the authenticated user
                $this->user = auth('api')->user();
            }
        } catch (\Exception $e) {
            return help_handle_exception($e);
        }
    }

    public function getUser(Request $request)
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
            //  Check if the current auth user is authourized to view this user resource
            if (auth('api')->user()->can('view', $this->user)) {

                $type = $request->input('type');
                $limit = $request->input('limit');
                $search_term = $request->input('search');

                //  Get the stores
                $stores = $this->user->getStores($type, $limit, $search_term);

                //  Paginate stores
                $stores = $stores->paginate($limit) ?? [];
    
                //  Return an API Readable Format of the Store Instance
                return ( new \App\Store() )->convertToApiFormat($stores);

            } else {
                //  Not Authourized
                return help_not_authorized();
            }
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
}
