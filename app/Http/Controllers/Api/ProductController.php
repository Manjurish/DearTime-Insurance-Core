<?php     

namespace App\Http\Controllers\Api;

use App\Country;
use App\Coverage;
use App\Credit;
use App\Helpers;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Individual;
use App\Underwriting;
use App\IndustryJob;
use App\SpoCharityFundApplication;
use App\Notifications\EmailPromoter;
use App\ParticularChange;
use App\Product;
use App\User;
use App\UserPdsReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Helpers\NextPage;
use App\Notification;


class ProductController extends Controller
{

    /**
     * @api {post} api/getProducts get product
     * @apiVersion 1.0.0
     * @apiName getProducts
     * @apiGroup Product
     *
     * @apiDescription It can show product with attributes, create new/increased/decreased coverage for customer, show active customer's coverages
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} mode null/update/confirm   null for init mode
     * @apiParam (Request) {String} user_id user uuid
     * @apiParam (Request) {String} fill_type buy_for_others/buy_for_others_child
     * @apiParam (Request) {Array} payload coverages array, [['name'=>'Death','coverage'=>3000]]
     * @apiParam (Request) {Boolean} skip_pds
     * @apiParam (Request) {String} payment_term

     *
     * @apiSuccess (Response (200) init mode) {String} name product name
     * @apiSuccess (Response (200) init mode) {String} payers payer id
     * @apiSuccess (Response (200) init mode) {String} active for show active product button in app
     * @apiSuccess (Response (200) init mode) {Array} options product options
     * @apiSuccess (Response (200) init mode) {Array} documents title,link,type
     *
     * @apiSuccess (Response (200) update mode) {String} name product name
     * @apiSuccess (Response (200) update mode) {String} payers payer id
     * @apiSuccess (Response (200) update mode) {String} active for show active product button in app
     * @apiSuccess (Response (200) update mode) {Array} options product options
     * @apiSuccess (Response (200) update mode) {Array} documents title,link,type

     * @apiSuccess (Response (200) confirm mode) {String} next_page
     * @apiSuccess (Response (200) confirm mode) {String} uw_done
     * @apiSuccess (Response (200) confirm mode) {Array} modal
     *
     * @apiError (Response Error confirm mode) {String} status error
     * @apiError (Response Error confirm mode) {Array} data
     * @apiError (Response Error confirm mode) {String} data[message]
     */

    public function index(Request $request)
    {
        // All requests are get here
        list($user,$uuid,$buy_for_self,$buy_for_others,$buy_for_others_child,$payloads,$skip_pds,$increase_check,$mode,$payment_term,$user_id) = $this->getRequests($request);

        // get important data for coverage
        list($user_data,$profile,$payer,$payer_id,$covered_id,$owner_id) = $this->getData($buy_for_self,$buy_for_others,$buy_for_others_child,$user,$uuid);

        // when you buy for yourself, and you have done medical survey (underwriting) but because of your answers you cannot buy any product.
//        if($buy_for_self && !empty($profile->underwritings) && !$profile->underwritings->canBuyCoverage()){
//            return ['status' => 'success','data' => ['next_page' => 'dashboard_page','msg' => __('mobile.uw_limit_err')]];
//        }

        //check age, if more than 65 years old they cannot buy insurance for themself.
        if ($buy_for_self &&  !empty($profile) && $profile->hasAgeLimit()){
            return ['status' => 'success', 'data' => ['next_page' => 'dashboard_page', 'msg' => __('mobile.age_limit_err')]];
        }

        // buy
        //dd('User is user auth' , $user, 'Payer is Inv' , $payer, 'user_data is user', $user_data, 'profile is inv', $profile);

        $result = [];
        $products = Product::all();
        if ($payloads == null && $mode == null){
            // init : get method, when you open the product page
            return $this->initMode($profile,$payer,$products,$user_data,$result,$user_id);
        }else{
            // confirm
            if($mode == "confirm"){
                $unpaidCoverages = Coverage::where(['payer_id' => $user->id, 'covered_id' => $profile->id])->where('status',Enum::COVERAGE_STATUS_UNPAID)->select(['coverage','product_name'])->get();
                $payloads = $payloads??[];
                foreach($unpaidCoverages as $unpaidCoverage){
                    if(count($payloads) == 0){
                        array_push($payloads,[
                            'name'=> $unpaidCoverage->product_name,
                            'coverage'=> $unpaidCoverage->real_coverage
                        ]);
                    }
                    else{
                        foreach ($payloads as $payload){
                            $dataSubjectsValue = array_column($payloads, 'name');
                            if($payload['name'] != $unpaidCoverage->product_name && !in_array($unpaidCoverage->product_name, $dataSubjectsValue)){
                                array_push($payloads,[
                                    'name'=> $unpaidCoverage->product_name,
                                    'coverage'=> $unpaidCoverage->real_coverage
                                ]);
                            }

                        }
                    }

                }
                return $this->confirmMode($products,$payloads,$skip_pds,$increase_check,$profile,$payer,$payment_term,$owner_id,$payer_id,$covered_id,$uuid,$buy_for_self,$buy_for_others_child,$buy_for_others);
            }elseif($mode == "update"){
                return $this->updateMode($products,$payloads,$profile,$buy_for_self,$payer,$user_data,$result,$user_id);
            }
        }
    }

    public function canPayForOthers(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();
        $canBuy = true;
        $reason = '';
        $page   = '';

        if(!$user->isIndividual()) {
            $canBuy = false;
            $reason = __('web/pay_others.individuals');
            $page   = 'dashboard_page';
        }
        $profile = $user->profile;

        if($profile->age() < 16) {
            $canBuy = false;
            $reason = __('web/pay_others.minors');
            $page   = 'dashboard_page';

        }

//        $kyc = $profile->verification ?? null;
        $kyc = $profile->isVerified();

        if(!$kyc) {
            $canBuy = false;
            $reason = __('web/pay_others.kyc');
            $page   = 'verification_page';

        }


        $card = $profile->bankCards;
        if($card->count() > 0){
            $card = $card->first();
            if($card->auto_debit != '1') {
                $canBuy = false;
                $reason = __('web/pay_others.enable_autodebit');
                $page   = 'payment_details_page';
            }
        }else {
            $canBuy = false;
            $reason = __('web/pay_others.credit_card');
            $page   = 'payment_details_page';
        }

        $account = $profile->bankAccounts()->count();
        if($account == 0) {
            $canBuy = false;
            $reason = __('web/pay_others.bank_account');
            $page   = 'payment_details_account_page';

        }
        $thanksgiving = $profile->thanksgiving()->count();
        if($thanksgiving == 0) {
            $canBuy = false;
            $reason = __('web/pay_others.thanksgiving');
            $page   = 'thanksgiving_page';

        }
        if(!empty($reason)){
            return ['data'=> 'error'];
        }
        return ['data'=> $canBuy ? '1' : implode("\n",$reason)];

    }

    /**
     * @api {post} api/buy-for-others buy product for others
     * @apiVersion 1.0.0
     * @apiName BuyForOthers
     * @apiGroup Product
     *
     * @apiDescription It is used for buy coverages for other
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} name
     * @apiParam (Request) {String} passport
     * @apiParam (Request) {Date} dob format d/m/Y
     * @apiParam (Request) {String} nationality uuid
     * @apiParam (Request) {String} gender male/female
     * @apiParam (Request) {Number} personal_income between 0 and 10001
     * @apiParam (Request) {Number} household_income between 0 and 10001
     * @apiParam (Request) {Number} [occ] uuid
     * @apiParam (Request) {Date} passport_expiry_date format d/m/Y
     * @apiParam (Request) {File} birth_cert require if relationship is Child, mime:jpg,jpeg,png,bmp,pdf max:5000

     *
     * @apiSuccess (Response (200)) {String} status success
     * @apiSuccess (Response (200)) {Array} data
     * @apiSuccess (Response (200)) {String} data[next_page]
     * @apiSuccess (Response (200)) {Array} data[next_page_params]
     * @apiSuccess (Response (200)) {String} data[next_page_params][user_id] uuid
     * @apiSuccess (Response (200)) {String} data[next_page_params][user_name]
     * @apiSuccess (Response (200)) {String} data[next_page_params][fill_type]
     * @apiSuccess (Response (200)) {String} data[next_page_url]
     * @apiSuccess (Response (200)) {String} data[] new user uuid
     *
     * @apiError (Response) {String} status error
     */

    public function buyForOther(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();


        $validator = [
            'name' => 'required|string',
            'passport' => 'required_without:uuid',
            'dob' => 'bail|required_without:uuid|nullable|date_format:m/d/Y',
            'nationality' => ['required_without:uuid',Rule::exists('countries','uuid')->where('is_allowed','1')],
            'gender' => 'bail|required_without:uuid|nullable|in:male,female',
            'personal_income' => 'bail|required_without:uuid|nullable|numeric|min:0|max:10001',
            'household_income' => 'bail|required_without:uuid|nullable|numeric|min:0|max:10001',
            'occ' => 'nullable|exists:industry_jobs,uuid',
            'passport_expiry_date' => 'nullable|date_format:d/m/Y',
            'birth_cert' => 'required_if:relationship,Child|mimes:jpg,jpeg,png,bmp,pdf|max:5000',

        ];


        $request->validate($validator);
        $check_owner =Individual::where('nric',str_replace("-","",$request->input('passport')))->first();
        if($check_owner && $check_owner->is_charity()){
            $charity_user =User::where('id',$check_owner->user_id)->first();
            $charity =SpoCharityFundApplication::where('user_id',$check_owner->user_id)->latest()->first();
            if($charity->status =='ACTIVE'){

                $spo_cov =Coverage::where('owner_id',$check_owner->id)->where('sponsored',1)->where('state',Enum::COVERAGE_STATE_ACTIVE)->first();

                $modal = [
                    "title"   => '',
                    "body"    => $check_owner->name.__('mobile.spo_active_payorprompt',['date'=>Carbon::parse($spo_cov->next_payment_on)->format('d/m/y')]),
                    "buttons" => [
                            [
                                "title"  => __('mobile.ok'),
                                "action" => NextPage::POLICIES,
                                "type"   => "page",
                            ],
                            
                    ]
                ];
            }else{
                $modal = [
					"title"   => '',
					"body"    => $check_owner->name.__('mobile.spo_waiting_payorprompt'),
					"buttons" => [
						    [
								"title"  => __('mobile.continue'),
								"action" => NextPage::PRODUCT,
								"type"   => "page",
							],
							[
								"title"  => __('mobile.withdraw'),
								"action" => NextPage::POLICIES,
								"type"   => "page",
							],
							
					]
				];
            }

            return ['status' => 'success','data'=>['modal'=>$modal,'next_page' => 'product_page','next_page_params'=>['user_id'=>$charity_user->uuid,'user_name'=>$charity_user->profile->name ??  $check_owner->name,'fill_type'=>'buy_for_others'],'next_page_url'=>route('userpanel.pay_for_others.product',$charity_user->uuid)]];
            
        }
        //SpoCharityFundApplication
        //if child mobile & email is not required
        $dob = Carbon::createFromFormat("m/d/Y",$request->input('dob'));
        if($dob->diffInYears(now()) < 16){
            //child
            $request->request->add([
               'email'=>Str::uuid()->toString().'@deartime.com',
               'mobile'=>'',
            ]);
            $individual = Individual::OnlyChild()->where("nric",str_replace("-","",$request->input('passport')))->first();
            if(empty($individual)){
                throw ValidationException::withMessages([
                    'passport' => __('web/product.age_16_register')
                ]);
            }
            return ['status' => 'success', 'data' => ['next_page' => 'product_page','next_page_params'=>['user_id'=>$individual->uuid,'user_name'=>$individual->name ?? 'Promoted User','fill_type'=>'buy_for_others_child'],'next_page_url'=>route('userpanel.pay_for_others.product',$individual->uuid)]];

        }else {
            //adult

            $validator = [
                'email' => ['required', 'email'],
                'mobile' => 'required_without:uuid',
            ];
            $request->validate($validator);
        }

        //buy for my self
        if($user->email == $request->input('email'))
            return ['status' => 'success', 'data' => ['next_page' => 'product_page','next_page_url'=>route('userpanel.product.index')]];


        //check if email exists
        $new_user = User::WithPendingPromoted()->where("email",$request->input('email'));//->whereUuid($request->input('uuid'));
        if($new_user->count() == 0){
            $request->validate([
                'mobile'=>'unique:individuals,mobile',
            ]);
            //check if mykad is already not registered
            $ind = Individual::where("nric",str_replace("-","",$request->input('passport')));
            if($ind->count() != 0){
                return ['status' => 'error', 'message' =>__('web/product.nric_invalid')];
            }


            //new user
            $new_user = new User();
            $new_user->email = $request->input('email');
            $new_user->password = null;
            $new_user->type = 'individual';
            $new_user->activation_token = Str::uuid()->toString();
            $new_user->promoter_id = $user->id;
            $new_user->save();

        }else{
            //check if passport is valid
            $new_user = $new_user->first();
            $nric = $new_user->profile->nric ?? null;



            if($nric && $nric != str_replace("-","",$request->input('passport')) && empty($request->input('uuid')))
                return ['status' => 'error', 'message' =>__('web/product.nric_invalid')];


        }
        //check if user has nominated this user
        if(!empty($new_user->profile) && $new_user->isIndividual()){
            if($new_user->profile->nominees()->where('nominee_id',$user->profile->id ?? 0)->count() > 0)
            {
                $nominee_conflict = true;
                return ['status' => 'success', 'data' =>['nominee_conflict'=>$nominee_conflict]];
            }
               
        }
        // if(!empty($user->profile)){
        //     if($user->profile->nominees()->where("relationship","other")->where(function ($q) use($request) {
        //             $q->where("email",$request->input('email'))->orWhere("nric",str_replace("-","",$request->input('passport')));
        //         })->whereStatus('registered')->count() > 0)
        //         return ['status' => 'error', 'message' =>__('web/product.other_nominee_error')];
        // }



        $individual = Individual::where("user_id",$new_user->id);
        if($individual->count() == 0) {
            $dob = Carbon::createFromFormat("m/d/Y",$request->input('dob'));
            $passport_expiry_date = null;
            if(!empty($request->input('passport_expiry_date')))
                $passport_expiry_date = Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));
            $individual = new Individual();
            $individual->user_id = $new_user->id;
            $individual->name = $request->input('name');
            $individual->gender = ucfirst($request->input('gender'));
            $individual->dob = $dob;
            $individual->nationality = $request->input('nationality');
            $country = Country::whereUuid($request->input('nationality'))->first();
            $individual->country_id = $country->id ?? 0;
            $individual->household_income = $request->input('household_income');
            $individual->personal_income = $request->input('personal_income');
            $occ = IndustryJob::where("uuid",$request->input('occ'));
            if($occ->count() > 0){
                $occ = $occ->first()->id ?? 0;
            }else{
                $occ = IndustryJob::where("uuid",$request->input('occ'));
                $occ = $occ->first()->id ?? 0;
            }
            if(empty($occ)){
                if($individual->isChild()){
                    //default -> student
                    $occ = 1145;
                }else{
                    //default -> manager
                    $occ = 1140;
                }
            }
            $individual->occ = $occ ?? 0;
            $individual->passport_expiry_date = $passport_expiry_date;
            $individual->nric = str_replace("-","",$request->input('passport'));
            // todo we should change +60
            $individual->mobile =  $request->input('mobile');
            $individual->save();

        }else{
            $individual = $individual->first();
        }

        if($request->hasFile('birth_cert')) {
            $individual->documents()->where("type", "birth_cert")->delete();
            $selfie = Helpers::crateDocumentFromUploadedFile($request->file('birth_cert'), $individual, 'birth_cert');
        }
        if(!empty($newUserReg)) {
            try {
                $new_user->notify(new EmailPromoter($user->profile->name ?? 'a Promoter User', $new_user->uuid));
            } catch (\Exception $e) {

            }
        }

        return ['status' => 'success', 'data' => ['next_page' => 'product_page','next_page_params'=>['user_id'=>$new_user->uuid,'user_name'=>$new_user->profile->name ?? $individual->name,'fill_type'=>'buy_for_others'],'next_page_url'=>route('userpanel.pay_for_others.product',$new_user->uuid)]];
    }
    public function removeCoverage(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();
        //unAuthorized(empty($user) || empty($user->profile));

        $user->profile->coverages_owner()->where('uuid',$request->input('id'))->update(['status'=> Enum::COVERAGE_STATUS_CANCELLED]);
        return ['status' => 'success', 'message' => __('web/messages.coverage_removed')];

    }

    /**
     * @param array $result
     * @return array
     */
    private function response(array $result): array
    {
        $documents = [];
        return ['status' => 'success','documents' => $documents,'data' => $result];
    }

    /**
     * @param Product $product
     * @param $profile
     * @return UserPdsReview
     */
    private function createUserPdsReview(Product $product,$profile): UserPdsReview
    {
        $userPdsReviews = new UserPdsReview();
        $userPdsReviews->product_id = $product->id;
        $userPdsReviews->individual_id = $profile->id;
        $userPdsReviews->skipped = 0;
        $userPdsReviews->save();
        return $userPdsReviews;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getRequests(Request $request): array
    {
        // default user is set to the current user, which means current user is payer, covered and owner
        $user = $request->user();
        // If the logged in user reaches this method then it can be like this $user = auth()->user();
        if(empty($user)){ // what is this?
            $user = auth()->user();
        }

        // it's better to get uuid instead of user_id from user
        $uuid = $request->input('user_id');
        $fill_type = $request->input('fill_type');
        $buy_for_self = empty($uuid) ? true : false;
        $buy_for_others = ($fill_type == 'buy_for_others');
        $buy_for_others_child = ($fill_type == 'buy_for_others_child');
        $payloads = $request->json('payload');
        $skip_pds = $request->has('skip_pds');
        $increase_check =$request->input('increase_check');
        $mode = $request->json('mode');
        $payment_term = $request->json('payment_term');
        $user_id = $request->json('payer_id');
        return array($user,$uuid,$buy_for_self,$buy_for_others,$buy_for_others_child,$payloads,$skip_pds, $increase_check,$mode,$payment_term,$user_id);
    }

    /**
     * @param $buy_for_self
     * @param $buy_for_others
     * @param $buy_for_others_child
     * @param $user
     * @param $uuid
     * @return array
     */
    private function getData($buy_for_self,$buy_for_others,$buy_for_others_child,$user,$uuid): array
    {
        if($buy_for_self){
            $user_data = $user;
            $profile = $user_data->profile;
            //$user = $user_data->profile;
        }elseif($buy_for_others){
            // if promoted removed then We have to remove it
            // buy for others user is set to the user_id sent from frontend = owner, covered
            $user_data = User::AllUsersDTOrNonDT()->where("uuid",$uuid)->firstOrFail();
            ////unAuthorized(empty($user)); // replaced with firstOrFail but 404; // with scope
            $profile = $user_data->profile;
            //$user = $user_data->profile;
        }else{
            // $buy_for_others_child & user_id is child individual & buy for others child = covered
            $individual = Individual::OnlyChild()->where("uuid",$uuid)->firstOrFail();
            $user_data = $user;
            $profile = $individual;
            //$user = $individual;
            //$buy_for_others = true;
        }

        // always payer will be the authenticated user
        $payer = $user->profile;
        $payer_id = $payer->user_id;
        $covered_id = $profile->id;
        $owner_id = $buy_for_others_child ? $profile->owner_id : $profile->id;
        return array($user_data,$profile,$payer,$payer_id,$covered_id,$owner_id);
    }

    /**
     * @param bool $need_uw if coverage has been increased
     * @param $profile
     * @param $payer
     * @param $uuid
     * @return string
     */
    private function getNextPage(bool $need_uw,$needNominee,$profile,$payer,$uuid): string
    {
        if($need_uw ){
                $next_page = '';
        }else{
            if($payer->id == $profile->id){
                if($needNominee){
                    $next_page = 'nominee_page';
                }elseif($profile->thanksgiving()->count() == 0){
                    $next_page = 'thanksgiving_page';
                }elseif(($profile->bankCards()->count() == 0 || $profile->bankAccounts()->count() == 0) && (!$profile->is_charity()) ){
                    $next_page = 'payment_details_page';
                }elseif(($profile->verification()->count() == 0)){
                    $next_page = 'verification_page';
                }else{
                    if($profile->is_charity()){
                        //$spo_coverage =Coverage::where('payer_id',$profile->user_id)->where('status','unpaid')->get();
                        
                        $spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
                        if($spo_application->status !='QUEUE'){
                        $spo_application->status ='SUBMITTED';
                        }
                        $spo_application->active=1;
                        if($spo_application->renewed!=1){
                            $spo_application->submitted_on =Carbon::now();
                            $spo_application->save();
                        }else{
                                $spo_application->renewed_at =Carbon::now();
                                $spo_application->save();
                        }
                        if($spo_application->status =='QUEUE'){
                            $modal = [
                                
                     
                                "title"   => __('mobile.sponsored_insurance'),
                                "body"    => __('mobile.spo_success_inqueue'),
                                "buttons" => [
                                    [
                                        "title"  => __('ok'),
                                        "action" => NextPage::DASHBOARD,
                                        "type"   => "page",
                                    ],
                                    
                     
                                ]
                            ];
                            return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                            }else{
                                $modal = [
                                
                     
                                    "title"   => __('mobile.sponsored_insurance'),
                                    "body"    => __('mobile.spo_success_submit'),
                                    "buttons" => [
                                        [
                                            "title"  => __('ok'),
                                            "action" => NextPage::DASHBOARD,
                                            "type"   => "page",
                                        ],
                                        
                         
                                    ]
                                ];
                                return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                            }
                        
            }else{
                    $next_page = 'order_review_page';
                }
                }
            }else{
                $next_page = 'promoter_page';
            }
        }
        if(!empty($uuid)){
            $next_page = 'promoter_page';
        }
        return $next_page;
    }

    /**
     * @param $buy_for_self
     * @param string $next_page
     * @param $profile
     * @param $buy_for_others_child
     * @param $buy_for_others
     * @param $payer
     * @return array
     */
    private function getResult($buy_for_self,string $next_page,$profile,$buy_for_others_child,$buy_for_others,$payer,$modal): array
    {
        if($buy_for_self){
            //$result = ['next_page' => $next_page,'uw_done' => $profile->underwritings != null,'modal'=>$modal];
            $coveragesCovered = Coverage::where('covered_id',$profile->id)
                ->whereIn('product_name',[Enum::PRODUCT_NAME_ACCIDENT,Enum::PRODUCT_NAME_DEATH])
                ->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                ->get();

            $needNominee = $coveragesCovered->count()>0 && $profile->beneficiaries()->count() == 0;
            if($needNominee){
                $next_page = NextPage::NOMINEE;
            }
            else if($profile->thanksgiving()->count() == 0) {
                $next_page = NextPage::THANKSGIVING;
            }
            else if (($profile->bankCards()->count() == 0 || $profile->bankAccounts()->count() == 0)&&(!$profile->is_charity())) {
                $next_page = NextPage::PAYMENT_DETAIL;
            }else if ($profile->bankAccounts()->count() == 0 && $profile->is_charity()) {
                $next_page = NextPage::PAYMENT_DETAILS_ACCOUNT;
            }
            else if(!$profile->isVerified()){
                $next_page = NextPage::VERIFICATION;
            }else{
                $modal=[];
                if($profile->is_charity()){
                    $next_page = NextPage::DASHBOARD;
                    $spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
                    if($spo_application->status !='QUEUE'){
                    $spo_application->status ='SUBMITTED';
                    }
                    $spo_application->active=1;
                    if($spo_application->renewed!=1){
                        $spo_application->submitted_on =Carbon::now();
                        $spo_application->form_expiry =Carbon::now()->addMonths(6);
                        $spo_application->save();
                    }else{
                            $spo_application->renewed_at =Carbon::now();
                            $spo_application->save();
                    }
                    if($spo_application->status =='QUEUE'){
                        $modal = Helpers::setModal(__('mobile.spo_success_inqueue'),[
                            ['title'=>__('mobile.uw_ok'),'action'=>'dashboard_page','type'=>'page']
                          ]);
                        //return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                        }else{
                            
                            $modal = Helpers::setModal(__('mobile.spo_success_submit'),[
                                ['title'=>__('mobile.uw_ok'),'action'=>'dashboard_page','type'=>'page']
                              ]);
                            
                        }
                       
                }else{
                    $next_page = NextPage::ORDER_REVIEW;
                }
                
            }
            
            if($profile->is_charity()){
                $spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['ACTIVE','SUBMITTED','QUEUE'])->first();
                if($spo_application && $needNominee == false)
                {
                    if($profile->bankAccounts()->count() == 0){
                       
                        $result = ['next_page' => $next_page,'modal'=>$modal];

                    }else{
                        $result = ['modal'=>$modal];

                    }

                }else{
                    $result = ['next_page' => $next_page,'modal'=>$modal];

                }

            }else{
                $result = ['next_page' => $next_page,'modal'=>$modal];

            }

        }elseif($buy_for_others_child){
            $result = ['next_page' => 'underwriting_page','modal'=>$modal,'uw_done' => true,'next_page_url' => route('userpanel.pay_for_others.medicalSurvey',$profile->uuid),'next_page_params' => ['fill_type' => 'pay_for_others','user_name' => $profile->name ?? null,'user_id' => $profile->uuid ?? null]];
        }elseif($buy_for_others){
            $result = ['next_page' => 'order_review_page','modal'=>$modal,'next_page_url' => route('userpanel.order.other',$profile->uuid),'next_page_params' => ['fill_type' => 'pay_for_others','user_id' => $profile->uuid ?? 0]];
        }

        return $result;
    }

    /**
     * @param $products
     * @param $payloads
     * @param $skip_pds
     * @param $profile
     * @param $payer
     * @param $payment_term
     * @param $owner_id
     * @param $payer_id
     * @param $covered_id
     * @param $uuid
     * @param $buy_for_self
     * @param $buy_for_others_child
     * @param $buy_for_others
     * @return array
     */
    private function confirmMode($products,$payloads,$skip_pds,$increase_check,$profile,$payer,$payment_term,$owner_id,$payer_id,$covered_id,$uuid,$buy_for_self,$buy_for_others_child,$buy_for_others)
    {
		$currentPaymentTerm = Coverage::query()
			->where('payer_id', $payer_id)
			->where('state', Enum::COVERAGE_STATE_ACTIVE)
			->first();

		$currentPaymentTerm = empty($currentPaymentTerm) ? null : $currentPaymentTerm->payment_term;

		$changeTypePaymentTerm = null;

		if(($currentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_MONTHLY) && ($payment_term != Enum::COVERAGE_PAYMENT_TERM_MONTHLY)){ // monthly to annually
			$changeTypePaymentTerm = 'mta';
		}elseif(($currentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_ANNUALLY) && ($payment_term != Enum::COVERAGE_PAYMENT_TERM_ANNUALLY)){ // annually to monthly
			$changeTypePaymentTerm = 'atm';
		}

        //$has_death = false;
        //$hasUpdate = false;
        $need_uw = false;
		$needNominee = false;

        $totalAnnually = 0;
        $allOptions = [];
        $deathCoverage = $profile->coverages_payer()->where("covered_id", $profile->id ?? 0)->where("product_name", Enum::PRODUCT_NAME_DEATH)->where("state", Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
        $need_uw_detector =[];
        foreach ($products as $product) {
            $prod_options = null;
            foreach ($payloads as $payload) {
                if($payload['name'] != $product->name) continue;
                
                if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL && $payload['coverage'] < 0) continue;

                if($product->name == Enum::PRODUCT_NAME_DEATH &&  $payload['coverage'] > 0 && $profile->beneficiaries()->count() == 0){
					$needNominee = true;
				}


//                $deathCoverage = $profile->coverages_payer()->where("covered_id", $profile->id ?? 0)->where("product_name", Enum::PRODUCT_NAME_DEATH)->where("state", Enum::COVERAGE_STATE_ACTIVE)->sum('coverage') ?? 0 + ($payload['name'] == 'Death') ? $payload['coverage'] : 0;

                // $payload['coverage'] is main coverage that receive from app, it's not increased or decreased
                //$oldCoverages = $profile->coverages_payer()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE);
                $oldCoverages = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE);
                if($oldCoverages->count() == 0){
                    array_push($need_uw_detector,true);
                }
                if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                    $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->orderBy('created_at','desc')->limit(1)->get());
                }
                else{
                    $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->get());
                }

                if($changedCoverage > 0){
                    if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                        $payload['coverage'] = $changedCoverage;
                    }
                }
                $decrease_cov =false;

                
                if($changedCoverage < 0){
                    $decrease_cov =true;
                    if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                    $payer_cov_check = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("payer_id",'<>',$payer->user_id)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE)->get();   
                    if($payer_cov_check->isNotEmpty()){
                        $payer_cov = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("payer_id",'<>',$payer->user_id)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                        if( $payload['coverage'] >  $payer_cov){
                            $payload['coverage'] = $payload['coverage']- $payer_cov;
    
                        }else{
                            $payload['coverage'] = $payer_cov - $payload['coverage'];
                        }
                    }
                   
                    }
                }

                $med = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

                 if($med == null){
                    $diff_med = false;
                }else{
                    if($changedCoverage>0){
                    $diff_med = true;
                    }else{
                        $diff_med = false;
                    }
                }

                $prod_options = $product->quickQuoteFor($profile,$payload['coverage'],$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ($payload['coverage']) : null,$decrease_cov, $diff_med);
                //$this->send_email();
                //dd($prod_options);
                
                if($payment_term == 'monthly'){ 
                $full_premium = $prod_options ['monthly'];
                }elseif($payment_term == 'annually'){
                $full_premium = $prod_options ['annually'];
                }

              
                 // increased
                if($changedCoverage>0){
                    $now = Carbon::now();
                    $days = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->next_payment_on));
                    $test = Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->format('Y-m-d');
                    $testt = Carbon::parse($oldCoverages->latest()->first()->next_payment_on)->format('Y-m-d');
                    $diff_days =date_diff(date_create($test), date_create($testt));
                            if($diff_days->format("%y") < 1){
                                $te = $diff_days->format("%a");
                            }else{
                                $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                            }
                    // if($payment_term =='monthly'){
                    //     $prod_options['without_loading'] =round($prod_options['without_loading']* 0.085, 2);
                    // }
                    $prod_options['status'] = Enum::COVERAGE_STATUS_INCREASE_UNPAID;
                    if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                        $prod_options['monthly'] = Helpers::proRate($prod_options['monthly'], $te, $days);
                        $prod_options['annually'] = Helpers::proRate($prod_options['annually'], $te, $days);
                        if ($payment_term=='monthly'){
                            $prod_options['without_loading']  =Helpers::proRate((Helpers::round_up($prod_options['without_loading']* 0.085, 2)), $te, $days);

                        }else{
                            $prod_options['without_loading']  =Helpers::proRate($prod_options['without_loading'], $te, $days);

                        }
                        //$prod_options['without_loading'] =Helpers::proRate($prod_options['without_loading']-$oldCoverages->sum('payment_without_loading'), $te, $days);
                    }
                    else{
                        $prod_options['monthly'] = Helpers::proRate($prod_options['monthly'], $te, $days);
                        $prod_options['annually'] = Helpers::proRate($prod_options['annually'], $te, $days);
                        //$prod_options['without_loading']  =Helpers::proRate($prod_options['without_loading'], $te, $days);
                        if ($payment_term=='monthly'){
                            $prod_options['without_loading']   =Helpers::proRate((Helpers::round_up($prod_options['without_loading']* 0.085, 2)), $te, $days);

                            }else{
                                $prod_options['without_loading']  =Helpers::proRate($prod_options['without_loading'], $te, $days);

                            }
                           

                            
                    }
                    array_push($need_uw_detector,true);
                     $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where('payer_id',$payer_id)->where("product_name", $payload['name'])
                    ->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->update([
                        'status'=>Enum::COVERAGE_STATUS_DECREASE_TERMINATE
                    ]);
                }


                // decreased
                if($changedCoverage<0){
                    if($payer) {
                        $payer->coverages_payer()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])
                            ->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->update([
                                'status'=>Enum::COVERAGE_STATUS_DECREASE_TERMINATE
                            ]);
                    }
                    else {
                        $profile->coverages_payer()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])
                            ->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->update([
                                'status'=>Enum::COVERAGE_STATUS_DECREASE_TERMINATE
                            ]);
                    }
                    $prod_options['status'] = Enum::COVERAGE_STATUS_DECREASE_UNPAID;
                    array_push($need_uw_detector,false);
                }

                $allOptions[$product->name] = $prod_options;
                if ($profile->is_charity()){
                $totalAnnually += $prod_options['annually'];
                }else{
                    if($payment_term == 'monthly'){
                 $totalAnnually += $prod_options['monthly'];
                    }else{
                    $totalAnnually += $prod_options['annually'];
                    }
                  }
                 $stat[] =  $prod_options['status'] ?? null;            
                 }
        }
        
         $dec_sta = true;

        foreach($stat as $sta){
            if ($sta != 'decrease-unpaid'){
                $dec_sta = false;
                break;
         }

        }
        //dd($allOptions);
     
        $need_uw = in_array(true,$need_uw_detector);

        if($dec_sta == false && $totalAnnually < 2){
//Dev-533 Commented the below line to Encounter error message "Minimum premium is RM0.10. Please increase your coverage". Unable to proceed with testing resolved
           return Helpers::response('success', Enum::PAGE_ACTION_TYPE_TOAST,null,null,__('mobile.total_price_more_2'));
//            return [
//                'status' => 'error',
//                'data' => [
//                    'message' => __('mobile.total_price_more_10_cent'),
//                ],
//            ];
        }

		$actions = [];
		$coverageIds = [];

		$isShowPds=$this->checkSkipPds($products,$payloads,$skip_pds,$profile);
		if($isShowPds){
            return [
                'status' => 'error',
                'data' => [
                    'action'  => 'read_pds',
                    'message' => __('mobile.read_pds_first',['title' => $payload['name']]),
                    'product' => $payload['name'],
                ],
            ];
        }

        $spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
        if($profile->is_charity() && $increase_check && $spo_application->status =='QUEUE'){
            $has_coverage =Coverage::where('payer_id',$payer_id)->where('status','unpaid')->get();
            $annually =$has_coverage->sum('payment_annually');
           
            if($has_coverage->isNotEmpty() && $totalAnnually > $annually){
                
                return [
                    'status' => 'error',
                    'data' => [
                        'action'  => 'increase_in_premium',
                        'message' =>__('mobile.spo_coverage_increase'),
                        //'product' => $payload['name'],
                    ],
                ];
            }
        }

        foreach ($products as $product) {
            foreach ($payloads as $payload) {

                $deathCoverage = ($payload['name'] == 'Death') ? $payload['coverage'] : null;

                if($payload['name'] != $product->name) continue;

                if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL && $payload['coverage'] < 0) continue;

                // todo new param : datetime, recalc age
                $prod_options = $allOptions[$payload['name']];
                
                 $oldCoverages = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE);
                if($oldCoverages->count() == 0){
                    array_push($need_uw_detector,true);
                }
                if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                    $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->orderBy('created_at','desc')->limit(1)->get());
                }
                else{
                    $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->get());
                }

                if($changedCoverage > 0){
                    if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                        $payload['coverage'] = $changedCoverage;
                    }
                }
                
                
                if($changedCoverage < 0){
                   
                    if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                    $payer_cov_check = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("payer_id",'<>',$payer->user_id)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE)->get();   
                    if($payer_cov_check->isNotEmpty()){
                        $payer_cov = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("payer_id",'<>',$payer->user_id)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                        if( $payload['coverage'] >  $payer_cov){
                            $payload['coverage'] = $payload['coverage']- $payer_cov;
    
                        }else{
                            $payload['coverage'] = $payer_cov - $payload['coverage'];
                        }
                    }
                   
                    }
                   
                }

                $med = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

                if($med == null){
                   $diff_med = false;
               }else{
                   if($changedCoverage>0){
                   $diff_med = true;
                   }else{
                       $diff_med = false;
                   }
               }
               $decrease_cov =false;
               if($changedCoverage<0)
               {
                $decrease_cov =true;
               }

               $prod_incre = $product->quickQuoteFor($profile,$payload['coverage'],$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ($payload['coverage']) : null,$decrease_cov, $diff_med);      
               if($payment_term == 'monthly'){ 
                $full_premium = $prod_incre ['monthly'];
                }elseif($payment_term == 'annually'){
                $full_premium = $prod_incre ['annually'];
                }
                //$prod_options = $product->quickQuoteFor($profile,$payload['coverage'],$deathCoverage,$payer,$product->name == 'Medical' ? ($payload['coverage']) : null);

                $has_death = ($product->name == "Death") ? true : false;

                // always payer is filled
                $owned_product = $profile->all_own_product($product,$payer->user_id ?? null)->where("payer_id",$payer->user_id)->where("status",Enum::COVERAGE_STATUS_UNPAID)->first();

                // $payer->id == $user->id == buy for self
				// $payer->id == $profile->id buy for self
                if($owned_product && $buy_for_self){ // edit existing, revise price, ...
                    if(($prod_options['coverage'] ?? 0) < 1){
                        //TODO if user try to delete coverage !/?
                        // if(!$buy_for_others)
                        $owned_product->update(['status' => Enum::COVERAGE_STATUS_CANCELLED]);
                        continue;
                    }

                    $payment_term = $payment_term ?? 'monthly';
                    $coverage = $prod_options['coverage'];
                    $payment_monthly = $prod_options['monthly'];
                    $payment_annually = $prod_options['annually'];

                    //$without_loading =   $prod_options['without_loading'];
                   
                    
                  //  $ow = $profile->all_own_product($product,$payer->user_id ?? null)->where("payer_id",'!=',$payer->user_id)->where("status",Enum::COVERAGE_STATUS_UNPAID)->sum('coverage');
                 //   $coverage -= $ow;

                    $active_coverage_sum = $payer->coverages_payer()->where("covered_id",$profile->id ?? 0)->where("product_id",$product->id)->where("status",Enum::COVERAGE_STATUS_ACTIVE)->sum('coverage');

                    // before 100 now 110 then yon need uw

                    // show again medical survey page in app if user has been new and completed this section in the clinic
                    // for resolve this problem we can check if $active_coverage_sum was 0 then set need_new false
                    $need_uw = $active_coverage_sum < $coverage ? true : false;

                    if($owned_product->payment_term != $payment_term ||
                        $owned_product->coverage != $coverage ||
                        $owned_product->payment_monthly != $payment_monthly ||
                        $owned_product->payment_annually != $payment_annually){
                        //$hasUpdate = true;
                        $owned_product->payment_term = $payment_term;
                        $owned_product->payment_term_new = $payment_term;
                        $owned_product->coverage = $coverage;
                        $owned_product->payment_monthly = $payment_monthly;
                        $owned_product->payment_annually = $payment_annually;

                        $owned_product->full_premium = $payment_term=='monthly'?$prod_options['monthly']:$prod_options['annually'];
                        $owned_product->payment_without_loading = $payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                        $owned_product->status = Enum::COVERAGE_STATUS_UNPAID;
                        $owned_product->deductible = $prod_options['deductible'] ?? null;
                        $owned_product->save(); //TODO update coverage need to update price and ...
                    }
                }else{
                     if(($prod_options['coverage'] ?? 0) < 1){
                        $unpaid_cov =Coverage::where('owner_id',$profile->id)->where('product_id',$product->id)->where('payer_id',$payer->user_id)->where("status", Enum::COVERAGE_STATUS_UNPAID)->first();
                        if($unpaid_cov){
                            $unpaid_cov->update(['status' => Enum::COVERAGE_STATUS_CANCELLED]);  
                        }
                        continue;
                    }

                    // $promoter_id = null;
					$status = Enum::COVERAGE_STATUS_UNPAID;
					$payment_term = $payment_term ?? Enum::COVERAGE_PAYMENT_TERM_MONTHLY;
					$coverage = $prod_options['coverage'];
                    //dump($coverage);
					$payment_monthly = $prod_options['monthly'];
					$payment_annually = $prod_options['annually'];
                    // tood delete here and in migration
                    $has_loading = true;

                    // change Unpaid
                    // $ow = $profile->all_own_product($product,$payer->user_id ?? null)->whereProductId($product->id)->where("payer_id",'!=',$payer->user_id)->where("status",Enum::COVERAGE_STATUS_UNPAID)->sum('coverage');

                   // $coverage -= $ow;
                    //dump($coverage);

                    $coverageForProfile = Coverage::whereOwnerId($profile->id)->whereProductId($product->id);

                    // multiple payers how
                    //if($payer->id != $owner_id){
                    if($buy_for_others){
                        $cov = $coverageForProfile->where("payer_id",$payer->user_id)->where("status", Enum::COVERAGE_STATUS_UNPAID)->first();
                
                        //get other coverages  sum
                        $other_coverages_sum = $coverageForProfile->where("payer_id","!=",$payer->user_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE)->sum('coverage') ?? 0;

                        if($coverage < $other_coverages_sum){
                            return [
                                'status' => 'error',
                                'message' => __('web/messages.other_coverages_edit_error'),
                            ];
                        }

                        $coverage -= $other_coverages_sum;
                        
                        $med = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

                        if($med == null){
                           $diff_med = false;
                       }else{
                           if($changedCoverage>0){
                           $diff_med = true;
                           }else{
                               $diff_med = false;
                           }
                       }

                       $decrease_cov =false;
                       if($changedCoverage<0){
                        $decrease_cov =true;
                       }

                        $prod_options_payor = $product->quickQuoteFor($profile,$coverage,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ($coverage) : null, $decrease_cov, $diff_med);

                        if(empty($cov)){
                            //new coverage
                            $status = $prod_options['status'] ?? $status;
                            if($status == 'unpaid'){
                                $without_loading = $payment_term=='monthly'?(Helpers::round_up($prod_options_payor['without_loading']* 0.085, 2)): $prod_options_payor['without_loading'];
                              }else{
                                $without_loading = $prod_options['without_loading'];
    
                              }
                           // $without_loading= $payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                            $payment_term_new = $payment_term;
                            if($status == 'unpaid'){
                                $full_premium = $payment_term=='monthly'?$prod_options_payor['monthly']:$prod_options_payor['annually'];
                              }else{
                                $full_premium = $full_premium;
    
                              }
                            if($status == 'unpaid'){
                              $payment_monthly = $prod_options_payor['monthly'];
                              }
                            if($status == 'unpaid'){
                              $payment_annually = $prod_options_payor['annually'];
                              }
                           // $full_premium = $full_premium;
                               $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$has_loading,$prod_options,$without_loading,$full_premium);
                        }else{
                            //edit coverage
                            //$hasUpdate = true;
                            $cov->payment_term = $payment_term;
                            $cov->payment_term_new = $payment_term;
                            $cov->max_coverage = $prod_options['max_coverage'];
                            $cov->coverage = $coverage;
                            $cov->deductible = $prod_options['deductible'] ?? null;

                           if($status == 'unpaid'){
                            $cov->payment_monthly = $prod_options_payor['monthly'];
                            }
                            if($status == 'unpaid'){
                            $cov->payment_annually = $prod_options_payor['annually'];
                            }
                            if($status == 'unpaid'){
                                $cov->full_premium = $payment_term=='monthly'?$prod_options_payor['monthly']:$prod_options_payor['annually'];
                             }else{
                                $cov->full_premium = $full_premium;
                              }
                           // $cov->full_premium = $full_premium;
                            if($status == 'unpaid'){
                                $cov->payment_without_loading = $payment_term=='monthly'?(Helpers::round_up($prod_options_payor['without_loading']* 0.085, 2)): $prod_options_payor['without_loading'];
                              }else{
                                $cov->payment_without_loading = $prod_options['without_loading'];
                            }
                           // $cov->payment_without_loading = $payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                            $cov->status = $prod_options['status'] ?? Enum::COVERAGE_STATUS_UNPAID;

                            $cov->save(); //TODO update coverage need to update price and ...
                        }

                        if($profile->user()->WithPendingPromoted()->first()->isPendingPromoted())
                            $result = ['next_page' => 'policies_page','uw_done' => true,'message' => __('web/product.wait_for_owner')];
                        else{
                            $uo = User::WithPendingPromoted()->where("id",$profile->user_id)->first();
                            $result = ['next_page' => 'underwriting_page','uw_done' => true,'next_page_params' => ['fill_type' => 'pay_for_others','user_name' => $uo->name ?? null,'user_id' => $uo->uuid ?? null]];
                     }
                    }elseif($buy_for_self || $buy_for_others_child){
                        $other_coverages_sum = $coverageForProfile->where("payer_id","!=",$payer->user_id)->sum('coverage') ?? 0;
                        //$coverage -= $other_coverages_sum;
                        $status = $prod_options['status'] ?? $status;
                        $new_payment = Coverage::where('owner_id',$owner_id)->where('state','active')->where("payer_id",$profile->user_id)->first()->payment_term_new ?? null;
                        if($new_payment == null){
                        $payment_term_new = $payment_term;
                        }else{
                        $payment_term_new = $new_payment;
                        }
                        
                        $diff = $product->name == Enum::PRODUCT_NAME_MEDICAL?$prod_options['deductible']: $prod_options['coverage'];
                        $med = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

                        if($med == null){
                            $diff_med = false;
                        }else{
                            if($changedCoverage>0){
                            $diff_med = true;
                            }else{
                                $diff_med = false;
                            }
                        }

                        $decrease_cov =false;

                        if($changedCoverage>0){
                            $decrease_cov = true;
                        }

                        $incre_amt = $product->quickQuoteFor($profile, $diff, null, null, $product->name == Enum::PRODUCT_NAME_MEDICAL ? $diff : null,$decrease_cov, $diff_med);
                         if($payment_term == 'monthly'){
                            $full_premium = $incre_amt ['monthly'];
                            }elseif($payment_term == 'annually'){
                            $full_premium = $incre_amt ['annually'];
                          }
                          
                          if($status == 'unpaid'){
                            $without_loading = $payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                          }else{
                            $without_loading = $prod_options['without_loading'];

                          }

                         // Commented the below lines to fix the renewal date issue for coporate flow for insertion of payment_annaully_new and payment_monthly_new (2/2/24)
                         
                //           $oldCoverages = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE);
                //           $now = Carbon::now();
                //           $days = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->next_payment_on ?? null));
                //           if($payment_term == 'monthly'){
                //           if(empty($oldCoverages->latest()->first()->renewal_date)){
                //             $dayss = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->addYears(1));
                //             $ren = Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->addYears(1);
                //           }else{
                //             $dayss = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null));
                //             $ren = Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null);
                //           }
                //         }elseif($payment_term == 'annually'){
                //             if(empty($oldCoverages->latest()->first()->renewal_date)){
                //                 $dayss = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->addYears(1));
                //                 $ren = Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->addYears(1);
                //               }else{
                //                 $dayss = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null));
                //                 $ren = Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null);  
                //             }
                //         }
                //         //   $dayss = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null));
                //           $test = Carbon::parse($oldCoverages->latest()->first()->first_payment_on ?? null);
                //           $testt = Carbon::parse($oldCoverages->latest()->first()->first_payment_on ?? null);
                //           $nex = Carbon::parse($oldCoverages->latest()->first()->next_payment_on ?? null);
                //         //   $ren = Carbon::parse($oldCoverages->latest()->first()->renewal_date ?? null);
                //           $monn = Carbon::parse($oldCoverages->latest()->first()->first_payment_on ?? null)->daysInMonth;
                //           $mon_nex = $testt->addDays($monn);
                //           $dayy = $now->startOfDay()->diffInDays($mon_nex);
                //           $diff_mon = date_diff(date_create($test), date_create($mon_nex));
                //           $diff_nex =date_diff(date_create($test), date_create($nex));
                //           $dif_ren = date_diff(date_create($test), date_create($ren));
                            
                //         //  

                //           if($diff_nex->format("%y") < 1){
                //                   $te = $diff_nex->format("%a");
                //               }else{
                //                   $te  = round($diff_nex->format("%a")/$diff_nex->format("%y"));
                //               }
  
                //               if($dif_ren->format("%y") < 1){
                //                   $tes = $dif_ren->format("%a");
                //               }else{
                //                   $tes  = round($dif_ren->format("%a")/$dif_ren->format("%y"));
                //               }
  
                //               if($diff_mon->format("%y") < 1){
                //                   $tess = $diff_mon->format("%a");
                //               }else{
                //                   $tess  = round($diff_mon->format("%a")/$diff_mon->format("%y"));
                //               }
        
                //      
  
                //       if($te > 0){
                //           if($payment_term=='monthly'){
                //         if( $prod_options['status'] = Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                //           if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                //               $payment_monthly_new = Helpers::proRate($incre_amt['monthly'], $te, $days);
                //               $payment_annually_new = Helpers::proRate($incre_amt['annually'], $tes, $dayss);
                //               if ($payment_term=='monthly'){
                //                   $without_loading  =Helpers::proRate((Helpers::round_up($incre_amt['without_loading']* 0.085, 2)), $te, $days);
  
  
                //               }else{
                //                   $without_loading  =Helpers::proRate($prod_options['without_loading'], $te, $days);
  
                //               }
  
                //           }
                //           else{
                //                $payment_monthly_new = Helpers::proRate($incre_amt['monthly'], $te, $days);
                //                $payment_annually_new = Helpers::proRate($incre_amt['annually'], $tes, $dayss);
                //                if ($payment_term=='monthly'){
                //                   $without_loading   =Helpers::proRate((Helpers::round_up($incre_amt['without_loading']* 0.085, 2)), $te, $days);
  
                //                }else{
                //                   $without_loading  =Helpers::proRate($prod_options['without_loading'], $te, $days);
                //                }
                //           }
                //       }
                //   }elseif($payment_term=='annually'){
                //           if( $prod_options['status'] = Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                //             if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                //                 $payment_monthly_new = Helpers::proRate($incre_amt['monthly'], $tess, $dayy);
                //                 $payment_annually_new = Helpers::proRate($incre_amt['annually'], $te, $days);
                //                 if ($payment_term=='monthly'){
                //                     $without_loading  =Helpers::proRate((Helpers::round_up($incre_amt['without_loading']* 0.085, 2)), $te, $days);
    
    
                //                 }else{
                //                     $without_loading  =Helpers::proRate($prod_options['without_loading'], $te, $days);
    
                //                 }
    
                //             }
                //             else{
                //                  $payment_monthly_new = Helpers::proRate($incre_amt['monthly'], $tess, $dayy);
                //                  $payment_annually_new = Helpers::proRate($incre_amt['annually'], $te, $days);
                //                  if ($payment_term=='monthly'){
                //                      $without_loading   =Helpers::proRate((Helpers::round_up($incre_amt['without_loading']* 0.085, 2)), $te, $days);
    
                //                  }else{
                //                      $without_loading  =Helpers::proRate($prod_options['without_loading'], $te, $days);
    
                //                  }
    
    
                //             }
                //         }
                //     }
                //   }
                //       else{
                //           if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                //               $payment_monthly_new =$incre_amt['monthly']; 
                //               $payment_annually_new =$incre_amt['annually']; 
                //               $without_loading   =$payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                //           }else{
                //           $payment_monthly_new =$incre_amt['monthly']; 
                //           $payment_annually_new =$incre_amt['annually']; 
                //           $without_loading  =$payment_term=='monthly'?(Helpers::round_up($prod_options['without_loading']* 0.085, 2)): $prod_options['without_loading'];
                //           }
                //       }

                        $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$has_loading,$prod_options,$without_loading,$full_premium);
                    }

					/*if(!empty($changeTypePaymentTerm)){
						$oldCoverage = Coverage::query()
							->where('product_id', $product->id)
							->where('payer_id', $payer_id)
							->where('state', Enum::COVERAGE_STATE_ACTIVE)
							->where('status', Enum::COVERAGE_STATUS_ACTIVE)
							->first();

						array_push($actions, [
							'methods'           => '',
							'coverage_id'       => $newCoverage->id,
							'product_name'      => $newCoverage->product->name,
							'old_payment_term'  => $currentPaymentTerm,
							'new_payment_term'  => $payment_term,
							'changed_at'        => Carbon::now(),
							'first_payment_on'  => $oldCoverage->first_payment_on,
							'current_annually'  => $oldCoverage->payment_annually,
						]);

						array_push($coverageIds, $newCoverage->id);
					}*/
                }
            }
        }

        // add action
		/*if(!empty($actions)){
			$action = $profile->user
				->actions()
				->create([
							 'user_id'    => $profile->user->id,
							 'type'       => Enum::ACTION_TYPE_PLAN_CHANGE,
							 'event'      => Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM,
							 'actions'    => $actions,
							 'execute_on' => Carbon::now(),
							 'status'     => Enum::ACTION_STATUS_EXECUTED
						 ]);

			$action->coverages()->attach($coverageIds);

			if($changeTypePaymentTerm == 'mta'){
				$totalCredit = 0;
				foreach ($actions as $actionItem){
					$to = Carbon::parse($actionItem['first_payment_on']);
					$from = Carbon::parse($actionItem['changed_at']);
					$diffInMonths = ceil($to->floatDiffInMonths($from));
					$totalCredit += round(($actionItem['current_annually'] * (12 - $diffInMonths)) / 12,2);
				}
				if($totalCredit > 0){
					Credit::create([
						'from_id' => $action->user_id,
						'amount'  => -$totalCredit,
						//'type'    => Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM,
					]);
				}
			}

			$actionParams = collect($action->actions)->first();

			$group   = ParticularChange::where('individual_id',$profile->id)->latest()->first();
			$groupId = empty($group) ? 1 : $group->group_id + 1;

			$columnName = 'payment term';
			$oldValue   = $actionParams['old_payment_term'];
			$newValue   = $actionParams['new_payment_term'];
			$particularChange                = new ParticularChange;
			$particularChange->individual_id = $profile->id;
			$particularChange->group_id      = $groupId;
			$particularChange->action_id     = $action->id;
			$particularChange->created_by    = auth()->user()->id;
			$particularChange->column_name   = empty($columnAlias) ? $columnName : $columnAlias;
			$particularChange->old_value     = $oldValue;
			$particularChange->new_value     = $newValue;
			$particularChange->save();
		}*/

        if($profile->is_charity()){
            $coverages =Coverage::where('owner_id',$profile->id)->where('status','unpaid')->get();
            if($coverages->isNotEmpty()){
                foreach($coverages as $coverage){
                    $coverage->sponsored =1;
                    $coverage->save();
                }
            }
        }

        $next_page = $this->getNextPage($need_uw,$needNominee,$profile,$payer,$uuid);

        /* check underwriting modal section */
        $modal = [];
        //Dev-496 temp-content before medical survey
        if($next_page == ''){
            /* if($totalMonthly<30){
                $next_page = Helpers\NextPage::UNDERWRITING;
            } else {
            */
                $modal = Helpers::setModal(__('mobile.total_price_more_30_RM'),[
                  //['title'=>__('mobile.near_clinics'),'action'=>'clinics_page','type'=>'page','after_action_message'=>__('mobile.near_clinics_message')],
                  // ['title'=>__('mobile.self'),'action'=>'underwriting_page','type'=>'page']
                  ['title'=>__('mobile.uw_ok'),'action'=>'underwriting_page','type'=>'page']
                ]);
            //}
        }

		$result = $this->getResult($buy_for_self,$next_page,$profile,$buy_for_others_child,$buy_for_others,$payer,$modal);

        return $this->response($result);
    }

    private function createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$has_loading,$prod_options,$without_loading,$full_premium)
    {
		return Coverage::create([
			'owner_id'         => $owner_id,
			'payer_id'         => $payer_id,
			'covered_id'       => $covered_id,
			'product_id'       => $product->id,
			'product_name'     => $product->name,
			'status'           => $status,
			'payment_term'     => $payment_term,
            'payment_without_loading'=>$without_loading,
            'payment_term_new' => $payment_term_new,
			'coverage'         => $coverage,
			'payment_monthly'  => $payment_monthly,
			'payment_annually' => $payment_annually,
            'full_premium'     => $full_premium,
            //'payment_monthly_new'  => $payment_monthly_new,
			//'payment_annually_new' => $payment_annually_new,
			'has_loading'      => $has_loading,
			'deductible'       => $prod_options['deductible'] ?? null,
		]);
    }

    /**
     * @param $profile
     * @param $payer
     * @param $products
     * @param $user_data
     * @param array $result
     * @return array
     */
    private function initMode($profile,$payer,$products,$user_data,array $result,$user_id): array
    {

        $deathCoverage = $profile->all_own_product(Product::whereName(Enum::PRODUCT_NAME_DEATH)->first())->where(function ($q){
                $q->where("state",Enum::COVERAGE_STATE_ACTIVE)->orWhere('status',Enum::COVERAGE_STATUS_UNPAID);
            })->sum('coverage') ?? null;

            if ($user_id != $profile->user_id){
                $deathCoverage = Coverage::where('owner_id',$profile->id)->where('product_name','Death')->where(function ($q){
                    $q->where("state",Enum::COVERAGE_STATE_ACTIVE);
                })->sum('coverage') ?? null;
                
                $death_cov = Coverage::where('owner_id',$profile->id)->where('payer_id',$user_id)->where('product_name','Death')->where("status",Enum::COVERAGE_STATUS_UNPAID)->sum('coverage') ?? 0;
    
                if($death_cov != 0 && $deathCoverage != null){
                    $deathCoverage = $death_cov + $deathCoverage;
                }elseif($death_cov != 0){
                    $deathCoverage = $death_cov;
                }
    
            }
        Coverage::where('payer_id',$payer->user_id)->where('covered_id',$profile->id)->where('status',Enum::COVERAGE_STATUS_INCREASE_UNPAID)
            ->update([
                'status'=>Enum::COVERAGE_STATUS_INCREASE_TERMINATE
            ]);

        foreach ($products as $product) {
            $product->product_payer_id = $user_id;
            // why check unpaid status first and only check others when unpaid status returns no item
            $owned_product = $profile->all_own_product($product,null)->where(function ($q){
                $q->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_DEACTIVATE])
                    ->orWhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE]);
                    
            });

            $unpaid_products = $profile->all_own_product($product,null)->where(function ($q){
                    $q->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_DEACTIVATE]);
                //         ->orWhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE]);
                        
                 });
            $active_products = $profile->all_own_product($product,null)->where(function ($q){
              
                $q->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE]);
            //         ->orWhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE]);
                    
             });

            
           
//            if($owned_product->count() == 0)
//                $owned_product = $profile->all_own_product($product,$payer->user_id ?? null)->where("status","!=",Enum::COVERAGE_STATUS_CANCELLED)->where("status","!=",Enum::COVERAGE_STATUS_DECREASED);

            $coverage_payers = [];
            // gets active coverage for this product which convers this user and payer is not the user (someone else bought for them)
        $covs = Coverage::where("covered_id",$profile->id)->where("product_id",$product->id)->where("payer_id",'!=',$profile->user_id)->where("state",'active')->get();
            if($payer->id == $profile->id)
                foreach ($covs as $cov) {
                    if($covs->count() == 1 && $cov->covered_id == ($cov->payer->profile->id ?? 0))
                        continue;

                    if($cov->coverage <= 0)
                        continue;

                    $coverage_payers[] = ['title' => ($cov->payer->name ?? '-') . ($cov->covered_id == $cov->payer->profile->id ? ' (Myself)' : ''),'id' => $cov->uuid,'coverage' => $cov->RealCoverage,'show_coverage' => $cov->coverage,'color' => $cov->color];
                }

            if(($active_products->count()>0) && ($unpaid_products->count()>0)){
                if($product->name == Enum::PRODUCT_NAME_MEDICAL && $owned_product->sum('coverage') == 0){
                $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$unpaid_products->orderBy('id','desc')->first()->real_coverage ?? -1:(int)$unpaid_products->sum('coverage') ?? -1,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$unpaid_products->sum('coverage') ?? -1) : null);
               // $options['annually'] = $active_products->sum('payment_annually')+ $options['annually'];
                }else{
                $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$unpaid_products->orderBy('id','desc')->first()->real_coverage ?? -1:(int)$unpaid_products->sum('coverage') ?? -1,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$unpaid_products->orderBy('id','desc')->first()->coverage ?? -1) : null);
                $options['monthly'] =$active_products->sum('payment_monthly')+$options['monthly'];
                $options['annually'] =$active_products->sum('payment_annually')+$options['annually'];
                }
            }else{
                if($active_products->count()>0)
                {
                    if($product->name == Enum::PRODUCT_NAME_MEDICAL){
                        $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$active_products->orderBy('id','desc')->first()->real_coverage ?? -1:(int)$active_products->sum('coverage') ?? -1,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$active_products->sum('coverage') ?? -1) : null);
                        $options['monthly'] = $active_products->sum('payment_monthly');
                        $options['annually'] = $active_products->sum('payment_annually');
                        }else{
                        $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$active_products->orderBy('id','desc')->first()->real_coverage ?? -1:(int)$active_products->sum('coverage') ?? -1,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$active_products->orderBy('id','desc')->first()->coverage ?? -1) : null);
                        $options['monthly'] = $active_products->sum('payment_monthly'); 
                        $options['annually'] = $active_products->sum('payment_annually');
                        }
                }else{
                    if($product->name == Enum::PRODUCT_NAME_MEDICAL && $owned_product->sum('coverage') == 0){
                        $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$owned_product->orderBy('id','desc')->first()->real_coverage ?? 0:(int)$owned_product->sum('coverage') ?? 0,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$owned_product->sum('coverage') ?? 0) : null);
                        }else{
                        $options = $product->quickQuoteFor($profile,$product->name == Enum::PRODUCT_NAME_MEDICAL?$owned_product->orderBy('id','desc')->first()->real_coverage ?? -1:(int)$owned_product->sum('coverage') ?? -1,$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? ((int)$owned_product->orderBy('id','desc')->first()->coverage ?? -1) : null);
                        }
                }
                
            }
            
            if($profile->user_id != $user_id){
                if($product->name == 'Medical'){
                    $cov = Coverage::where('owner_id',$profile->id)->where('payer_id',$user_id)->where("product_id",$product->id)->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_INCREASE_UNPAID,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->latest()->first() ?? null;
                }else{    
                    $cov = Coverage::where('owner_id',$profile->id)->where('payer_id',$user_id)->where("product_id",$product->id)->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_INCREASE_UNPAID,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get() ?? null;
                }
                    
                
            if($product->name == 'Medical' && $cov != null){     
                        $covv = $cov->real_coverage;
                }elseif($product->name != 'Medical' && $cov != null){
                        $covv = $cov->sum('coverage');
                    }else{
                        $covv = 0;
                    }
                    if($product->name == 'Medical'){
                        $options['monthly'] = $cov->payment_monthly ?? 0; 
                        $options['annually'] = $cov->payment_annually ?? 0;
        
                        $options['coverage'] = $covv ?? 0;
                    }else{
                    $options['monthly'] = $cov->sum('payment_monthly') ?? 0; 
                    $options['annually'] = $cov->sum('payment_annually') ?? 0;
                    $options['coverage'] = $covv ?? 0;
                    }
                }
            
            //$this->send_email();
            
           $cover_owner = Coverage::where('owner_id',$profile->id)->where('payer_id',$profile->user_id)->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_INCREASE_UNPAID,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get();
            $cover_payor = Coverage::where('owner_id',$profile->id)->where('payer_id','<>',$profile->user_id)->WhereIn("status",[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_INCREASE_UNPAID,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get();

           if(!$cover_owner->isNotEmpty() && $cover_payor->isNotEmpty()){
             if($user_id != $profile->user_id){
                $toggle_disable = FALSE;
             }else{
                $toggle_disable = TRUE;
             }
           
           }else{
            $toggle_disable = FALSE;
           }
           
           
            $cover = Coverage::where('owner_id',$profile->id)->where('payer_id',$user_id)->where('state','active')->get();

            if(($cover)->isNotEmpty()){
                $coverage_status = 'active';
            }else{
                $coverage_status = 'unpaid';
            }

            $path = 'documents/contract/'.$profile->user->locale.'/Contract-'.$product->name.'.pdf';
            $documents = [
                1 => [
                    "title" => __('mobile.product_disclosure_sheet'),
                    "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'pds','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                    "type" => "pdf"
                ],
                2 => [
                    "title" => __('mobile.contract'),
                    "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'con','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                    "type" => "pdf"
                ],
                3 => [
                    "title" => __('mobile.financial_calculator'),
                    "link" => 'https://www.deartime.com/calculator/',
                ],
                4 => [
                    "title" => __('mobile.faq'),
                    "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'faq','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                    "type" => "pdf"
                ],
            ];

            $result[] = ['name' => $product->name,'payers' => $coverage_payers,'active' => $owned_product->count() > 0,'options' => $options,'documents' => array_values($documents),'toggle_disable' => $toggle_disable,'coverage_status' => $coverage_status]; // TODO add current death coverage
        }

        return $this->response($result);
    }

    /**
     * @param $products
     * @param $payloads
     * @param $profile
     * @param $buy_for_self
     * @param $payer
     * @param $user_data
     * @param array $result
     * @return array
     */
    private function updateMode($products,$payloads,$profile,$buy_for_self,$payer,$user_data,array $result,$user_id): array
    {
        $deathCoverage = null;


        foreach ($products as $product) {
            $product->product_payer_id = $user_id;
            foreach ($payloads as $payload) {
                if($payload['name'] == 'Death')
                    $deathCoverage = $payload['coverage'];

                //check if medical is already purchased for this user

                if($payload['name'] == $product->name){
                    // for child and for another always null
                $covs = Coverage::where("covered_id",$profile->id)->where("product_id",$product->id)->where("payer_id",'!=',$profile->user_id)->where("state",'active')->get();

                    //dd($covs);
                    $coverage_payers = [];
                    //if ($payer->id == $user->id)
                    if($buy_for_self){
                        foreach ($covs as $cov) {
                            if($covs->count() == 1 && $cov->covered_id == ($cov->payer->profile->id ?? 0))
                                continue;

                            if($cov->coverage <= 0)
                                continue;

                            $coverage_payers[] = ['title' => ($cov->payer->name ?? '-') . ($cov->covered_id == $cov->payer->profile->id ? ' (Myself)' : ''),'id' => $cov->uuid,'coverage' => $cov->RealCoverage,'show_coverage' => $cov->coverage,'color' => $cov->color];
                        }
                    }

                    $oldCoverages = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where("state", Enum::COVERAGE_STATE_ACTIVE);
                    if(!$buy_for_self ){
                        $oldCoverages = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", $payload['name'])->where('payer_id',$payer->user_id)->where("state", Enum::COVERAGE_STATE_ACTIVE);

                        if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                        $other_payer_cov = $profile->coverages_owner()->where("product_name", $payload['name'])->where('payer_id','<>',$payer->user_id)->whereIn("status", [Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->sum('coverage') ?? 0;
                        if($payload['coverage'] > $other_payer_cov){
                            $payload['coverage'] = $payload['coverage'] - $other_payer_cov;

                        }else{
                            $payload['coverage'] = $other_payer_cov - $payload['coverage'];

                        }
                    
                        }
                    }
                    if($oldCoverages->count()==0){
                        $options = $product->quickQuoteFor($profile,($payload['coverage'] ?? -1),$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? $payload['coverage'] : null);

                    }else{
                        if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                            $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->orderBy('created_at','desc')->limit(1)->get());
                        }
                        else{
                            $changedCoverage = $this->checkIncreased($payload['coverage'],$oldCoverages->get());
                        }
                    
                        // Included pro rate functionality for increase scenario as on 22-09-2023
                        if($changedCoverage > 0){
                            if($payload['name'] != Enum::PRODUCT_NAME_MEDICAL){
                                $payload['coverage'] = $changedCoverage;
                            }
                        }

                        $decrease_cov =false;


            

                        if($changedCoverage > 0){
                            $now = Carbon::now();
                            $days = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->next_payment_on));
                            $test = Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->format('Y-m-d');
                            $testt = Carbon::parse($oldCoverages->latest()->first()->next_payment_on)->format('Y-m-d');
                            $diff_days =date_diff(date_create($test), date_create($testt));
                            if($diff_days->format("%y") < 1){
                                $te = $diff_days->format("%a");
                            }else{
                                $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                            }
                
                            $options['status'] = Enum::COVERAGE_STATUS_INCREASE_UNPAID;
                            if($payload['name'] == Enum::PRODUCT_NAME_MEDICAL){
                                        
                            $med = $profile->coverages_owner()->where("covered_id", $profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

                            if($med == null){
                                $diff_med = false;
                            }else{
                                $diff_med = true;
                            }


                                $options = $product->quickQuoteFor($profile,($payload['coverage'] ?? -1),$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? $payload['coverage'] : null, null, $diff_med);
                               
                                if($options['restrict_increase']==false){
                                $options['monthly'] = Helpers::proRate($options['monthly'], $te, $days);
                                $options['annually'] = Helpers::proRate($options['annually'], $te, $days);

                                // to add the exiting premium and new premium (diff of old premium and new premium - issue reported by Anisa trello - 95)

                                $options['monthly'] = $options['monthly'] + $oldCoverages->sum('payment_monthly');
                                $options['annually'] = $options['annually'] + $oldCoverages->sum('payment_annually');
                                $prod_options['without_loading'] =Helpers::proRate($options['without_loading'], $te, $days);
                                }
                            }else{
                                $options = $product->quickQuoteFor($profile,($changedCoverage ?? -1),$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? $changedCoverage : null);
                                if($options['restrict_increase']==false){
                                $options['monthly'] = Helpers::proRate($options['monthly'], $te, $days);
                                $options['annually'] = Helpers::proRate($options['annually'], $te, $days);
                                $options['without_loading']  =Helpers::proRate($options['without_loading'], $te, $days);
                                
                                
                              
                                $options['coverage'] = $oldCoverages->sum('coverage')+$changedCoverage;
                                $options['monthly'] = $options['monthly'] + $oldCoverages->sum('payment_monthly');
                                $options['annually'] = $options['annually'] + $oldCoverages->sum('payment_annually');
                                }
                            }
                        }else{

                            if($changedCoverage < 0){
                                $decrease_cov =true;
                            }
                    
                            $options = $product->quickQuoteFor($profile,($payload['coverage'] ?? -1),$deathCoverage,$payer,$product->name == Enum::PRODUCT_NAME_MEDICAL ? $payload['coverage'] : null,$decrease_cov);
                            if(!$decrease_cov){
                            $options['monthly'] = $oldCoverages->sum('payment_monthly');
                            $options['annually'] = $oldCoverages->sum('payment_annually');
                            }
                        }

        
                    }
                    // Included pro rate functionality for increase scenario as on 22-09-2023 - END

                    $path = 'documents/contract/'.$profile->user->locale.'/Contract-'.$product->name.'.pdf';
                    $documents = [
                        1 => [
                            "title" => __('mobile.product_disclosure_sheet'),
                            "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'pds','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                            'type' => 'pdf'
                        ],
                        2 => [
                            "title" => __('mobile.contract'),
                            "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'con','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                            "type" => "pdf"
                        ],
                        3 => [
                            "title" => __('mobile.financial_calculator'),
                            "link" => 'https://www.deartime.com/calculator/',
                        ],
                        4 => [
                            "title" => __('mobile.faq'),
                            "link" => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'monthly' ?? 'annually','type' => 'faq','p' => $product->name,'uuid' => encrypt($user_data->id)]),
                            "type" => "pdf"
                        ],
                    ];

                    $result[] = ['name' => $product->name,'active' => false,'payers' => $coverage_payers,'options' => $options,'documents' => array_values($documents)];
                }
            }
        }
        //$this->send_email();
        return $this->response($result);
    }

    public function checkIncreased($newCoverage,$oldCoverages){
        $oldCoverage = 0;
        if(count($oldCoverages)==0) return 0;

        foreach ($oldCoverages as $oneOldCoverage){
            if($oneOldCoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                return $oneOldCoverage->real_coverage - $newCoverage;
            }
            else{
                $oldCoverage += $oneOldCoverage->coverage;
            }
        }
        return $newCoverage - $oldCoverage;
    }

    private function checkSkipPds($products, $payloads, $skip_pds,$profile)
    {
        $flag = false;
        foreach ($products as $product){
            foreach ($payloads as $payload){
                if($payload['name'] != $product->name) continue;
                if($payload['coverage'] > 0) {
                    $userPdsReviewsCount = UserPdsReview::where('product_id', $product->id)->where('individual_id', $profile->id);
                    if($userPdsReviewsCount->count() == 0){
                        $this->createUserPdsReview($product,$profile);
                        $flag = true;
                    }
                    else{
                        if($userPdsReviewsCount->latest()->first()->skipped == 0){
                            if($skip_pds){
                                $userPds=$userPdsReviewsCount->latest()->first();
                                $userPds->skipped =1;
                                $userPds->save();

                            }
                            else{
                                $flag = true;
                            }
                        }
                    }
                }
            }
        }

        if($flag){
            return true;
        }

        if($skip_pds){
            UserPdsReview::where('individual_id', $profile->id)->update([
                'skipped'=>1
            ]);
            return false;
        }

        return false;
    }

    public function send_email(Request $request) {
       $user = auth()->user();
       $ttotal= Coverage::where("owner_id",$user->profile->id)->wherein('status',['terminate','increase-terminate'])->get()->count();
       $alerttotal= Notification:: where(['title'=>$user->email])->get()->count();
       $data['uw_reject_modal'] = FALSE;
    if($ttotal && $alerttotal==0)
    {
     
        $data['uw_reject_modal'] = TRUE;
    
    
        $emaildata['title'] ='Product Rejection';
        $emaildata['subject'] ='Product Rejection';
        
        $message ="Hi  ".$user->profile->name.", <br> <p> We regret to inform you that due to occupation and Medical survey , one or more of your offered products are not eligible.</p>";
     /*
        
    $status= $user->notify(new \App\Notifications\Email( $message, $emaildata));
    Notification::insert(['title'=>$user->email,'text'=>"termination",'data'=>'{}','is_read'=>0,'auto_read'=>0,'show'=>0,
    'created_at'=> now()
    ]);
     */
    }

    $payer_id = $request->get('payer_id');

    $corporate_type  = User::where('id',$payer_id)->first()->corporate_type ?? null;

  if($corporate_type == null ){
    $coverages = Coverage::where('owner_id',$user->profile->id)->where('payer_id',$payer_id)->whereIn('status',['unpaid','increase-unpaid'])->get();


    foreach($coverages as $coverage){
            $age = $user->profile->age();
            $occ_loading = null;
            $latestuw = Coverage::where('owner_id',$user->profile->id)->latest()->first()->uw_id;
            $underwriting=Underwriting::where('id',$latestuw)->first();

            $med = $user->profile->coverages_owner()->where("covered_id", $user->profile->id ?? 0)->where("product_name", 'Medical')->where("state", Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible ?? null;

            if($med == null){
                $diff_med = false;
            }else{
                $diff_med = true;
            }

            $quote = $coverage->product->getPrice($user->profile,$coverage->coverage,$occ_loading,$age,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting,null,$diff_med);

            if($coverage->status == 'unpaid'){
                $coverage->update(
                    [
    
                        'payment_annually' =>$quote[0],
                        'payment_monthly' =>Helpers::round_up($quote[0] * 0.085, 2),
                        'full_premium'  => $coverage->payment_term =='monthly'?(Helpers::round_up($quote[0]* 0.085, 2)): $quote[0],
                        'payment_without_loading' => $coverage->payment_term =='monthly'?(Helpers::round_up($quote[3]* 0.085, 2)): $quote[3],
    
                    ]
                    );
            }elseif($coverage->status == 'increase-unpaid'){

                $oldCoverages = $user->profile->coverages_owner()->where("covered_id", $user->profile->id ?? 0)->where('product_name',$coverage->product_name)->where("state", Enum::COVERAGE_STATE_ACTIVE);

                $now = $coverage->created_at;
                $days = $now->startOfDay()->diffInDays(Carbon::parse($oldCoverages->latest()->first()->next_payment_on));
                $test = Carbon::parse($oldCoverages->latest()->first()->first_payment_on)->format('Y-m-d');
                $testt = Carbon::parse($oldCoverages->latest()->first()->next_payment_on)->format('Y-m-d');
                $diff_days =date_diff(date_create($test), date_create($testt));
                        if($diff_days->format("%y") < 1){
                            $te = $diff_days->format("%a");
                        }else{
                            $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                        }
              
                if($coverage->product_name == 'Medical'){
                    $monthly = Helpers::proRate(Helpers::round_up($quote[0] * 0.085, 2), $te, $days);
                    $annually = Helpers::proRate($quote[0], $te, $days);
                    if ($coverage->payment_term=='monthly'){
                        $without_loading  =Helpers::proRate((Helpers::round_up($quote[3]* 0.085, 2)), $te, $days);

                    }else{
                        $without_loading  =Helpers::proRate($quote[3], $te, $days);

                    }
                }else{
                    $monthly = Helpers::proRate(Helpers::round_up($quote[0] * 0.085, 2), $te, $days);
                    $annually = Helpers::proRate($quote[0], $te, $days);

                    if ($coverage->payment_term=='monthly'){
                        $without_loading   =Helpers::proRate((Helpers::round_up($quote[3]* 0.085, 2)), $te, $days);

                        }else{
                            $without_loading  =Helpers::proRate($quote[3], $te, $days);

                        }
                       
                        
                }
                $coverage->update(
                    [
    
                        'payment_annually' =>$annually,
                        'payment_monthly' =>$monthly,
                        'full_premium'  => $coverage->payment_term =='monthly'?(Helpers::round_up($quote[0]* 0.085, 2)): $quote[0],
                        'payment_without_loading' => $without_loading,
    
                    ]
                    );
            }
          
    
        }

        return ['status' => 'success', 'data' =>$data];  
    }
    
       return ['status' => 'success', 'data' =>$data];
} 
}