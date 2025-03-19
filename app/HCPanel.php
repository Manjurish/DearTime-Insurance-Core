<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class HCPanel extends Model
{
    use Uuids;
    protected $visible = ['uuid','name','address','city','state','post_code','phone','fax','longitude','latitude','distance'];
}
