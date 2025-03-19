<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Address extends Model
{
//    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['id','created_at','updated_at','type','deleted_at'];
    protected $appends = ['AddressCity','AddressState','AddressPostcode'];

    public function getAddressCityAttribute()
    {
        return $this->cityDetail()->select('uuid','name')->first();
    }
    public function getAddressStateAttribute()
    {
        return $this->stateDetail()->select('uuid','name')->first();
    }
    public function getAddressPostcodeAttribute()
    {
        return $this->postcodeDetail()->select('uuid','name')->first();
    }

    public function cityDetail()
    {
        return $this->belongsTo(City::class,'city','uuid');
    }
    public function stateDetail()
    {
        return $this->belongsTo(State::class,'state','uuid');
    }
    public function postcodeDetail()
    {
        return $this->belongsTo(PostalCode::class,'postcode','uuid');
    }
}
