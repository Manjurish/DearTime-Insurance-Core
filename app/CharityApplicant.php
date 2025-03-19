<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharityApplicant extends Model
{
    use SoftDeletes;
    use Uuids;
    protected $guarded = [];
    protected $hidden = ['id', 'individual_id', 'deleted_at', 'individual'];
    protected $appends = ['selfie','gender', 'city', 'job', 'age', 'name', 'waiting'];

    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }
    public function individual(){
        return $this->belongsTo(Individual::class);
    }

    public function getSelfieAttribute(){
        return  $this->documents()->whereType('selfie')->first()->ThumbLink ?? null;
    }
    public function getGenderAttribute(){
        return  $this->individual->gender;
    }
    public function getCityAttribute()
    {
        return $this->individual->address->AddressCity->name ?? '';
    }
    public function getNameAttribute()
    {
        return $this->individual->name;
    }
    public function getWaitingAttribute()
    {
        return  $this->created_at->diffForHumans();
    }
    public function getJobAttribute()
    {
        return IndustryJob::find($this->individual->occ)->name;
    }
    public function getAgeAttribute()
    {
        return $this->individual->age();
    }

}
