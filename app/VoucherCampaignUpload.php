<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

// use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherCampaignUpload extends Model
{
//     use HasFactory;
//     // use Uuids;

    protected $fillable = [
     	'voucher_code',
		'nric',
		'age',
		'dob',
		'gender',
		'name',
		'email',
		'mobile',
        'nationality',
		'residential_address',
		'state',
		'city',
		'zipcode',
		'country',
		'other_life_insurance',
        'declaration',
		'existing_user',
		'premium_annually_death',
		'premium_annually_accident',
		'premium_annually_disability',
		'full_premium_death',
		'full_premium_accident',
        'full_premium_disability',
		'payment_without_loading_death',
		'payment_without_loading_accident',
		'payment_without_loading_disability',
		'first_payment_on',
		'next_payment_on',
		'last_payment_on',
        'transaction_id',
		'amount',
		'payment_date',
		'gender',
		'coverage_death',
		'coverage_accident',
		'coverage_disability',
        'created_at',
		'updated_at',
		'gateway',
		'card_type',
		'invoice_no',
	];
 }
