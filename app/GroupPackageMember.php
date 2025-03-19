<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class GroupPackageMember extends Model
{
    use Uuids;

    protected $guarded = [];
    protected $hidden = ['id', 'package_id'];
}
