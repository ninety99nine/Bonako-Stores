<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Http\Resources\ShortCode as ShortCodeResource;
use App\Http\Resources\ShortCodes as ShortCodesResource;

trait ShortCodeTraits
{
    public $store_id = null;
    public $location_id = null;

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

            //  Set the location id (if available)
            $location_id = $data['location_id'] ?? null;

            //  Search for a current active short code
            $current_short_code = $this->getCurrentResource($action, $model);

            //  If this is a payment short code
            if( $action == 'payment' ){

                //  Set expiry after 24 hours
                $expires_at = Carbon::now()->addHours(24)->format('Y-m-d H:i:s');

            //  If this is a visit short code and we have the model e.g "Store" or "InstantCart"
            }elseif( $action == 'visit' && $model ){

                //  Get the subscription with the longest time till expiry for the given model
                $subscription = \App\Subscription::where('owner_id', $model->id)->where('owner_type', $model->resource_type)->orderBy('end_at', 'desc')->first();

                //  Set expiry to the same time as the subscription end datetime
                $expires_at = Carbon::parse($subscription->end_at)->format('Y-m-d H:i:s');

            //  Otherwise
            }else{

                //  Set expiry after 24 hours
                $expires_at = Carbon::now()->addHours(24)->format('Y-m-d H:i:s');

            }

            //  If we have a current active short code
            if( $current_short_code ){

                //  Update the current short code datetime of expiry
                $current_short_code->update([
                    'expires_at' => $expires_at
                ]);

                //  Get a fresh instance of the current short code
                $short_code = $current_short_code->fresh();

                //  Generate the resource recycle report
                $short_code->generateResourceRecycledReport($model, ['previously_owned' => true]);

            }else{

                //  Search for any other available inactive short codes
                $available_short_code = $this->getAvailableResource($action, $model);

                //  If we have any available short code
                if( $available_short_code ){

                    //  Update the available short code owner and datetime of expiry
                    $available_short_code->update([
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                        'expires_at' => $expires_at,
                        'user_id' => $user->id
                    ]);

                    //  Get a fresh instance of the available short code
                    $short_code = $available_short_code->fresh();

                    //  Generate the resource recycle report
                    $short_code->generateResourceRecycledReport($model, ['previously_owned' => false]);

                }else{

                    //  Generate a new code
                    $code = $this->generateResourceCode($action, $model);

                    //  Set the short code template
                    $template = [
                        'code' => $code,
                        'action' => $action,
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                        'expires_at' => $expires_at,
                        'user_id' => $user->id
                    ];

                    /**
                     *  Create a new resource
                     */
                    $short_code = $this->create($template);

                    //  Generate the resource creation report
                    $short_code->generateResourceCreationReport($model);

                }

            }

            //  Return the short code
            return $short_code;

        } catch (\Exception $e) {

            throw($e);

        }

    }

    /**
     *  This method generates a short code creation report
     */
    public function generateResourceCreationReport($model = null)
    {
        //  Set the store and location id
        $this->setStoreAndLocationID($model);

        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceCreationReport($this, $this->resourceReportMetadata(), $this->store_id, $this->location_id);
    }

    /**
     *  This method generates a short code recycle report
     */
    public function generateResourceRecycledReport($model = null, $additionalMetadata = [])
    {
        //  Set the store and location id
        $this->setStoreAndLocationID($model);

        //  Generate the resource creation report
        ( new \App\Report() )->generateResourceRecycledReport($this, $this->resourceReportMetadata($additionalMetadata), $this->store_id, $this->location_id);
    }

    public function setStoreAndLocationID($model = null)
    {
        if( $model ){

            //  If this is a store
            if( $model->resource_type == 'store' ){

                $this->store_id = $model->id;

            }elseif( $model->resource_type == 'location' ){

                $this->location_id = $model->id;
                $this->store_id = $model->store->id;

            }elseif( $model->resource_type == 'instant_cart' ){

                $this->location_id = $model->location->id;
                $this->store_id = $model->location->store->id;

            }

        }
    }

    /**
     *  This method generates the report metadata
     */
    public function resourceReportMetadata($additionalMetadata = [])
    {
        $defaultMetadata = [
            'action' => $this->action,
            'owner_type' => $this->owner_type
        ];

        return array_merge($defaultMetadata, $additionalMetadata);
    }

    /**
     *  This method returns a single shortcode
     */
    public function searchResourceByCode($search_code)
    {
        try {

            //  Convert "001" to "1"
            $code = (int) $search_code;

            //  Search for shortcodes matching the given code
            $short_codes = $this->where('code', $code);

            //  If the search_code starts with double "0" e.g "0045"
            if( substr($search_code, 0, 2) === '00' ){

                //  Get the first matching payment shortcode
                $short_code = $short_codes->where('action', 'payment')->first();

            //  If the search_code starts with single "0" e.g "045"
            }elseif( substr($search_code, 0, 1) === '0' ){

                //  Get the first matching instant cart visit shortcode
                $short_code = $short_codes->where('action', 'visit')->where('owner_type', 'instant_cart')->first();

            //  If the search_code starts with natural number e.g "45"
            }else{

                //  Get the first matching store or location visit shortcode
                $short_code = $short_codes->where('action', 'visit')->whereIn('owner_type', ['store', 'location'])->first();

            }

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
     *  This method generates a resource payment short code
     */
    public function generatePaymentShortCode($model, $user)
    {
        try {

            return $this->generateResourceShortCode('payment', $model, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a resource visit short code
     */
    public function generateVisitShortCode($model, $user)
    {
        try {

            return $this->generateResourceShortCode('visit', $model, $user);

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  This method generates a resource short code
     */
    public function generateResourceShortCode($action, $model, $user)
    {
        try {

            $data = [

                //  Set the action on the data
                'action' => $action

            ];

            /**
             *  Create new a short code resource
             */
            return $this->createResource($data, $model, $user);

        } catch (\Exception $e) {

            throw($e);

        }
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
