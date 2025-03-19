<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;


class InternalUser extends Authenticatable implements Auditable
{
    use Notifiable;
    use HasRoles;
    use Uuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'internal_users';
    protected $guard_name = 'internal_users';


    protected $fillable = [
        'name','family', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getSelfieAttribute()
    {
        return asset('images/male.png');
    }

    public function actions()
    {
        return $this->morphMany(Action::class, 'createdbyable');
    }
}
