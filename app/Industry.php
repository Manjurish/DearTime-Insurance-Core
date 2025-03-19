<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Industry extends Model
{
    use SoftDeletes;
    use Uuids;
//    use \OwenIt\Auditing\Auditable;

    protected $visible = ['id','uuid', 'name', 'occupations'];
    protected $appends = ['name'];

    public function occupations(){
        return $this->hasMany(IndustryJob::class);
    }

    public function getNameAttribute()
    {
        if(!auth()->check() && !auth('api')->check())
            return $this->attributes['name'];
        $locale = Helpers::getLocale();

        if(empty($locale) || $locale == 'en')
            return $this->attributes['name'];

        return $this->attributes['name_'.$locale];

    }

}
