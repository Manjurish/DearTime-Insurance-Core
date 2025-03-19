<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScreening extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['user_id'];
    const STATUS=['ap'=>'approve','rj'=>'reject','pn'=>'pending'];

    public function getUserNameAttribute()
    {
        return ($this->user->profile->name ?? '-').' ( '.($this->user->profile->nric ?? '-').' )';
    }

    public function getUserUuidAttribute()
    {
        return ($this->user->uuid ?? '-');
    }
    public function getDetailsAttribute($value)
    {
        return json_decode($value,true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
