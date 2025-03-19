<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerVerification extends Model
{
    protected $guarded = [];

    public function isDone()
    {
        return true;
    }

    public function individual()
    {
        return $this->belongsTo(Individual::class,'individual_id');
    }

    public function details()
    {
        return $this->hasMany(CustomerVerificationDetail::class,'kyc_id')->orderBy("created_at","desc");
    }

    public function lastDetail()
    {
        return $this->hasOne(CustomerVerificationDetail::class,'kyc_id')->orderBy("created_at","desc");
    }

    public function lastUserDetail()
    {
        return $this->lastDetail()->where("type","user");
    }
    public function lastStaffDetail()
    {
        return $this->lastDetail()->where("type","staff");
    }

    public function isVerified()
    {
        return $this->lastDetail && $this->lastDetail->isVerified();
    }
    public function isPending()
    {
        return $this->lastDetail && $this->lastDetail->status == 'Pending';
    }
}
