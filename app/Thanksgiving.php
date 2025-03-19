<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Helpers\Enum;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thanksgiving extends Model
{
    use Uuids,SoftDeletes;

    protected $guarded = [];
    protected $visible = ['uuid', 'type', 'percentage'];

    public function getTypeAttribute($value){
        if($value === 'charity')
            return __('mobile.charity_insurance');
        return $value;
    }

    public function coverages(){
        return $this->belongsToMany(Coverage::class, 'coverage_thanksgivings');
    }

    public function credits(){
        return $this->morphMany(Credit::class, 'typeable','type','type_item_id');
    }
}
