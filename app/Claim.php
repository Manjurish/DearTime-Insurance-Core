<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Traits\UuidsRefsAlphaNumeric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Claim extends Model
{
	use UuidsRefsAlphaNumeric;

	public    $prefix  = 'CL';
	protected $guarded = [];
	protected $appends = ['policyName','userRole','ownerName','policy','claimantName'];
	protected $visible = ['uuid','ref_no','status','policyName','policy','ownerName','userRole','created_at','updated_at','documents','statusChanges','claimantName','is_panel_hospital'];

	public function coverage()
	{
		return $this->belongsTo(Coverage::class,'coverage_id');
	}

	public function owner()
	{
		return $this->belongsTo(Individual::class,'owner_id');
	}

	public function answers()
	{
		return $this->hasMany(UserClaimQuestion::class,'claim_id');
	}

	public function documents()
	{
		return $this->morphMany('App\Document','documentable');
	}

	public function statusChanges() {
		return $this->hasMany(ClaimStatusLogs::class,'claim_id');
	}

	public function getPolicyNameAttribute()
	{
		return $this->coverage->product_name ?? '';
	}

	public function getPolicyAttribute()
	{
		return $this->coverage->uuid ?? '';
	}

	public function getOwnerNameAttribute()
	{
		return $this->coverage->owner->name ?? '';
	}

	public function getClaimantNameAttribute()
	{
		return $this->profile->user->name ?? '';
	}

	public function getQrAttribute()
	{
		return encrypt($this->coverage->uuid . $this->owner_);
	}

	public function getUserRoleAttribute()
	{
		$user = Auth::user()->profile ?? NULL;
		if(empty($user))
			return NULL;

		if($user->id == ($this->coverage->owner_id ?? 0))
			return 'owner';

		if(!empty($this->coverage->owner) && $user->id == ($this->coverage->owner->beneficiaries()->where('nominee_id')->first()->id ?? 0))
			return 'beneficiary';

		return NULL;
	}

	public function profile()
	{
		return $this->belongsTo(Individual::class,'individual_id');
	}

	public function hospital()
	{
		return $this->belongsTo(User::class,'panel_id');
	}
}
