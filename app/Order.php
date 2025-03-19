<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Helpers\Enum;
use App\Traits\Uuids;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use UuidsRefs;
    public $prefix = 'OR';
    protected $guarded = [];
    protected $hidden = ['id','payer_id'];

    public function coverages(){
        return $this->belongsToMany(Coverage::class, 'coverage_orders');
    }
    public function payer()
    {
        return $this->belongsTo(User::class,'payer_id');
    }

    public function transactions(){
        return $this->hasMany(Transaction::class,'order_id');
    }

    public function scopeSuccessfulTransaction($query){
        $query->transactions->where('success', 1)->latest()->first();
    }

    public function parent()
    {
        return $this->hasOne(Order::class, 'id','parent_id');
    }

    public function credit(){
        return $this->hasMany(Credit::class);
    }

    public function getCreditByThanksgiving($thanksgivingType){
        return $this->credit()->where('type',Enum::CREDIT_TYPE_THANKS_GIVING)->whereHas('thanksgiving',function ($q) use ($thanksgivingType) {
            $q->where('type',$thanksgivingType);
        });
    }

}
