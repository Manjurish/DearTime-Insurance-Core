<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public static function getValue($name)
    {
        return Config::where('key',$name)->first()['value'] ?? null;
    }
}
