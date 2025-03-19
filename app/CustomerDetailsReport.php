<?php

namespace App;

use App\Traits\Encryptable;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Crypt;

class CustomerDetailsReport extends Model
{
    // use HasFactory;
    protected $table = 'view_customerdetails_report';
    public $timestamps = false;

    use Encryptable;

    protected $casts = ['Answers' => 'array'];
    protected $encryptable = ['Answers'];


}
