<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPAClaim extends Model
{
    use HasFactory;
    use Uuids;

    protected $fillable = [
		'claim_type',
		'claim_no',
		'policy_no',
		'id_no',
		'date_of_visit',
		'date_of_discharge',
		'diagnosis_code_1',
		'diagnosis_code_2',
		'diagnosis_code_3',
		'provider_code',
		'provider_name',
		'provider_invoice_no',
		'date_claim_received',
		'medical_leave_from',
		'medical_leave_to',
		'tpa_invoice_no',
		'cliam_type',
		'actual_invoice_amount',
		'approved_amount',
		'non_approved_amount',
	];
}
