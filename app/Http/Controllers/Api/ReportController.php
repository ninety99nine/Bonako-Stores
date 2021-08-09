<?php

namespace App\Http\Controllers\Api;

use App\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function getReports(Request $request)
    {
        try {

            //  Return a list of Reports
            return (new Report)->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getReportStatistics(Request $request)
    {
        try {

            $store_id = $request->input('store_id');

            if( isset($store_id) && !empty($store_id) ){

                //  Return Store report statistics
                return (new \App\Store())->getResource($store_id)->getResourceStatistics($request);

            }

            //  Return Report statistics
            return (new Report)->getResourceStatistics($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

}
