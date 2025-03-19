<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForeignQuestion extends Model
{
    public function answers()
    {
        return $this->hasMany(ForeignQuestionAnswer::class,'question_id');
    }
    public function getTitleAttribute()
    {
        if(!auth()->check())
            return $this->attributes['title'];
        $locale = auth()->user()->locale;
        if(empty($locale) || $locale == 'en')
            return $this->attributes['title'];

        return $this->attributes['title_'.$locale];

    }
}
