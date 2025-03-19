<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QR extends Model
{
    use Uuids;
    protected $visible = ['uuid', 'expiry'];
    protected $dates = ['expiry'];

    public function getExpiryAttribute($value){
        return Carbon::now()->diffInSeconds(Carbon::parse($value));
    }
}
