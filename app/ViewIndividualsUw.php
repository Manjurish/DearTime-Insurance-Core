<?php

namespace App;
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;

class ViewIndividualsUw extends Model
{

    use Encryptable;

    protected $table    =   'view_individuals_underwritings';
    protected $casts = ['answers' => 'array'];
     protected $encryptable = ['answers'];
}