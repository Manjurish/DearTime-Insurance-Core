<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 

namespace App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Beneficiary extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];
    protected $visible = ['name','email','gender','relationship','type','percentage','nric','passport_expiry_date','NationalityName','dob', 'status', 'is_trust'];
    protected $appends = ['NationalityName', 'is_trust'];

    public function getRelationshipAttribute($value){
        if($this->isCharity() || $this->attributes['name'] == 'charity_insurance')
            return __('mobile.gift_recipient');
        if($this->attributes['relationship'] == 'gift_recipient')
            return __('mobile.gift_recipient');

        return $value;
    }
    public function setNricAttribute($value)
    {
        $this->attributes['nric'] = str_replace("-","",$value);
    }
    //Dev-518 Wrong trust tagging
    public function getIsTrustAttribute($value)
    {
      //if($this->relationship == 'parent')
         return  !$this->has_living_spouse_child   && ($this->individual && $this->individual->religion == 'non_muslim') &&( $this->relationship == 'spouse' || $this->relationship == 'child' ||$this->relationship == 'parent');
        //return $this->relationship == 'spouse' || $this->relationship == 'child';
               
    }

    public function scopeWithoutCharity($q)
    {
        return $q->where("email","!=",'Charity@Deartime.com');
    }

    public function getNationalityNameAttribute()
    {
        return $this->nationalityData->nationality ?? 'Malaysian';
    }
    public function getNameAttribute()
    {
        return $this->attributes['name'] == 'charity_insurance' ? __('mobile.charity_insurance') : $this->attributes['name'];
    }

    public function coverage(){
        return $this->belongsTo(Coverage::class);
    }
    public function individual(){
        return $this->belongsTo(Individual::class);
    }
    public function nominee(){
        return $this->belongsTo(Individual::class, 'nominee_id');
    }
    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }

    public function isCharity()
    {
        return $this->email == 'Charity@Deartime.com';
    }

    public function nationalityData()
    {
        return $this->belongsTo(Country::class,'nationality','id');
    }

}
