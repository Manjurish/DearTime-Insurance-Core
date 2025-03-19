<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractExceptions extends Model
{
    use HasFactory;

    protected $casts = ['medical_exp_en' => 'array','medical_exp_bm' => 'array','medical_exp_ch' => 'array','ci_exp_en' => 'array','ci_exp_bm' => 'array','ci_exp_ch' => 'array'];
}
