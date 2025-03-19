<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use Uuids;

    public function state()
    {
        return $this->belongsTo(State::class,'id','state_id');
    }
    public function postalCodes()
    {
        return $this->hasMany(PostalCode::class,'city_id','id');
    }
}
