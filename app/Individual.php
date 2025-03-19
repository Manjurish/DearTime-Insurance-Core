<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Helpers\Enum;
use App\Notifications\Email;
use App\Notifications\Sms;
use App\Scopes\OwnerScope;
use App\Scopes\RegisteredScope;
use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Individual extends Model implements Auditable
{
	use Uuids;
	use \OwenIt\Auditing\Auditable;
	protected $guarded = [];
	protected $hidden  = ['created_at','updated_at','address_id','id','user_id','verification','occ','country_id'];
	protected $dates   = ['created_at','updated_at',/*'dob',*/'passport_expiry_date'];
	protected $appends = ['selfie','occupation','verification_status'];
	protected $with    = ['address','country'];

	/** exclude auditing */
	protected $auditExclude = [
		'id',
		'uuid',
		'user_id',
		'created_at'
	];

	//boot
	protected static function boot()
	{
		parent::boot();

		static::saved(function ($model) {
			//check if is a registered nominee
			$email = $model->user->email ?? NULL;
			$ben = Beneficiary::where("email",$email)->get();
            foreach ($ben as $bn){
				$bn->nominee_id = $model->id;
				$bn->save();
			     }
			if(!empty($email) && !empty($model->nric) && $model->isVerified()){

				$beneficiaries = Beneficiary::where("email",$email)->where("status","!=",'registered')->get();
				foreach ($beneficiaries as $beneficiary) {
					if(((string)str_replace("-","",$beneficiary->nric)) == ((string)str_replace("-","",$model->nric))){
						$beneficiary->status     = 'registered';
						$beneficiary->nominee_id = $model->id;
					}else{
						$beneficiary->status = 'mismatch';
					}
					$beneficiary->save();
					if(!empty($beneficiary->individual->user)){
						$u    = $beneficiary->individual->user;
						$text = 'Your Nominee "' . $beneficiary->name . '" verification status changed to ' . $beneficiary->status;

						$u->sendNotification(__('notification.nominee_verification.title'),$text,['command' => 'next_page','data' => 'policies_page']);
						$u->notify(new Sms($text));
						$u->notify(new Email($text));
					}
				}
			}
			$age = $model->age();
			if(($age > 16 && $age < 65) || empty($model->dob)){

			}else{
				$model->in_restricted_age = 1;
				//                $model->save();
			}

		});

		static::creating(function ($model) {
			$model->uuid = Str::uuid()->toString();
		});

		static::updated(function ($model) {
			if(!empty($model->user)){
				$model->user->screening($model->name,$model->dob,$model->user_id);
			}
		});

		self::addGlobalScope(new OwnerScope());
	}

	public function scopeWithChild($q)
	{
		return $q->withoutGlobalScope(OwnerScope::class);
	}

	public function scopeOnlyChild($q)
	{
		return $q->withoutGlobalScope(OwnerScope::class)->where(function ($q) {
			return $q->where("type","!=","owner");
		});

	}

	//mutators
	public function setNricAttribute($value)
	{
		$this->attributes['nric'] = str_replace("-","",$value);
	}

	public function setMobileAttribute($value)
	{
		$this->attributes['mobile'] = str_replace("-","",$value);
	}

	//relations

	public function childs()
	{
		return $this->hasMany(Individual::class,'owner_id')->OnlyChild();
	}

	public function owner()
	{
		return $this->belongsTo(Individual::class,'owner_id')->WithChild();
	}

	public function user()
	{
		return $this->belongsTo(User::class)->WithPendingPromoted();
	}

	public function charity()
	{
		return $this->belongsTo(SpoCharityFundApplication::class,'user_id','user_id')->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE']);
	}

	// public function charity()
	// {
	// 	return $this->hasOne(CharityApplicant::class);
	// }

	public function housemember(){
		return $this->hasMany(SpoHouseholdMembers::class)->where('sop_id',$this->charity->id);
	}
	
	

	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	public function underwritings()
	{
		return $this->hasOne(Underwriting::class)->orderBy("created_at","desc");
	}

	public function medicalSurvey()
	{
		return $this->hasMany(Underwriting::class);
	}

	public function hasOnlyParentNominee()
	{
		if($this->nominees()->count() == 0)
			return TRUE;

		return $this->nominees()->where("relationship","parent")->withoutCharity()->count() == $this->nominees()->withoutCharity()->count();
	}

	public function nominees()
	{
		return $this->hasMany(Beneficiary::class);
	}

	public function verification()
	{
		return $this->hasOne(CustomerVerification::class);
	}

	public function claims()
	{
		return $this->hasMany(Claim::class);
	}

	public function address()
	{
		return $this->belongsTo(Address::class);
	}

	public function bankAccounts()
	{
		return $this->morphMany(BankAccount::class,'owner');
	}

	public function bankCards()
	{
		return $this->morphMany(BankCard::class,'owner');
	}

	public function selfieMatch()
	{
		return $this->hasOne(SelfieMatch::class,'individual_id');
	}
    public function coverages_owner()
    {
        // owner is only individual
        return $this->hasMany(Coverage::class, 'owner_id');
    }
	public function coverages_payer()
	{
		//TODO for group must be company id
		return $this->hasMany(Coverage::class,'payer_id','user_id');
	}

	public function coverages_covered()
	{
		return $this->hasMany(Coverage::class,'covered_id','user_id');
	}

	public function coverages_beneficiary()
	{
		// is only individual
		// current user is 2 , indiv is 2
		return $this->hasManyThrough('App\Coverage','App\Beneficiary','nominee_id','owner_id','id','individual_id')
            ->where('state',Enum::COVERAGE_STATE_ACTIVE);
	}

	public function beneficiaries()
	{
		return $this->hasMany(Beneficiary::class);
	}

	public function thanksgiving()
	{
		return $this->hasMany(Thanksgiving::class)->where("percentage",">",0);
	}

	public function getOccupationAttribute()
	{
		if($this->occupationJob)
			return ['job' => $this->occupationJob()->first(),'industry' => $this->occupationJob()->first()->industry()->get()];
	}

	public function occupationJob()
	{
		return $this->belongsTo(IndustryJob::class,'occ');
	}

	public function getNationalityAttribute()
	{
		if(empty($this->country_id))
			return $this->attributes['nationality'] ?? 'Malaysian';
		return $this->country->nationality ?? NULL;
	}

	//accessors

	public function getCountryNameAttribute()
	{
		return $this->country->country ?? NULL;
	}

	public function getSelfieAttribute()
	{
		return $this->selfie();
	}

    public function getAvatarAttribute()
    {
        return $this->selfie();
    }

    /*public function getNameAttribute($name)
    {
        return strtoupper($name);
    }*/

	public function setNameAttribute($name)
	{
		$this->attributes['name'] = strtoupper($name);
	}

    /*public function getDobAttribute($dob)
    {
        return Carbon::parse($dob)->format('d/m/y');
    }*/

    //helpers

    public function isOld()
    {
        if(empty($this->dob))
            return false;
        if($this->age() > 65)
            return true;
        return false;
    }

    public function isChild()
    {
        if(empty($this->dob))
            return false;
        if($this->age() < 16)
            return true;
        return false;
    }
    public function is_local()
    {
        return $this->nationality == "Malaysian" || ($this->country->nationality ?? '') == 'Malaysian';
    }
	public function is_charity()
    {
        // must check if active charity member
        //  return !empty($this->charity);
        if ($this->charity){
		 return $this->charity->active;
		}else{
			return false;
		}
        
    }


	// public function is_charity()
    // {
    //     // must check if active charity member
    //     //  return !empty($this->charity);
    //     if (!$this->charity) return false;
    //     return $this->charity->active;
    // }

    public function getVerificationStatusAttribute()
    {
        return $this->isVerified() ? 'Verified' : 'Pending';
    }

    public function isVerified()
    {
        if(empty($this->verification)){
            return false;
		}
        else if($this->verification->status=="Accepted"){
        return $this->verification->isDone();
		}
    }

    public function needBankAccount()
    {
        return true;

        $charity = (boolean) ($this->nominees()->count() == '1' && $this->nominees()->first()->email == 'Charity@Deartime.com') || ($this->nominees()->count() == 0);
        $cov = $this->coverages_owner()->whereIn("product_id",[2,3,4])->whereIn('status',['Active','unPaid'])->count() > 0;
        return $cov;
    }
    public function hasAgeLimit()
    {
        foreach (Product::all() as $product){
            $numberCoverage = Coverage::where('covered_id', $this->id)->where('product_id', $product->id)->where('status', Enum::COVERAGE_STATUS_ACTIVE)->count();

            if($numberCoverage > 0){
                return false;
            }
        }
        // todo change it in future
        /*if($this->is_restricted_foreign == '1')
            return true;*/
        if(empty($this->dob))
            return false;
        if($this->isOld() || $this->isChild())
            return true;
        return false;
    }
    public function ageMonths()
    {
            return Carbon::parse($this->dob)->diffInMonths(Carbon::now());
    }
    public function selfie()
    {
        $default = asset('images/male.png');
        if($this->gender == 'Female')
            $default = asset('images/female.png');

        return $this->documents()->where("type","selfie")->first()->ThumbLink ?? $default;
    }
    public function documents(){
        return $this->morphMany('App\Document', 'documentable');
    }
    public function age($daysOld = false)
    {
        if ($daysOld)
            return Carbon::parse($this->dob)->diffInDays(Carbon::now());
        return Carbon::parse($this->dob)->age;
    }
    public function own_product($product)
    {
        return $this->all_own_product($product)->first();
    }
    public function all_own_product($product,$excludeUser = null)
    {

        if($excludeUser == 'n')
            return Coverage::whereCoveredId($this->id)->whereProductId($product->id);

		if(!empty($excludeUser))
			$payed_others = Coverage::whereCoveredId($this->id)->whereProductId($product->id)->where("payer_id",$excludeUser)->get()->pluck("id")->toArray();
		else
			$payed_others = Coverage::whereCoveredId($this->id)->whereProductId($product->id)->where("payer_id","!=",$this->user_id)->where("state",Enum::COVERAGE_STATE_ACTIVE)->get()->pluck("id")->toArray();
        return Coverage::whereCoveredId($this->id)->whereProductId($product->id)->where(function ($q) use($payed_others){
            $q->where("payer_id",$this->user_id)->orWhere("payer_id","!=",$this->user_id);
        });
    }

	public function activeCoverages($owner)
	{
		$listOwner = [
			Enum::COVERAGE_OWNER_TYPE_MYSELF,
			Enum::COVERAGE_OWNER_TYPE_OTHERS, // others = company
			Enum::COVERAGE_OWNER_TYPE_CHILD,
		];

		if(!in_array($owner,$listOwner)){
			return NULL;
		}

		// myself
		switch ($owner) {
			case Enum::COVERAGE_OWNER_TYPE_MYSELF:
				$coverages = [];
				foreach (Product::all() as $product) {
					$coverage = Coverage::query()
										->where('product_id',$product->id)
										->where('owner_id',$this->id)
										->where('covered_id',$this->id)
										->where('payer_id',$this->user->id)
										->where('state',Enum::COVERAGE_STATE_ACTIVE)
										->latest()
										->first();

					if(!empty($coverage)){
						array_push($coverages,$coverage);
					}
				}
				return $coverages;

			case Enum::COVERAGE_OWNER_TYPE_OTHERS:
				$coverages = [];
				foreach (Product::all() as $product) {
					$coverage = Coverage::query()
										->where('product_id',$product->id)
										->where('owner_id','!=',$this->id)
										->where('covered_id','!=',$this->id)
										->where('payer_id',$this->user->id)
										->where('state',Enum::COVERAGE_STATE_ACTIVE)
										->latest()
										->first();

					if(!empty($coverage)){
						array_push($coverages,$coverage);
					}
				}
				return $coverages;

				break;

			case Enum::COVERAGE_OWNER_TYPE_CHILD:
				$coverages = [];
				foreach (Product::all() as $product) {
					$coverage = Coverage::query()
										->where('product_id',$product->id)
										->where('owner_id',$this->id)
										->where('covered_id','!=',$this->id)
										->where('payer_id',$this->user->id)
										->where('state',Enum::COVERAGE_STATE_ACTIVE)
										->latest()
										->first();

					if(!empty($coverage)){
						array_push($coverages,$coverage);
					}
				}
				return $coverages;

				break;
		}
	}

	public function freeLook()
	{
		$firstPaymentOn = $this->coverages_owner()->select('first_payment_on')->orderBy('first_payment_on','desc')->first()->first_payment_on;
		return Carbon::parse($firstPaymentOn)->addDay(config('static.freelook_days'))->gt(Carbon::now());
	}
} // end class
