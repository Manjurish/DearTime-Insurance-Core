<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;


class PartnerUser extends Authenticatable implements Auditable
{
    use Notifiable, HasApiTokens;
    use Uuids;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    use HasRoles;

    protected $guard_name = 'partner';

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

}
