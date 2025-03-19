<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use Illuminate\Database\Eloquent\Model;

class UserClaimQuestion extends Model
{

    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        $name = explode("_",$this->question_id);
        $question_id = (isset($name[1]) ? $name[1] : $name[0]);
        $question = ClaimQuestion::find($question_id ?? 0);
        if(!empty($question))
            return $question->title;
        return '-';
    }

    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }
}
