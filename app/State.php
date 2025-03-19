<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use Uuids;

    public function cities()
    {
        return $this->hasMany(City::class,'state_id','id');
    }
    //
}
