<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ShortCode as ShortCodeResource;
use App\Http\Resources\ShortCodes as ShortCodesResource;

trait ShortCodeTraits
{

    /*  convertToApiFormat() method:
     *
     *  Converts to the appropriate Api Response Format
     *
     */
    public function convertToApiFormat($short_codes = null)
    {
        if ($short_codes) {

            //  Transform the multiple instances
            return new ShortCodesResource($short_codes);

        } else {

            //  Transform the single instance
            return new ShortCodeResource($this);

        }
    }

    /**
     *  This method creates a new short code
     */
    public function createResource($action, $model)
    {
        try {

            //  Search for a current active short code
            $current_short_code = $this->getCurrentShortCode($action, $model);

            //  If this is a payment short code
            if( $action == 'payment' ){

                //  Set expiry after 24 hours
                $expires_at = Carbon::now()->addHours(24)->format('Y-m-d H:i:s');

            //  If this is a visit store short code
            }elseif( $action == 'visit' && $model->resource_type == 'store' ){

                //  Get the subscription with the longest time till expiry
                $subscription = \App\Subscription::where('store_id', $model->id)->orderBy('end_at', 'desc')->first();

                //  Set expiry after at the same time as the subscription end datetime
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
                $available_short_code = $this->getAvailableShortCode($action, $model);

                //  If we have any available short code
                if( $available_short_code ){

                    //  Update the available short code owner and datetime of expiry
                    $available_short_code->update([
                        'owner_id' => $model->id,
                        'expires_at' => $expires_at
                    ]);

                //  Get a fresh instance of the available short code
                    $short_code = $available_short_code->fresh();

                }else{

                    //  Generate a new code
                    $code = $this->generateCode($action, $model);

                    //  Set the short code template
                    $template = [
                        'code' => $code,
                        'action' => $action,
                        'owner_id' => $model->id,
                        'owner_type' => $model->resource_type,
                        'expires_at' => $expires_at
                    ];

                    /**
                     *  Create new a short code
                     */
                    $short_code = $this->create($template);

                }

            }

            //  Return an API Readable Format
            return $short_code->convertToApiFormat();

        } catch (\Exception $e) {

            throw($e);

        }
    }

    /**
     *  Check if this model already has a valid short code.
     *  The short code must match the same Action, Resource
     *  ID and Resource Type.
     */
    public function getCurrentShortCode($action, $model)
    {
        return \App\ShortCode::where(['action' => $action, 'owner_id' => $model->id, 'owner_type' => $model->resource_type])
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    /**
     *  Check if we have any available short code to use.
     *  The short code must match the same action and
     *  resource type and must also have expired.
     */
    public function getAvailableShortCode($action, $model)
    {
        return \App\ShortCode::where(['action' => $action, 'owner_type' => $model->resource_type])
                    ->whereDate('expires_at', '<', Carbon::now())
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    public function generateCode($action, $model)
    {
        //  Count similar short codes
        $total = \App\ShortCode::where(['action' => $action, 'owner_type' => $model->resource_type])->count();

        //  The new code must be an increment of this total
        $code = ($total + 1);

        //  If this is a store resource type with an action equal to "visit"
        if( $model->resource_type == 'store' && $action == 'visit' ){

            /** Add 10 to offset so that every store related
             *  short code can always have a code starting
             *  from 10 and above.
             */
            $code += 10;

        }

        //  Return the code
        return $code;

    }

}
