<?php

namespace App;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;
    use Uuids;
    
    protected $table='referral';

    protected $fillable = [
        'from_referrer',
        'to_referee',
        'from_referral_name',
        'to_referee_name',
        'amount',
        'thanksgiving_percentage',
		'payment_status',
		'transaction_ref',
        'order_id',
        'month',
        'year',
        'transaction_date'
    ];

}
