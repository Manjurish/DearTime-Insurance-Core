<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Helpers\Enum;
use App\Traits\Uuids;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coverage extends Model
{
    use UuidsRefs;
    public $prefix = 'CG';
    protected $guarded = [];
    protected $visible = ['uuid', 'product_name', 'payment_term', 'payment_term_new', 'status','state', 'coverage','real_coverage', 'ownerProfile', 'covered_id','payment_monthly', 'payment_annually','diff','deductible','has_loading','annual_limit', 'existing_coverage', 'forecast_total_cover', 'cov_operation','id'];
    protected $appends = ['ownerProfile','payable','annual_limit','real_coverage', 'existing_coverage', 'forecast_total_cover', 'cov_operation'];

    protected static function bootUuidsRefs()
    {
        static::created(function ($model){

            $model->color = Helpers::getColor();
            $model->save();
            //cancel other coverages
            Coverage::where("owner_id",$model->owner_id)->where("payer_id",$model->payer_id)->where("covered_id",$model->covered_id)->where("product_id",$model->product_id)->where("status","Unpaid")->where("id","!=",$model->id)->update(['status'=>Enum::COVERAGE_STATUS_CANCELLED]);
        });

        static::saving(function ($model){
            if($model->isDirty('status')){
                if($model->status == Enum::COVERAGE_STATUS_GRACE_UNPAID
                    || $model->status == Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID
                    || $model->status == Enum::COVERAGE_STATUS_ACTIVE
                    || $model->status == Enum::COVERAGE_STATUS_ACTIVE_INCREASED)
                {
                    $model->state = Enum::COVERAGE_STATE_ACTIVE;
                }
                elseif($model->status == Enum::COVERAGE_STATUS_CANCELLED
                    || $model->status == Enum::COVERAGE_STATUS_EXPIRED
                    || $model->status == Enum::COVERAGE_STATUS_FULFILLED_GRACE
                    || $model->status == Enum::COVERAGE_STATUS_GRACE_TERMINATE
                    || $model->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID
                    || $model->status == Enum::COVERAGE_STATUS_INCREASE_TERMINATE
                    || $model->status == Enum::COVERAGE_STATUS_FULFILLED_INCREASE
                    || $model->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID
                    || $model->status == Enum::COVERAGE_STATUS_DECREASE_TERMINATE
                    || $model->status == Enum::COVERAGE_STATUS_FULFILLED_DECREASED
                    || $model->status == Enum::COVERAGE_STATUS_UNPAID
                    || $model->status == Enum::COVERAGE_STATUS_PAYMENT_TERMINATE
                ){
                    $model->state = Enum::COVERAGE_STATE_INACTIVE;
                }
            }

        });
    }

    public function scopeActive($q)
    {
        return $q->where("coverages.state",Enum::COVERAGE_STATE_ACTIVE);
    }

    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }

    public function thanksgivings(){
        return $this->belongsToMany(Thanksgiving::class,'coverage_thanksgivings');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function orders(){
        return $this->belongsToMany(Order::class, 'coverage_orders');
    }
    public function claims()
    {
        return $this->hasMany(Claim::class, 'coverage_id');
    }
    public function owner()
    {
        //TODO for group owner
        return $this->belongsTo(Individual::class, 'owner_id')->WithChild();
    }
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id','id');
    }
    public function covered()
    {
        return $this->belongsTo(Individual::class, 'covered_id')->WithChild();
    }
    public function parent()
    {
        return $this->hasOne(Coverage::class,'id', 'parent_id');
    }

    public function getOwnerProfileAttribute()
    {
        return ['name' => $this->owner->name ?? '',
            'photo' => !empty($this->owner) ? $this->owner->selfie() : null];
    }
    public function getPayableAttribute()
    {
        return $this->payment_term == 'monthly' ? $this->payment_monthly : $this->payment_annually;
    }
    public function getCoverageAttribute($value){
        if($this->product_name !== Enum::PRODUCT_NAME_MEDICAL )
            return $value;

        return $this->deductible;
//        return 100000 ;
    }
    public function getRealCoverageAttribute()
    {
        return $this->attributes['coverage'] ?? 0;
    }

    public function getExistingCoverageAttribute()
    {
        return $this->attributes['existing_coverage'] ?? 0;
    }

    public function getForecastTotalCoverAttribute()
    {
        return $this->attributes['forecast_total_cover'] ?? 0;
    }

    public function getCovOperationAttribute() {
        return $this->attributes['cov_operation'] ?? '';
    }

    public function calcDeductible(){
        $deductible = 0;
        switch ($this->coverage){
            case 0:
                $deductible = 0;
                break;
            case 1:
                $deductible=500;
                break;
            case 2:
                $deductible = 1000;
                break;
            case 3:
                $deductible = 2000;
                break;
            case 4:
                $deductible = 5000;
                break;
            case 5:
                $deductible = 10000;
                break;
        }
        return $deductible;
    }

    public function actions()
    {
        return $this->belongsToMany(Action::class)->withTimestamps();;
    }

    public static function changeCoveragesToInactive($coverages){
        foreach ($coverages as $coverage){
            $coverage->state = Enum::COVERAGE_STATE_INACTIVE;
            switch ($coverage->status){
                case Enum::COVERAGE_STATUS_INCREASE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_INCREASE_TERMINATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_TERMINATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_DECREASE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_DECREASE_TERMINATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID:
                case Enum::COVERAGE_STATUS_GRACE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_GRACE_TERMINATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_ACTIVE_INCREASED:
                    $coverage->status = Enum::COVERAGE_STATUS_FULFILLED_INCREASE;
                    $coverage->save();
                    break;
             //Commented for fulfilled status on payment failure on May/16/2023
                case Enum::COVERAGE_STATUS_ACTIVE:
                    $coverage->status = Enum::COVERAGE_STATUS_FULFILLED;
                   $coverage->save();
                   break;
            }
        }
    }

    public static function changeCoveragesToActive($coverages){
        foreach ($coverages as $coverage){
            $coverage->state = Enum::COVERAGE_STATE_ACTIVE;
            switch ($coverage->status){
                case Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID:
                case Enum::COVERAGE_STATUS_INCREASE_UNPAID:
                case Enum::COVERAGE_STATUS_ADDPREM_INCREASE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_ACTIVE_INCREASED;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_DECREASE_UNPAID:
                case Enum::COVERAGE_STATUS_GRACE_UNPAID:
                case Enum::COVERAGE_STATUS_UNPAID:
                case Enum::COVERAGE_STATUS_ADDPREM_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_ACTIVE;
                    $coverage->save();
                    break;
            }
        }
    }
    public function getAnnualLimitAttribute()
    {
        return $this->product_name == Enum::PRODUCT_NAME_MEDICAL ?config('static.medical_annual_limit'):0;
    }


}
