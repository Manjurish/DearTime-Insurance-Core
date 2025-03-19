<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class Notification extends Model
{
    use Uuids;
    protected $hidden = ['user_id','id','text'];
    protected $appends = ['created'];
    protected $fillable = ['show','execute_on'];

    public function getLinkAttribute()
    {
        $attributes = [$this->uuid];

        if(!Helpers::isFromApp()){
            $attributes['wb'] = '1';
        }

        return route('page.showNotification',$attributes);
    }

    public function getCreatedAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getTitleAttribute()
    {
        $translate_attributes = (array) (json_decode($this->attributes['data'])->translate_data ?? []);

        return Lang::has($this->attributes['title']) ? __($this->attributes['title'],$translate_attributes) : $this->attributes['title'];
    }

    public function getTextAttribute()
    {
        $translate_attributes = (array) (json_decode($this->attributes['data'])->translate_data ?? []);
        if(!empty($translate_attributes['coverages'])){
            $translate_attributes['coverages'] = $this->translateCoverages($translate_attributes['coverages']);
        }
        return Lang::has($this->attributes['text']) ? __($this->attributes['text'],$translate_attributes) : $this->attributes['text'];
    }

    public function getFullTextAttribute()
    {
        $translate_attributes = (array) (json_decode($this->attributes['data'])->translate_data ?? []);
        if(!empty($translate_attributes['coverages'])){
            $translate_attributes['coverages'] = $this->translateCoverages($translate_attributes['coverages']);
        }
        return Lang::has($this->attributes['full_text']) ? __($this->attributes['full_text'],$translate_attributes) : $this->attributes['full_text'];
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    private function translateCoverages($coverages){
        $coverages = str_replace(':Accident',__('web/product.accident'),$coverages);
        $coverages = str_replace(':Death',__('web/product.death'),$coverages);
        $coverages = str_replace(':Critical Illness',__('web/product.ci'),$coverages);
        $coverages = str_replace(':Medical',__('web/product.medical'),$coverages);
        $coverages = str_replace(':Disability',__('web/product.disability'),$coverages);
        return $coverages;
    }
}
