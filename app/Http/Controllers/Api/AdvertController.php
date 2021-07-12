<?php

namespace App\Http\Controllers\Api;

use App\Advert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvertController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        //  Get the authenticated user
        $this->user = auth('api')->user();
    }

    public function createAdvert(Request $request)
    {
        try {

            //  Return a new advert
            return (new Advert)->createResource($request, $this->user)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function updateAdvert(Request $request, $advert_id)
    {
        try {

            //  Get the advert
            $advert = Advert::where('id', $advert_id)->first() ?? null;

            //  Check if the advert exists
            if ($advert) {

                //  Return the updated advert
                return $advert->updateResource($request, $this->user)->convertToApiFormat();

            } else {

                //  Not Found
                return help_resource_not_found();

            }

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getAdverts(Request $request)
    {
        try {

            //  Return a list of adverts
            return (new Advert)->getResources($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function getAdvert($advert_id)
    {
        try {

            //  Return a single advert
            return (new Advert)->getResource($advert_id)->convertToApiFormat();

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function deleteAdvert($advert_id)
    {
        try {

            //  Delete the advert
            return (new Advert)->getResource($advert_id)->deleteResource($this->user);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }

    public function arrangeAdverts(Request $request)
    {
        try {

            //  Arrange adverts
            return (new Advert())->reorderAdverts($request);

        } catch (\Exception $e) {

            return help_handle_exception($e);

        }
    }
}
