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
    public function scopeSearch($query, $searchTerm)
    {
        return $query->whereHas('code', function (Builder $query) use ($searchTerm) {
            $query->search($searchTerm);
        });
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
