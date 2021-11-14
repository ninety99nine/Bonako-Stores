<?php

namespace App;

use App\Traits\CommonTraits;
use App\Traits\MobileVerificationTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MobileVerification extends Model
{
    use MobileVerificationTraits, CommonTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'code', 'mobile_number', 'meta_data'
    ];

    /**
     *  Scope:
     *  Returns mobile verifications that are being searched
     */
    public function scopeSearchByCode($query, $code)
    {
        $code = str_replace(' ', '', $code);

        return $query->where('code', $code);
    }

    /**
     *  Scope:
     *  Returns mobile verifications that are being searched
     */
    public function scopeSearchByMobileAndCode($query, $mobile_number, $code)
    {

        //  Remove spaces from the search term
        $code = str_replace(' ', '', $code);
        $mobile_number = str_replace(' ', '', $mobile_number);

        return $query->where([
                ['mobile_number', 'like', "%".$mobile_number."%"],
                ['code', $code],
            ])->where([
                ['mobile_number', 'like', "%267".$mobile_number."%"],
                ['code', $code],
            ]);
    }

    /**
     *  Scope:
     *  Returns mobile verifications of a given type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /** ATTRIBUTES
     *
     *  Note that the "resource_type" is defined within CommonTraits
     *
     */
    protected $appends = [
        'resource_type'
    ];

}
