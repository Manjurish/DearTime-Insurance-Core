<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 

namespace App;

use App\Http\Controllers\Api\UserController;
use App\Jobs\Notification;
use App\Scopes\RegisteredScope;
use App\Traits\UuidsRefs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;


class UserPayer extends Authenticatable implements Auditable
{
    use Notifiable, HasApiTokens;
    use UuidsRefs;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public $prefix = 'CU';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $visible = [];
    protected $appends = ['profileDone','PromoteStatus','dashboardAccess'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'activation_token', 'created_at', 'updated_at', 'active','id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** exclude auditing */
    protected $auditExclude = [
        'id',
        'uuid',
        'created_at',
        'activation_token',
        'type',
        'ref_no',
        'password',
        'invalid_loginattempt'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
            $model->active = 1;
        });
        static::created(function ($model){
            $len = 6;
            if(strlen($model->id) > 6)
                $len = strlen($model->id);

            $model->ref_no = $model->prefix.str_pad($model->id,$len,0,STR_PAD_LEFT);
            $model->save();
        });
        self::addGlobalScope(new RegisteredScope());
    }

    public function getSelfieAttribute()
    {
        if(empty($this->profile))
            return asset('images/male.png');

        return $this->profile->selfie();
    }

    public function scopeWithPendingPromoted($q)
    {
        return $q->withoutGlobalScope(RegisteredScope::class);
    }
    public function scopeOnlyPendingPromoted($q)
    {
        return $q->withoutGlobalScope(RegisteredScope::class)->where(function($q){ return $q->whereNotNull('promoter_id')->whereNull('password');});

    }

    public function routeNotificationForSms($notification = null)
    {
        return '+'.$this->profile->mobile;
    }

    public function getProfileDoneAttribute(){
        if($this->active != 1) {
            return false;
        }

        if($this->type == 'individual')
            return ($this->profile->nric ?? null) != null && !empty($this->profile->occupationJob) && !empty($this->profile->address);
        if($this->type == 'corporate')
            return ($this->profile->type ?? null) != null;
    }
    public function getDashboardAccessAttribute(){
        if($this->type == 'individual')
        {
            if($this->ProfileDone)
                return true;
            else return false;

            $bankAccounts = count($this->profile->bankAccounts ?? []);
            $bankCards = count($this->profile->bankCards ?? []);
            $kyc = $this->profile->verification;

            return $kyc && $kyc->count() > 0;
//            return (($bankAccounts > 0) || ($bankCards > 0));
        }else{
            if($this->ProfileDone)
                return true;
        }
        return false;
    }
    public function getNameAttribute()
    {
        return $this->profile->name ?? '';
    }
    public function getPhoneAttribute()
    {
        if($this->type == 'individual')
            return $this->profile->mobile;
        if($this->type == 'corporate')
            return $this->profile->tel_no;

    }
    public function getProfileDoneTextAttribute()
    {
        return $this->ProfileDone ? 'true' : 'false';
    }
    public function getProfileVerificationTextAttribute()
    {
        return $this->profile->verification->status ?? 'Pending Data Entry';
    }
    public function getPromoteStatusAttribute()
    {
        return $this->isPendingPromoted();
    }

    public function isIndividual(){
        return $this->type == 'individual';
    }
    public function isCorporate(){
        return $this->type == 'corporate';
    }
    public function isFlowDone()
    {
        if($this->getNextPage() == 'dashboard_page')
            return true;
        return false;
    }
    public function isPendingPromoted()
    {
        return empty($this->password) && !empty($this->promoter_id);
    }

    public function foreign()
    {
        return $this->hasMany(UserForeignAnswer::class,'user_id');
    }

    public function referrer(){
        return $this->hasMany(User::class,'promoter_id');
    }

    public function setLocale($locale){
        $this->locale = $locale;
        $this->save();
    }
    public function getNextPage(){
        // check where user must go next
//        return 'profile_page';
        if($this->isIndividual()) {
           if($this->ProfileDone) {
               //check if not local
               if(!$this->profile->is_local()){
                   if($this->foreign()->count() == 0)
                       //return 'foreign_page';
                       return 'dashboard_page';
               }
               return 'dashboard_page';
               //check coverage
               if($this->profile->coverages_owner->count() == 0)
                   return 'product_page';
               //check uw
               if(empty($this->profile->underwritings))
               return 'underwriting_page';

               //check uw
               if($this->profile->nominees->count() == 0)
                   return 'nominee_page';

               //check thanksgiving
               if($this->profile->thanksgiving->count() == 0)
                   return 'thanksgiving_page';

               //check payment_details_page
               $charity = $this->profile->nominees()->whereEmail('Charity@Deartime.com')->first()->percentage ?? 0;
               if($this->profile->bankAccounts->count() == 0 && ($charity != 100))
                   return 'payment_details_page';

               //check verification
               if(!($this->profile->isVerified()))
                   return 'verification_page';

               return 'dashboard_page';

           } else {
               return 'profile_page';
           }
        } else {
            if(!$this->profileDone){
                return 'corporate_profile_page';
            }
          return  'group_packages_page';
        }

    }
    public function sendNotification($title,$message,$data)
    {
        return Notification::dispatch($this->id,$title,$message,$data);
    }

    // It is not used anywhere
    public function checkSanctionStatus()
    {

    }

    public function profile() {
		if($this->type == 'individual'){
			return $this->hasOne(Individual::class);
		}
        if($this->type == 'corporate'){
			return $this->hasOne(Company::class);
		}
	}

	public function individual()
	{
		return $this->hasOne(Individual::class);
	}

	public function corporate()
	{
		return $this->hasOne(Company::class);
	}

    public function orders_payer()
    {
        return $this->hasMany(Order::class,'payer_id');
    }

    public function paymentsHistory(){
        $user = $this;
        return Transaction::whereHas('order', function($query) use ($user){
            $query->where('payer_id', $user->id);
        })->latest()->get();
    }
    public function messages()
    {
        return $this->hasMany(\App\Notification::class);
    }
    public function notificationTokens()
    {
        return $this->hasMany(UserNotificationToken::class);
    }

    public function screening($name,$dob,$userId){
        if(empty($name) || empty($dob) || UserScreening::where('user_id',$userId)->count() > 0 || Config::getValue('user_screening') == 'deactive' ){
            return false;
        }

        if(is_string($dob)){
			$yearDob = Carbon::parse($dob)->format('Y');
		}else{
			$yearDob = $dob->format('Y');
		}

        $searchTerm = [
            'search_term'=>$name,
            'client_ref'=>$this->ref_no,
            'fuzziness'=>config('services.screening.fuzziness'),
            'filters'=>[
                'birth_year'=>$yearDob,
                'types'=>[
                    "sanction",
                    "warning",
                    "pep"
                ]
            ],
        ];

        $headers = [
            'Authorization'=>'Token '.config('services.screening.token')
        ];

        $res=Helpers::curlPost(config('services.screening.url').'searches?api_key='.config('services.screening.token'),$searchTerm,$headers);
        $res = json_decode($res,true);
        if($res['code']==200){
            $data = $res['content']['data'];
            $userScreening = new UserScreening();
            $userScreening->create([
                'name'=>$name,
                'user_id'=>$this->id,
                'ref'=>$data['ref'],
                'match_status'=>$data['match_status'],
                'total_hits'=>$data['total_hits'],
                'risk_level'=>$data['risk_level'],
                'fuzziness'=>config('services.screening.fuzziness'),
                'assignee_id'=>$data['assignee_id'],
                'status'=>$data['match_status']==$userScreening::STATUS['rj']?$data['match_status']:$userScreening::STATUS['pn'],
                'details'=>json_encode($data['hits']),
            ]);
            if($data['match_status'] == 'true_positive_approve' || $data['match_status'] == 'true_positive'){
                $this->active = 0;
                $this->save();
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return abort($res['code'],$res['message']);
        }
    }

    public function userConfig($request){
        return app(UserController::class)->getStatus($request,$this);
    }

	public function actions()
	{
		return $this->morphMany(Action::class, 'createdbyable');
	}

    public function findForPassport($username) {
        return $this->where(['email' => $username, 'type' => 'individual'])->first();
    }
}
