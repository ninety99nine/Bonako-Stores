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
     * The table associated with the model.
     *
     * @var string
     */
    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'code', 'mobile_number', 'metadata'
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
            ])->orWhere([
                ['mobile_number', 'like', "%267".$mobile_number."%"],
                ['code', $code],
            ]);
    }

    /**
     *  Scope:
     *  Returns mobile verifications that are being searched
     */
    public function scopeSearchByMobileAndCodeAndType($query, $mobile_number, $code, $type)
    {
        //  Remove spaces from the search term
        $code = str_replace(' ', '', $code);
        $mobile_number = str_replace(' ', '', $mobile_number);

        return $query->where([
                ['mobile_number', 'like', "%".$mobile_number."%"],
                ['type', $type],
                ['code', $code],
            ])->where([
                ['mobile_number', 'like', "%267".$mobile_number."%"],
                ['type', $type],
                ['code', $code],
            ]);
    }

    /**
     *  Scope:
     *  Returns mobile verifications that are being searched
     */
    public function scopeSearchByMobileAndType($query, $mobile_number, $type)
    {
        //  Remove spaces from the search term
        $type = str_replace(' ', '', $type);
        $mobile_number = str_replace(' ', '', $mobile_number);

        return $query->where([
                ['mobile_number', 'like', "%".$mobile_number."%"],
                ['type', $type],
            ])->where([
                ['mobile_number', 'like', "%267".$mobile_number."%"],
                ['type', $type],
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

    public function getMetadataAttribute($value)
    {
        return json_decode($value, true);
    }

}
