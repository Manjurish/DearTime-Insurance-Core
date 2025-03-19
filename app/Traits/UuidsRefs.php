<?php     

namespace App\Traits;

use Illuminate\Support\Str;

trait UuidsRefs
{

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });

        static::created(function ($model){
            $len = 6;
            if(strlen($model->id) > 6)
                $len = strlen($model->id);

            $model->ref_no = $model->prefix.str_pad($model->id,$len,0,STR_PAD_LEFT);
            $model->save();
        });
    }
}
