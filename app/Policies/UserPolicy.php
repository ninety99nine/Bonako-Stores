<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Authorize any action on this given policy if the user
     * is a super admin.
     */
    public function before($user, $ability)
    {
        try {

            /** Note that this will run before any other checks. This means is we return true we will be authorized
             *  for every action. However be aware that if we return false here, then we are also not authorizing
             *  all other methods. We must be careful here, we only return true if the user is a "Super Admin"
             *  but nothing is they are not, since we want other methods to run their own local checks.
             */
            if($user->isSuperAdmin()) return true;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view all users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAll(User $user)
    {
        try {

            return true;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        try {

            //  Any Authenticated user can view this user
            return auth('api')->user() ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        try {

            //  Any Authenticated user can create a user
            return ($user instanceof \App\User) ? true : false;

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        try {

            //  Only the Account Owner can update this user account
            return $model->isAccountOwner($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        try {

            //  Only the Account Owner can delete this user account
            return $model->isAccountOwner($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        try {

            //  Only the Account Owner can restore this user account
            return $model->isAccountOwner($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        try {

            //  Only the Account Owner can force delete this user account
            return $model->isAccountOwner($user->id);

        } catch (\Exception $e) {

            throw($e);

        }
    }
}
