<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class SelfieMatch extends Model
{
    use Uuids;

    protected $casts = [
        'face' => 'array'
    ];
    //
    public function getPercentAttribute()
    {
        if(empty($this->similarity) && empty($this->individual_id)){
            return 0;
        }

        return $this->similarity;
    }
}
