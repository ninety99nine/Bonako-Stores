<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the specified user's id
        $user_id = $request->route('user_id');

        //  If the user id was not provided
        if( !$user_id && auth('api')->user() ){

            //  Get the authenticated users id
            $user_id = auth('api')->user()->id;

        }

        if( $user_id ){

            //  Get the user
            $this->user = \App\User::where('id', $user_id)->first() ?? null;
    
            //  Check if the user exists
            if ( !$this->user ) {
    
                //  Not Found
                return help_resource_not_fonud();
    
            }

        }else{

            $this->user = null;

        }
    }
    
    public function getUser(Request $request)
    {
        //  Check if the current auth user is authourized to view this user resource
        if ($this->user->can('view', $this->user)) {

            //  Return an API Readable Format of the User Instance
            return $this->user->convertToApiFormat();

        } else {

            //  Not Authourized
            return help_not_authorized();

        }
    }

    public function getUserStores(Request $request)
    {
        //  Get the user stores
        $stores = $this->user->stores()->latest()->paginate() ?? null;

        //  Check if the user stores exist
        if ($stores) {

            //  Check if the current auth user is authourized to view this user stores resource
            if ($this->user->can('view', $this->user)) {
                
                //  Return an API Readable Format of the Store Instance
                return ( new \App\Store() )->convertToApiFormat($stores);

            } else {

                //  Not Authourized
                return help_not_authorized();

            }
            
        } else {

            //  Not Found
            return help_resource_not_fonud();

        }
    }

    public function updateUser(Request $request, $id)
    {
        //
    }
    
    public function destroyUser($id)
    {
        //
    }
}
