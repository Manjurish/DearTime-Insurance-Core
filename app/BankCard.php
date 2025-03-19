<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankCard extends Model
{
    use Uuids,SoftDeletes;


    protected $guarded = [];
    protected $hidden = ['id', 'owner_id', 'owner_type', 'created_at', 'updated_at', 'token', 'cc_msg', 'last_checked', 'status'];

    public function getCvvAttribute(){
        return 'XX/XX';
    }
    public function getCcAttribute(){
     return $this->masked_pan;
    }

}
