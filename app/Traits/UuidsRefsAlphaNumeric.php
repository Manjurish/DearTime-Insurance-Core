<?php     

namespace App\Traits;

use Illuminate\Support\Str;

trait UuidsRefsAlphaNumeric
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

            $model->ref_no = strtoupper(substr(sha1(time()), 0, 6));
            $model->save();
        });
    }
}
