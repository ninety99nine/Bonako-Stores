<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home as HomeResource;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** home()
     *
     *  1) Returns the API links for the non-authenticated 
     *     user that is logged in
     *
     *  2) Returns the API links for the authenticated 
     *     user that is logged in
     */
    public function home(Request $request)
    {
        try {

            //  Return the home Api Resource
            return new HomeResource($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
