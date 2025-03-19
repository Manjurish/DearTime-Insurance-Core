<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as RoleModel;
use App\Traits\Uuids;

class Role extends RoleModel
{
    use Uuids;
    use SoftDeletes;

	public function getNameAttribute($value)
	{
		return htmlspecialchars($value);
    }
}
