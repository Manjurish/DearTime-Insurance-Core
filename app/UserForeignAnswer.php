<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserForeignAnswer extends Model
{
    public function documents(){
        return $this->morphMany(Document::class, 'documentable');
    }
}
