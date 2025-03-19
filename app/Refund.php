<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;
    use Uuids;

    protected $fillable = [
        'action_id',
        'payer',
        'user_id',
        'bank_account_id',
        'amount',
        'status',
		'effective_date',
		'pay_ref_no'
    ];

    public function receiver(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function authorizedBy(){
        return $this->belongsTo(InternalUser::class,'authorized_by');
    }
    public function bankAccounts()
    {
        return $this->belongsTo(BankAccount::class,'bank_account_id');
    }
	public function action(){
		return $this->belongsTo(Action::class,'action_id');
	}

}
