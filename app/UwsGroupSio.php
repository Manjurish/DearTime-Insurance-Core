<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UwsGroupSio extends Model
{
    protected $appends =['title'];
    protected $table    =   'uw_groups';
    //
    public function questions()
    {
        return $this->hasMany(UwsSio::class,'group_id')->where('parent_uws_id', '<', 0);
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
