<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Traits\UuidsRefsAlphaNumeric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ClaimStatusLogs extends Model
{
	protected $guarded = [];
	protected $visible = ['status','claim_id','user_id','created_at','updated_at'];

	public function owner()
	{
		return $this->belongsTo(Claims::class,'claim_id');
	}


	public function profile()
	{
		return $this->belongsTo(Individual::class,'individual_id');
	}

}
