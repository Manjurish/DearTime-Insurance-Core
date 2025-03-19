<?php

namespace App;
// use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoveragePaymentTerm extends Model
{
    use HasFactory;
    // use Uuids;
    
    protected $table='coverage_payment_term';

    protected $fillable = [
        'owner_id',
        'pay_term',
        'payer_id'
    ];

}
