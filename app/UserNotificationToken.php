<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotificationToken extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
