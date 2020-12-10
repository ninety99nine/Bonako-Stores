<?php

namespace App\Traits;

//  Resources
use DB;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Users as UsersResource;

trait UserTraits
{
    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($users = null)
    {
        if( $users ){
                
            //  Transform the multiple instances
            return new UsersResource($users);

        }else{
            
            //  Transform the single instance
            return new UserResource($this);

        }
    }

    public function getStores($type = null, $limit = null, $search_term = null)
    {
        //  Get the stores
        $stores = $this->stores();

        //  Get the authenticated users's ID
        $user_id = auth()->user()->id;
        
        if ($type === 'created') {
            //  Scope stores created by the user
            $stores = $stores->asOwner($user_id);
        } elseif ($type === 'shared') {
            //  Scope stores shared with the user
            $stores = $stores->asNonOwner($user_id);
        } elseif ($type === 'favourite') {
            //  Scope stores favourated by the user
            $stores = $stores->asFavourite($user_id);
        }

        //  If we need to search for specific stores
        if (!empty($search_term)) {
            $stores = $stores->search($search_term);
        }

        return $stores;
    }

    /**
     *  Checks if a given user is the owner of the user account
     */
   public function isAccountOwner($user_id)
   {
        return $this->id == $user_id;
   }

   /**
    *  Checks if a given user is a Super Admin.
    */
   public function isSuperAdmin()
   {
       return $this->account_type == 'superadmin';
   }

}
