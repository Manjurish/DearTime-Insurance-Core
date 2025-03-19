<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerVerificationDetail extends Model
{
    public function documents(){
        return $this->morphMany(Document::class, 'documentable');
    }

    public function isVerified()
    {
        return $this->status == 'Verified';
    }

    public function creator()
    {
        if($this->type == 'staff'){
            return $this->belongsTo(InternalUser::class,'created_by');
        }else{
            return $this->belongsTo(User::class,'created_by');
        }
    }
}
