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

}
