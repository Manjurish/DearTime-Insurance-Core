<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Encryptable;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use Uuids,Encryptable;
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['id', 'owner_id', 'owner_type', 'verified_on', 'verified_by', 'created_at', 'updated_at'];
    protected $appends = ['verified'];
    protected $encryptable = ['account_no','bank_name'];

    public function getVerifiedAttribute(){
        return $this->verified_on != null;
    }
}
