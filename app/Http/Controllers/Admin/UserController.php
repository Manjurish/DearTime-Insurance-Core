<?php     

namespace App\Http\Controllers\Admin;

use App\Address;
use App\Beneficiary;
use App\Coverage;
use App\CoverageType;
use App\CoverageModerationAction;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\SpoCharityFundApplication;
use App\Individual;
use App\Product;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use DB;
class UserController extends Controller
{

	public function index()
	{
		return view('admin.customers.index');
	}

	public function corporatesIndex()
	{
		return view('admin.corporates.index');
	}

    public function configure()
    {
        //todo change some details for corporate users
        $this->model = User::class;
        $this->setTitle("Customers");
//        $this->addBladeSetting("side",true);
        $this->addColumn("RefNo",'ref_no');
        $this->addColumn("Name",'name');
        $this->addColumn("Email",'email');
        $this->addColumn("NRIC",function ($query){
            return $query->profile->nric;
        });

        $this->addColumn("Selfie Match",function ($q){
            return ($q->profile->selfieMatch->percent ?? 0 ).' %';
        });

        $this->addColumn("Date",function ($query){
            return Carbon::parse($query->created_at)->format(config('static.datetime_format'));
        });

//        $this->addField("name","Name",'required');
        $this->addField("email","Email",'required|email');
        $this->addField("active","Status",'required','select','',['1'=>'Active','0'=>'Deactive']);
        $this->addField("type","Type",'required','select','',['individual'=>'Individual','company'=>'Company']);
//        $this->addField("password","Password",['store'=>'required'],'password','',[],'passwordMaker',true);

        //userProfile

//        $this->addAction('admin.User.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.User.destroy','<i class="feather icon-trash-2"></i>','Delete',['$uuid'],Helpers::getAccessControlMethod(),['class'=>'ask']);
        $this->addAction('admin.User.audit','<i class="feather icon-activity"></i>','Audit Trail',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.User.show','<i class="feather icon-eye"></i>','View Details',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.User.resetPassword','<i class="feather icon-user-check"></i>','Reset Password',['$uuid'],Helpers::getAccessControlMethod(),['class'=>'ask']);
        $this->addAction('admin.User.verification','<i class="feather icon-shield"></i>','User eKYC',['$uuid'],Helpers::getAccessControlMethod());
        $this->addBladeSetting('hideCreate',true);

        return $this;
    }

    public function verification($id)
    {
        $user = User::where("uuid",$id);

        if(empty($user)){
			abort(404);
		}

        $user = $user->first();

        if(empty($user->profile) || empty($user->profile->verification)){
            return redirect()->back()->with("danger_alert","User verification has not been completed");
        }
        return redirect()->route('admin.Verification.verify',$user->profile->verification->id);
    }

    public function details($id)
    {
        $user = User::where("uuid",$id);

        if(empty($user)){
			abort(404);
		}

        $user = $user->first();

        return view('admin.user-details',compact('user'));
    }
    public function resetPassword($id)
    {
        if(is_numeric($id)){
			$data = User::where("id",$id);
		}else{
			$data = User::where("uuid",$id);
		}

        $data = $data->get()->first();
        if(empty($data)){
			abort(404);
		}

        $user = $data;
        $token = Password::getRepository()->create($user);
        $user->sendPasswordResetNotification($token);
        return redirect()->back()->with("success","Password reset link sent to user email address !");

    }

    public function dobConverter($request,$val)
    {
        return Carbon::parse($val);
    }

    public function show($id)
    {
        if(is_numeric($id)){
			$data = User::where("id",$id);
		}else{
			$data = User::where("uuid",$id);
		}

        $data = $data->get()->first();
        // dd($data);

        if(empty($data)){
			abort(404);
		}

        $user = $data;

        // for coverage moderation
        $products = [];
        $coveragesOwner = [];
        if($user->isIndividual()){
            $products = Product::all();
            $coveragesOwner =  $user->profile->coverages_owner->where('state', Enum::COVERAGE_STATE_ACTIVE);
            // dd($coveragesOwner);
           
        }

        $parentRef = [];
        // echo "<pre>";
        $parentRef = $coveragesOwner->mapWithKeys(function ($cov, $key) {
            return [$cov->id => $cov->ref_no];
        });
        // dd($parentRef);

        $data = [];

        foreach ($products as $product){
            $deathCoverage = null;

            $prodcutCoverageActive = $coveragesOwner->where('product_id',$product->id)->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();

            // current state
            $latestCoverageModerationAction = CoverageModerationAction::query()->where('product_id', $product->id)->where('individual_id', $user->profile->id)->latest()->first();
            $currentState = !empty($latestCoverageModerationAction) ? $latestCoverageModerationAction->action : Enum::COVERAGE_MODERATION_STATE_NOT_APPLICABLE;

            // not purchased && deactive
            if(empty($prodcutCoverageActive)){
                    if($currentState == Enum::COVERAGE_MODERATION_ACTION_ALLOW_PURCHASE){
                        $actions = [ Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE ];
                    }elseif($currentState == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE){
                        $actions = [ Enum::COVERAGE_MODERATION_ACTION_ALLOW_PURCHASE ];
                    }elseif($currentState == Enum::COVERAGE_MODERATION_STATE_NOT_APPLICABLE){
                        $actions = [ Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE ];
                    }
                $maxCoverages = $activeCoverage = Enum::COVERAGE_MODERATION_STATE_NOT_APPLICABLE;

                $data[$product->id] = [
                    'product-id'        => $product->id,
                    'product-name'      => $product->name,
                    'current-state'     => $currentState,
                    'active-coverage'   => $activeCoverage,
                    'max-coverage'      => $maxCoverages,
                    'actions'           => $actions,
                ];
            }else{
                // active coverage
                //$activeCoverage = $prodcutCoverageActive->coverage;
                $activeCoverage = $coveragesOwner->where('product_id',$product->id)->sum('coverage');

                // max coverage
                if($product->name == 'Accident'){
                    // id death in products is 1
                    //$maxCoverages = $coveragesOwner->where('product_id',1)->first()->coverage;
                    $maxCoverages = $coveragesOwner->where('product_id',1)->sum('coverage');
                }elseif($product->name == 'Medical'){
                    //$activeCoverage = $prodcutCoverageActive->deductible;
                    $activeCoverage = $coveragesOwner->where('product_id',$product->id)->sortByDesc('created_at')->first()->deductible;
                    $maxCoverages = Enum::COVERAGE_MODERATION_STATE_NOT_APPLICABLE;
                }else{
                    $payer = Individual::find($prodcutCoverageActive->payer_id);
                    $options = $product->quickQuoteFor($user->profile,$activeCoverage,$deathCoverage,$payer,$product->name == 'Medical' ? $activeCoverage : null);
                    $maxCoverages = $options['max_coverage'];
                }

                // Actions
                if($activeCoverage > 0 && $product->name == 'Medical'){
                    $actions = [Enum::COVERAGE_MODERATION_ACTION_NO];
                }else{
                    if($activeCoverage == $maxCoverages){ // no action
                        $actions = [Enum::COVERAGE_MODERATION_ACTION_NO];
                    }elseif($activeCoverage < $maxCoverages){ // c,d
                        if($currentState == Enum::COVERAGE_MODERATION_ACTION_ALLOW_INCREASE){
                            $actions = [ Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE ];
                        }elseif($currentState == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE){
                            $actions = [ Enum::COVERAGE_MODERATION_ACTION_ALLOW_INCREASE ];
                        }else{
                            $actions = [ Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE ];
                        }
                    }
                }

                $data[$product->id] = [
                    'product-id'        => $product->id,
                    'product-name'      => $product->name,
                    'current-state'     => $currentState,
                    'active-coverage'   => $activeCoverage,
                    'max-coverage'      => $maxCoverages,
                    'actions'           => $actions ?? [Enum::COVERAGE_MODERATION_ACTION_NO],
                ];
            }

        } // end foreach

		$hasCoverage = Coverage::query()
										->where('covered_id',$user->profile->id)
										->where('state',Enum::COVERAGE_STATE_ACTIVE)
										->exists();
    
        $coveragesOwner = $user->profile->coverages_owner->where('is_deleted', '!=', 1)->sortByDesc(['product', 'created_at']);
        return view('admin.user-details',compact('data','user','hasCoverage', 'parentRef', 'coveragesOwner'));
    }
    public function update(Request $request, $id)
    {
        if(is_numeric($id))
            $data = User::where("id",$id);
        else
            $data = User::where("uuid",$id);

        $data = $data->get()->first();
        if(empty($data))
            abort(404);


        $this->fields($data,$request->input('validationCheck') == '1');

        return parent::update($request, $id);
    }

    public function fields($data,$readOnly = false)
    {
        if($readOnly)
            $this->set("skipConfigureFields",true);
        if(empty($data->type) || ($data->type != 'individual' && $data->type != 'corporate'))
            return;
        $this->addField("ProfileReview","Profile Review",'','hr','',[],'','',true);

        $this->addField("ProfileType","Profile Type",'','view',$data->isIndividual() ? 'Individual' : 'Corporate',[],'','',true);
        $this->addField("ProfileStatus","Profile Status",'','view',function ($c,$v){
            if(empty($v->profile->verification->status))
                return "Data Entry Pending";
            else return $v->profile->verification->status;

        },[],'','',true);
        if($data->isIndividual()) {
            $this->addField("CharityApplication", "Charity Application", '', 'view', function ($c, $v) {
                if (($v->profile->household_income ?? 0) > 3000)
                    return "Non-Eligible";

                if (empty($v->profile->charity) || $v->profile->charity->active != '1') {
                    return "Not Active" . " (<a href='" . route('admin.CharityApplicant.details', [$v->uuid ?? 0]) . "'>Details</a>)";
                } else {
                    return "Active" . " (<a href='" . route('admin.CharityApplicant.details', [$v->uuid ?? 0]) . "'>Details</a>)";
                }
            }, [], '', '', true);
        }


        if($data->isIndividual()){
            $this->set('createScript',asset('js/pages/user.js'));
            //individual
            $this->addField("profile->name","Name",'required',$readOnly ? 'view' : 'text','profile->name');
            $this->addField("profile->nationality","Nationality",'required',$readOnly ? 'view' : 'select',
                function ($data,$value){
                    if(empty($value->profile->nationality))
                        return 'Malaysian';
                    return $value->profile->nationality;

                },config('static.nationalities'));
            $this->addField("profile->nric","NRIC",'required',$readOnly ? 'view' : 'text','profile->nric');
            $this->addField("profile->dob","Date Of Birth",'required|date',$readOnly ? 'view' : 'text',function ($c,$v){
                if(!empty($v->profile->dob))
                    return Carbon::parse($v->profile->dob ?? '')->format("d/m/Y");
            },[],'dobConverter',true);
            $this->addField("profile->gender","Gender",'required',$readOnly ? 'view' : 'select','profile->gender',['Male','Female']);
            $this->addField("profile->mobile","Mobile",'required',$readOnly ? 'view' : 'text','profile->mobile');
            $this->addField("profile->personal_income","Personal Income",'required|numeric',$readOnly ? 'view' : 'slider','profile->personal_income');
            $this->addField("profile->household_income","Household Income",'required|numeric',$readOnly ? 'view' : 'slider','profile->household_income');
            $this->addField("industry","Industry",'required',$readOnly ? 'view' : 'select','profile->occupationJob->industry_id',[],'',true,false,true);
            $this->addField("profile->occ","Occupation",'required',$readOnly ? 'view' : 'select','profile->occ',[]);
            $this->addField("profile->passport_expiry_date","Passport Expiry Date",'',$readOnly ? 'view' : 'text',function ($c,$v){
                if(!empty($v->profile->passport_expiry_date))
                    return Carbon::parse($v->profile->passport_expiry_date ?? '')->format("d/m/Y");
            },[],'dobConverter',true);
            $this->addField("user_address","Address",'',$readOnly ? 'view' : 'text','profile->address->address',[],'',false,true,true);
            $this->addField("state","State",'',$readOnly ? 'view' : 'select','profile->address->state',[],'',false,true,true);
            $this->addField("city","City",'',$readOnly ? 'view' : 'select','profile->address->city',[],'',false,true,true);
            $this->addField("zip_code","ZipCode",'',$readOnly ? 'view' : 'select','profile->address->postcode',[],'saveAddress',true,true,true);


        }else{
            //corporate
            $this->addField("profile->profile->name","Name",'',$readOnly ? 'view' : 'text','profile->name');
            $this->addField("profile->reg_no","Registration Number",'',$readOnly ? 'view' : 'text','profile->reg_no');
            $this->addField("profile->profile_type","Type",'',$readOnly ? 'view' : 'text','profile->type');
            $this->addField("profile->tel_no","Tel Number",'',$readOnly ? 'view' : 'text','profile->tel_no');
            $this->addField("user_address","Address",'',$readOnly ? 'view' : 'text','profile->address->address',[],'saveAddress',true,true,true);

        }



    }

    public function saveAddress($request,$val,$data)
    {

        if(empty($data->profile->address))
            $address = new Address();
        else {
            $address = Address::find($data->profile->address->id ?? 0);
            if (empty($address))
                $address = new Address();
        }

        $address->type = 'residential';
        $address->address = $request->input('user_address') ?? '-';
        $address->city = $request->input('city') ?? '-';
        $address->postcode = $request->input('zip_code') ?? '-';
        $address->state = $request->input('state') ?? '-';
        $address->country = '0';
        $address->save();


        $userProfile = $data->profile()->get()->first();
        $userProfile->address_id = $address->id;
        $userProfile->save();

    }
    public function UploadMykad($request,$val,$data)
    {

    }
    public function edit($id)
    {
        if(is_numeric($id))
            $data = User::where("id",$id);
        else
            $data = User::where("uuid",$id);

        $data = $data->get()->first();
        if(empty($data))
            abort(404);

        $this->fields($data);


        return parent::edit($id);
    }


    public function passwordMaker($request,$val)
    {
        if(!empty($val))
            return bcrypt($val);
    }

    public function store(Request $request, $data = null, $return = false)
    {

        $store = parent::store($request, $data, true);

        if($store->wasRecentlyCreated)
            return redirect()->route('admin.User.edit',$store->uuid)->with("success","Operation Successful");
        return redirect()->route('admin.User.index')->with("success","Operation Successful");

    }

     public function audit($userUuid){
        $user = User::whereUuid($userUuid)->first();
        $individual = $user->profile;
        $userAudit = $user->audits()->orderBy('id','desc')->get();
        $individualAudit = $individual->audits()->orderBy('id','desc')->get();
        $spo_application =SpoCharityFundApplication::where('user_id',$user->id)->withTrashed()->latest()->first();
        $test  = CoverageType::where('owner_id', $user->profile->id)->first();
        $coveragetype = $test->audits()->orderBy('id','desc')->get();
        if($spo_application){
            $spo_audit =$spo_application->audits()->orderBy('id','desc')->get();
            $data = array_merge($individualAudit->toArray(),$userAudit->toArray(),$spo_audit->toArray());
        }else{
            $data = array_merge($individualAudit->toArray(),$userAudit->toArray(),$coveragetype->toArray());

        }
       
        $data = Helpers::paginate($data,10);
        // dd($data);

        return view('admin.user.audit-list',compact('data'));
    }


    public function changeBasicInfo($userUuid)
    {
        $breadcrumbs = [
            ['name' => __('web/messages.admin_area'), 'link'=>route('admin.dashboard.main')],
            ['name' => __('web/messages.customer'), 'link'=> route('admin.User.index')],
            ['name' => __('web/messages.customer_details'), 'link'=> route('admin.User.show', $userUuid)],
            ['name' => __('web/messages.particular_change'), 'link'=> url()->current()],
        ];

        $user = User::whereUuid($userUuid)->first();
        $profile = $user->profile;
        return view('admin.user.change-basic-info',compact('user','profile','breadcrumbs'));
    }

    public function changePaymentTerm($userUuid)
    {
        $breadcrumbs = [
            ['name' => __('web/messages.admin_area'), 'link'=>route('admin.dashboard.main')],
            ['name' => __('web/messages.customer'), 'link'=> route('admin.User.index')],
            ['name' => __('web/messages.customer_details'), 'link'=> route('admin.User.show', $userUuid)],
            ['name' => __('web/messages.change_payment_term'), 'link'=> url()->current()],
        ];

        $user = User::whereUuid($userUuid)->first();
        $profile = $user->profile;
        return view('admin.user.change-payment-term',compact('user','profile','breadcrumbs'));
    }

	public function cancellCoverage($userUuid)
	{
		$breadcrumbs = [
			['name' => __('web/messages.admin_area'), 'link'=>route('admin.dashboard.main')],
			['name' => __('web/messages.customer'), 'link'=> route('admin.User.index')],
			['name' => __('web/messages.customer_details'), 'link'=> route('admin.User.show', $userUuid)],
			['name' => __('web/messages.cancel_coverage'), 'link'=> url()->current()],
		];

		$user = User::whereUuid($userUuid)->first();
		$profile = $user->profile;


		return view('admin.user.cancell-coverage',compact('profile','breadcrumbs'));
    }

    public function updateBeneficiary(Request $request){
        $input = $request->input();
        $beneficiary=Beneficiary::where('id',$input['id'])->first();
        $beneficiary->status = $input['status'];
        $beneficiary->save();
        if($request->file('assignment-file')){
            Helpers::crateDocumentFromUploadedFile($request->file('assignment-file'),$beneficiary);
        }
        return redirect(route('admin.User.show',['User'=>$beneficiary->individual->user->uuid]));
    }
    
        public function Premiunratechecking(){
        return view('admin.premiumcalculator');
}

      public function addVoucher(){
      return view('admin.voucher_insert');
     }


}
