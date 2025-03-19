<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use Uuids;
    public function getSelfieAttribute()
    {
        return asset('images/group.png');
    }
}
