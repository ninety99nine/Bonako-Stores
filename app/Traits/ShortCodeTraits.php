<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Http\Resources\ShortCode as ShortCodeResource;
use App\Http\Resources\ShortCodes as ShortCodesResource;

trait ShortCodeTraits
{

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
                return new ShortCodesResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\ShortCode){

                //  Transform the single instance
                return new ShortCodeResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method creates a new short code
     */
    public function createResource($data = [], $model, $user = null)
    {
        try {

            //  Extract the Request Object data (CommanTraits)
            $data = $this->extractRequestData($data);

            //  Verify permissions
            $this->createResourcePermission($user);

            //  Validate the data
            $this->createResourceValidation($data);

            //  Set the action
            $action = $data['action'];

            //  Search for a current active short code
            $current_short_code = $this->getCurrentResource($action, $model);

            //  If this is a payment short code
            if( $action == 'payment' ){

                //  Set expiry after 24 hours
                $expires_at = Carbon::now()->addHours(24)->format('Y-m-d H:i:s');

            //  If this is a visit store short code
            }elseif( $action == 'visit' && $model->resource_type == 'store' ){

                //  Get the subscription with the longest time till expiry
                $subscription = \App\Subscription::where('store_id', $model->id)->orderBy('end_at', 'desc')->first();

                //  Set expiry to the same time as the subscription end datetime
                $expires_at = Carbon::parse($subscription->end_at)->format('Y-m-d H:i:s');

            }

            //  If we have a current active short code
            if( $current_short_code ){

                //  Update the current short code datetime of expiry
                $current_short_code->update([
                    'expires_at' => $expires_at
                ]);

                //  Get a fresh instance of the current short code
                $short_code = $current_short_code->fresh();

            }else{

                //  Search for any other available inactive short codes
                $available_short_code = $this->getAvailableResource($action, $model);

                //  If we have any available short code
                if( $available_short_code ){

                    //  Update the available short code owner and datetime of expiry
                    $available_short_code->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                        'expires_at' => $expires_at
                    ]);

                    //  Get a fresh instance of the available short code
                    $short_code = $available_short_code->fresh();

                }else{

                    //  Generate a new code
                    $code = $this->generateResourceCode($action, $model);

                    //  Set the short code template
                    $template = [
                        'code' => $code,
                        'action' => $action,
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                        'expires_at' => $expires_at
                    ];

                    /**
                     *  Create a new resource
                     */
                    $short_code = $this->create($template);

                }

            }

            //  Return the short code
            return $short_code;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method returns a single shortcode
     */
    public function searchResourceByCode($code)
    {
        try {

            if( substr($code, 0, 2) === '00' ){

                $action = 'payment';

            }elseif( substr($code, 0, 1) === '0' ){

                $action = 'order';

            }else{

                $action = 'visit';

            }

            //  Convert "001" to "1"
            $code = (int) $code;

            //  Get the matching shortcode
            $short_code = $this->where('code', $code)->where('action', $action)->first();

            //  If exists
            if ($short_code) {

                //  Return shortcode
                return $short_code;

            } else {

                //  Return "Not Found" Error
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method will search and return a short code
     *  that matches the given action and owning model
     */
    public function getCurrentResource($action, $model)
    {
        $search = [
            'action' => $action,
            'owner_id' => $model->id,
            'owner_type' => $model->resource_type
        ];

        return \App\ShortCode::where($search)->latest()->first();
    }

    /**
     *  This method will search and return any available short code
     *  that is currently not in use. The short code must match the
     *  same action and resource type and must also have expired.
     */
    public function getAvailableResource($action, $model)
    {
        $search = [
            'action' => $action
        ];

        return \App\ShortCode::where($search)->where('expires_at', '<', Carbon::now())->latest()->first();
    }

    /**
     *  This method will generate a new unique code
     */
    public function generateResourceCode($action, $model)
    {
        $search = [
            'action' => $action
        ];

        //  Count the total number of similar short codes
        $total = \App\ShortCode::where($search)->count();

        //  The new code must be an increment of this total
        $code = ($total + 1);

        //  If this action is equal to "visit"
        if( $action == 'visit' ){

            /** Add 10 to offset so that every visit related
             *  short code can always has a code starting
             *  from 10 and above.
             */
            $code += 10;

        }

        //  Return the code
        return $code;

    }

    /**
     *  This method checks permissions for creating a new resource
     */
    public function createResourcePermission($user = null)
    {
        try {

            //  If the user is provided
            if( $user ){

                //  Check if the user is authourized to create the resource
                if ($user->can('create', ShortCode::class) === false) {

                    //  Return "Not Authourized" Error
                    return help_not_authorized();

                }

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

            $rules = [
                'action' => 'required|in:visit,payment',
            ];

            $messages = [
                'action.required' => 'The short code action is required',
                'action.in' => 'The short code action is incorrect'
            ];

            //  Method executed within CommonTraits
            $this->resourceValidation($data, $rules, $messages);

        } catch (\Exception $e) {

            throw($e);

        }
    }

}
