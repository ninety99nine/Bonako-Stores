<?php

namespace App\Traits;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\MobileVerification as MobileVerificationResource;
use App\Http\Resources\MobileVerifications as MobileVerificationsResource;

trait MobileVerificationTraits
{
    public $item_line = null;

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
                return new MobileVerificationsResource($collection);

            // If this instance is not a collection
            }elseif($this instanceof \App\MobileVerification){

                //  Transform the single instance
                return new MobileVerificationResource($this);

            }else{

                return $collection ?? $this;

            }

        } catch (\Exception $e) {

            throw($e);

        }
    }

    public function verifyMobileVerificationCode($mobile_number, $verification_code, $type){

        //  Search for matching verification codes
        $mobile_verification_record = $this->searchByMobileAndCodeAndType($mobile_number, $verification_code, $type)->first();

        //  If we have a matching verification code
        if( $mobile_verification_record ){

            //  Delete account registration verification codes
            $this->searchByMobileAndType($mobile_number, $type)->delete();

            //  Return the verified datetime
            return [
                'mobile_verification_record' => $mobile_verification_record,
                'mobile_number_verified_at' => \Carbon\Carbon::now()
            ];

        }else{

            //  Invalid verification code. Throw a validation error
            throw ValidationException::withMessages(['verification_code' => 'Invalid verification code']);

        }
    }

    public function getMobileVerificationType($type){

        $types = ['account_ownership', 'password_reset', 'order_delivery_confirmation'];

        if(collect($types)->contains($type)){
            return $type;
        }else{
            return 'unkwown';
        }

    }

    public function convertMobileToMsisdn($mobile){
        return '267'.preg_replace("/^267/", "$1", $mobile);
    }

    public function removeMobileExt($mobile){
        return preg_replace("/^267/", "$1", $mobile);
    }

}
