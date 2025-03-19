<?php

namespace App;
use App\Traits\Uuids;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;



class SpoCharityFundApplication extends Model implements Auditable
{
    use HasFactory;
    //use Uuids;
    use UuidsRefs;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    public $prefix = 'SI';
    protected $fillable = [
        'user_id','status','form_expiry'
    ];
    protected $table='spo_charity_fund_application';

    public function individual()
    {
        return $this->belongsTo(Individual::class,'user_id','user_id');
    }

    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }
}
