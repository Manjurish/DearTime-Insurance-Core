<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use Uuids;
    protected $hidden = ['id','created_at','updated_at','deleted_at'];
}
