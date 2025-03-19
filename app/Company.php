<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use Uuids;

    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'address_id', 'user_id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $appends = ['selfie'];
    protected $with = ['address','documents'];

    //relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }
    public function packages(){
        return $this->hasMany(GroupPackage::class);
    }
    public function bankCards()
    {
        return $this->morphMany(BankCard::class, 'owner');
    }
    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'owner');
    }

    //accessors
    public function getSelfieAttribute()
    {
        return $this->selfie();
    }

    //helpers
    public function hasAgeLimit()
    {
        return false;
    }
    public function is_charity(){
        return false;
    }
    public function selfie()
    {
        $default = asset('images/group.png');

        return $this->documents()->where("type","selfie")->first()->ThumbLink ?? $default;
    }
    public function isClinic()
    {
        return $this->relationship == 'Panel Clinic';
    }
    public function isHospital()
    {
        return $this->relationship == 'Panel Hospital';
    }
    public function own_product($product)
    {
        return $this->all_own_product($product)->first();
    }
    public function all_own_product($product)
    {
        return Coverage::whereOwnerId(-1);
    }




}
