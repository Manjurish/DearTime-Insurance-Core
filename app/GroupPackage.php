<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class GroupPackage extends Model
{
    use Uuids;

    protected $guarded = [];
    protected $hidden = ['id', 'company_id'];
    protected $appends = ['members_total'];

    public function members(){
        return $this->hasMany(GroupPackageMember::class, 'package_id');
    }

    public function getMembersTotalAttribute(){
        return $this->members()->count();
    }
}
