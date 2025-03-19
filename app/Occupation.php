<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{

    public function job(){
        return $this->belongsTo(IndustryJob::class);
    }
}
