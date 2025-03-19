<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use Illuminate\Database\Eloquent\Model;

class UwGroup extends Model
{
    protected $appends =['title'];
    //
    public function questions()
    {
        return $this->hasMany(Uw::class,'group_id')->where('parent_uws_id', '<', 0);
    }

   
    public function getTitleAttribute()
    {

        if(!app()->getLocale())
            return $this->attributes['title'];
        $locale = app()->getLocale();
        if(empty($locale) || $locale == 'en')
            return $this->attributes['title'];

        if($locale == 'ch')
            $locale = 'zh';

        return $this->attributes['title_'.$locale];

    }
}
