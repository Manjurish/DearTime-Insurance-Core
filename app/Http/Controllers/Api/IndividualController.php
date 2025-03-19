<?php     

namespace App\Http\Controllers\Api;
use App\Action;
use App\Address;
use App\Country;
use App\Helpers;
use App\Individual;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\Controller;
use App\IndustryJob;
use App\SpoCharityFundApplication;
use App\SpoHouseholdMembers;
use App\Industry;
use App\Coverage;
use App\Order;
use App\User;
use Carbon\Carbon;
use App\CoverageOrder;
use App\Credit;
use App\Refund;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\BankCard;
use App\BankAccount;


class IndividualController extends Controller
{
	/**
	 * @api {post} api/updateProfile update profile
	 * @apiVersion 1.0.0
	 * @apiName UpdateProfile
	 * @apiGroup Individual
	 * @apiDescription This endpoint use for update individual user. The first place is used at register then update profile in dashboard.
	 * @apiUse AuthHeaderToken
	 *
	 * @apiParam (Request) {String} nric
	 * @apiParam (Request) {String} gender male/female
	 * @apiParam (Request) {String} religion muslem/non_muslim
	 * @apiParam (Request) {Date} dob date format d/m/Y
	 * @apiParam (Request) {String} occ uuid
	 * @apiParam (Request) {Number} personal_income min 0 max 10001
	 * @apiParam (Request) {Number} household_income min 0 max 10001
	 * @apiParam (Request) {String} nationality
	 * @apiParam (Request) {Date} passport_expiry_date require if nationality not equal Malaysian, date format d/m/Y
	 * @apiParam (Request) {String} address1 max 30 character
	 * @apiParam (Request) {String} [address2] max 30 character
	 * @apiParam (Request) {String} [address3] max 30 character
	 * @apiParam (Request) {String} state
	 * @apiParam (Request) {String} city
	 * @apiParam (Request) {String} postcode
	 * @apiParam (Request) {Boolean} has_other_life_insurance
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {String} message
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {Boolean} data[is_foreign]
	 * @apiSuccess (Response (200) ) {Boolean} data[charity_eligible]
	 * @apiSuccess (Response (200) ) {Boolean} data[next_page]
	 * @apiSuccess (Response (200) ) {Object} data[config]
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */

	public function updateProfile(Request $request)
	{
		$rules = [
			'gender'                   => 'required|in:male,female',
			'religion'                 => 'required|in:muslim,non_muslim',
			'dob'                      => 'required|date_format:d/m/Y',
			'occ'                      => 'required|exists:industry_jobs,uuid',
			'personal_income'          => 'required|numeric|min:0|max:10001',
			'household_income'         => 'required|numeric|min:0|max:10001',
			'nationality'              => 'required',
			'country_id'               => 'required',
			'passport_expiry_date'     => 'required_unless:nationality,Malaysian|nullable|date_format:d/m/Y',
			'address1'                 => 'required|max:61',
			'address2'                 => 'required_with:address3|max:61',
			'address3'                 => 'max:61',
			'state'                    => 'required',
			'city'                     => 'required',
			'postcode'                 => 'required',
			'has_other_life_insurance' => 'required',
		];

		if($request->nationality == 'Malaysian'){
			// first validation nric for ymd
			$nric = $request->nric;
			$request['nric'] = !empty($nric) ? substr($nric,0,6) : NULL; // get the first six digits of nric(ymd) for validation
			$rules['nric']   = 'required|date_format:ymd';
			$request->validate($rules);

			// second validation for unique nric
			$individual_check =Individual::where('nric',$nric)->latest()->first();
			if($individual_check){
			$pending_nric =$individual_check->user->isPendingPromoted();

           if($pending_nric){
			$rules['nric']   = 'required';
			$request['nric'] = $nric;
			$request->validate($rules);
		   }else{
			
			$rules['nric']   = 'required|unique:individuals,nric,' . $request->user()->profile->id;
			$request['nric'] = $nric;
			$request->validate($rules);
		   }
		   }else{
			
			$rules['nric']   = 'required|unique:individuals,nric,' . $request->user()->profile->id;
			$request['nric'] = $nric;
			$request->validate($rules);
		   }

			// $rules['nric']   = 'required|unique:individuals,nric,' . $request->user()->profile->id;
			// $request['nric'] = $nric;
			// $request->validate($rules);
		}
		
		$new = empty($request->user()->profile->nric);

		Address::whereId($request->user()->profile->address_id ?? NULL)->delete();
		$address               = Address::create($request->only(['address1','address2','address3','state','city','postcode']));
		$request['address_id'] = $address->id;
		$charity_check = SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
       
		$request['occ'] = IndustryJob::whereUuid($request->input('occ'))->first()->id ?? 0;
		$que_positionchange =false;
		if($request->user()->profile->is_charity()){
		if(!empty($charity_check)){
		if($request['occ'] !=$request->user()->profile->occ){
			if($charity_check->status =='QUEUE'){
			    $que_positionchange = $this->addOccupationAction($request);
			}else{
				$this->addOccupationAction($request);
				$que_positionchange =false;
			}

			}
		}
	}

	  $jobcheck =IndustryJob::where('id',$request['occ'] )->first();
			

		

		$request['dob'] = Carbon::createFromFormat("d/m/Y",$request->input('dob'));

		if(!empty($request->input('passport_expiry_date'))){
			$request['passport_expiry_date'] = Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));
		}

		//$country_id = Country::where('country','Malaysia')->first()->id ?? NULL;
		$country_id = Country::where('uuid',$request->country_id)->first()->id ?? NULL;

		$save_data               = $request->only(['nric','gender','religion','dob','occ','personal_income','household_income','nationality','passport_expiry_date','address_id','has_other_life_insurance',]);
		$save_data['country_id'] = $country_id;

		//check if user has dashboard access
		if($request->user()->DashboardAccess){
			$save_data = $request->only(['personal_income','household_income','occ','address_id','has_other_life_insurance']);
		}

		$request->user()->profile()->update($save_data);

		$charity = $request->user()->profile->charity;

		// if(!empty($charity) && $request->household_income <= 3000){
		// 	$charity->active = 0;
		// 	$charity->save();
		// }

		$user    = User::find($request->user()->id);
		$profile = $user->profile;
	
	    // commented add bank card on sign up
		// $bankCard               =   new BankCard();
		// $bankCard->token        =   Str::random(40);
		// $user->profile->bankCards()->save($bankCard);
		// $bankCard->saved_date   =   Carbon::now();
		// $bankCard->scheme       =   'mc';
		// $bankCard->masked_pan   =   '5111111111111118';
		// $bankCard->holder_name  =   'XXXXXXXXXX';
		// $bankCard->expiry_month =   '05';
		// $bankCard->expiry_year  =   '2025';
		// $bankCard->code         =   '100';
		// $bankCard->message      =   'Card_has_been_successfully_verified.';
		// $bankCard->save();

		
		// commented add card on sign up page
		// $bankCard               =   new BankCard();
		// $bankCard->token        =   Str::random(40);
		// $user->profile->bankCards()->save($bankCard);
		// $bankCard->saved_date   =   Carbon::now();
		// $bankCard->scheme       =   'mc';
		// $bankCard->masked_pan   =   '5111111111111118';
		// $bankCard->holder_name  =   'XXXXXXXXXX';
		// $bankCard->expiry_month =   '05';
		// $bankCard->expiry_year  =   '2025';
		// $bankCard->code         =   '100';
		// $bankCard->message      =   'Card_has_been_successfully_verified.';
		// $bankCard->save();

		Helpers::updatePremiumOnOccupation($profile);

		if($request->user()->profile->isChild()){
			$request->user()->update(['active' => 0]);
			$modal = [
				"title"   => "",
				"body"    => __('mobile.register_age_below_16'),
				"buttons" => [
					[
						"title"  => "Ok",
						"action" => "",
						"type"   => "",
					]
				]
			];
			return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal,NULL,NULL,['config' => app(UserController::class)->getStatus($request,$user),'user' => $user]);
		}else{
			$request->user()->update(['active' => 1]);
		}

		if($new){
			$request->user()->sendNotification('mobile.verification_notification_title','mobile.verification_notification_body',
				[
					'command'   => 'next_page',
					'data'      => 'verification_page',
					'id'        => 'verification',
					'buttons'   => ['verify_now','cancell'],
					'auto_read' => FALSE
				]);

//			$user->sendNotification(__('web/mobileVerify.register.app.welcome'),__('web/mobileVerify.register.app.complete'),['command' => '','data' => '']);
//			$user->notify(new Email(__('web/mobileVerify.register.email.complete')));
		}

		if($user->active){
			$next_page = $profile->isOld() ? 'payment_details_account_page' : 'dashboard_page';
		}else{
			$next_page = 'login_page';
		}

		
		$jobeligibility = $jobcheck->death!=-1 && $jobcheck->Accident!=-1 && $jobcheck->TPD !=-1 && $jobcheck->Medical !=-1;

		$charity_eligible = !$profile->isOld() && $request->household_income <= 3400 && $request->has_other_life_insurance==0 && !$profile->isChild();
		
		if($request->input('step') == 'newUser'){
			if(!$charity_eligible){			
				if($user->profile->isOld()){
					$modal = [
						"title"   => __('web/mobileVerify.register.app.welcome'),
						"body"    => __('mobile.age_above_65'),
						"buttons" => [
							[
								"title"  => __('mobile.ok'),
								"action" => NextPage::PAYMENT_DETAILS_ACCOUNT,
								"type"   => 'page',
							],
						]
					];
					return Helpers::response('success',Enum::PAGE_ACTION_TYPE_NEXT_PAGE_MODAL,$modal,NextPage::DASHBOARD,NULL,['config' => app(UserController::class)->getStatus($request,$user),'user' => $user]);
				}
				if(!$user->profile->is_local()){
					$modal = [
						"title"   => __('web/mobileVerify.register.app.welcome'),
						"body"    => __('mobile.unlocal'),
						"buttons" => [
							[
								"title"  => __('mobile.ok'),
								"action" => '',
								"type"   => '',
							],
						]
					];
					return Helpers::response('success',Enum::PAGE_ACTION_TYPE_NEXT_PAGE_MODAL,$modal,NextPage::DASHBOARD,NULL,['config' => app(UserController::class)->getStatus($request,$user),'user' => $user]);
				}
				if(!$user->profile->isOld() && $user->profile->is_local()){
					$modal = [
						"title"   => __('web/mobileVerify.register.app.welcome'),
						"body"    => __('mobile.register_complete_new_user_modal'),
						"buttons" => [
							[
								"title"  => __('mobile.buy_for_self'),
								"action" => NextPage::POLICIES,
								"type"   => "page",
							],
							[
								"title"  => __('mobile.buy_for_other'),
								"action" => NextPage::POLICIES,
								"type"   => "page",
							],
							[
								"title"  => __('mobile.invite'),
								"action" => NextPage::REFERRALDASHBOARD,
								"type"   => "page",
							]
						]
					];
					return Helpers::response('success',Enum::PAGE_ACTION_TYPE_NEXT_PAGE_MODAL,$modal,NextPage::DASHBOARD,NULL,
											 [
												 'config'           => app(UserController::class)->getStatus($request,$user),
												 'user'             => $user,
												 'is_foreign'       => !$profile->is_local(),
												 'charity_eligible' => $profile->isOld() ? FALSE : $request->household_income <= 3000,
											 ]);
				}
			}
			
			if($charity_eligible && ($jobcheck->death!=-1 && $jobcheck->Accident!=-1 && $jobcheck->TPD !=-1 && $jobcheck->Medical !=-1)){
			 
			   // $modal = [
						// "title"   => __('web/mobileVerify.register.app.welcome'),

						// 	"title"   => __('mobile.sponsored_insurance'),
						//     "body"    => __('mobile.sop_eligible_popup'),
						//     "buttons" => [
						//         [
						//             "title"  => __('mobile.yes'),
						//             "action" => NextPage::SPO_APPLY,
						//             "type"   => "page",
						// 		],
						// 		[
						//             "title"  => __('mobile.no'),
						//             "action" => NextPage::DASHBOARD,
						//             "type"   => "page",
						//         ]

						//     ]
						// ];
				
                // return Helpers::response('success',Enum::PAGE_ACTION_TYPE_NEXT_PAGE_MODAL,$modal,NextPage::DASHBOARD,null,
                //     [
                //         'config'           => app(UserController::class)->getStatus($request,$user),
                //         'user'             => $user,
                //         'is_foreign'       => !$profile->is_local(),
                //         'charity_eligible' => $profile->isOld() ? FALSE : $request->household_income <= 3170,
                //     ]);
            }
		}
        $occ_risk =false;
		$income_si_eligible=true;
		if($request->household_income > 3400){
			$income_si_eligible=false;
			$application = SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
			if($application){
				$spo_housemember = $user->profile->housemember()->get();
				if($spo_housemember->isNotEmpty()){
				$user->profile->housemember()->delete();
				}
				$application->status ='REJECTED';
				$application->active =0;
				$application->save();
				$sop_coverages=Coverage::where('payer_id',$request->user()->profile->user_id)->where('status','unpaid')->get();
				foreach($sop_coverages as $sop_coverage){
					$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
					$sop_coverage->save();
				}
		}
	}
		if($jobcheck->death==-1||$jobcheck->Accident==-1||$jobcheck->TPD ==-1||$jobcheck->Medical ==-1){

			$que_positionchange =false;
			$application = SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
			if($application){
            // $application->status ='REJECTED';
			// $application->active =0;
			// $application->save();
			// $user->profile->housemember()->delete();
			// $application->delete();
			$occ_risk =true;
			$sop_coverages=Coverage::where('payer_id',$request->user()->profile->user_id)->where('status','unpaid')->get();
			if($sop_coverages->isNotEmpty()){
			foreach ($sop_coverages as $sop_coverage){
				if($sop_coverage->product_id==1 && $jobcheck->death==-1 ){
				$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				$sop_coverage->save();
			    }elseif($sop_coverage->product_id==2 && $jobcheck->TPD ==-1){
					$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				    $sop_coverage->save();
				}elseif($sop_coverage->product_id==3 && $jobcheck->Accident==-1){
					$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				    $sop_coverage->save();
				}else{
					if($sop_coverage->product_id==5 && $jobcheck->Medical ==-1){
						$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				        $sop_coverage->save();
					}
				}
			}
		}
			}
			
		}	
		
		 $modal = [
					"title"   => __('web/mobileVerify.register.app.welcome'),
					"body"    => __('mobile.register_complete_new_user_modal'),
					"buttons" => [
						    [
								"title"  => __('mobile.buy_for_self'),
								"action" => NextPage::POLICIES,
								"type"   => "page",
							],
							[
								"title"  => __('mobile.buy_for_other'),
								"action" => NextPage::POLICIES,
								"type"   => "page",
							],
							[
								"title"  => __('mobile.invite'),
								"action" => NextPage::REFERRALDASHBOARD,
								"type"   => "page",
							]
					]
				];

		return [
			'status'  => 'success',
			'action_type' => 'nextPage_modal',
			'modal' => $modal,
			
			'message' => $user->active ? __('web/messages.profile_updated') : __('web/messages.account_disabled'),
			'data'    => [
				'is_foreign'       => !$profile->is_local(),
				'charity_eligible' => $charity_eligible,
				'income_si_eligible'=> $income_si_eligible,
				'user'             => $user,
				'charityoccchange' => $que_positionchange,
				'occ_risk'        =>  $occ_risk,
				'next_page'        => $next_page,
				'config'           => app(UserController::class)->getStatus($request,$user)
			]
		];
	}

	public function update(Request $request)
	{
		return User::all()->load('profile');
	}

	public function addOccupationAction(Request $request)
	{
		$actions     = [];
				 $coverageIds = [];
		 
				 $activeCoverages = Coverage::query()
									 ->where('covered_id',$request->user()->profile->id)
									 //->where('product_id',$product->id)
									 //->whereNotNull('last_payment_on')
									 ->orderBy('product_id','desc')
									 //->where('state',Enum::COVERAGE_STATE_ACTIVE)
									 ->get();
				 $currentAge      = Carbon::parse($request->user()->profile->dob)->age;
				 $currentGender   = $request->user()->profile->gender;
				 $changedAt       = Carbon::now()->toDateTimeString();
				 $oldOcc          = IndustryJob::where('id',$request->user()->profile->occ)->first()->name;
				 $newOcc          = IndustryJob::where('id',$request['occ'])->first()->name;
				 $industryId      =IndustryJob::where('id',$request['occ'])->first()->industry_id;
				 $jobId           =IndustryJob::where('id',$request['occ'])->first()->id;
		 
				 if(empty($activeCoverages)){
					 array_push($actions,[
						 'methods'    => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
						 'old_occ'    => $oldOcc,
						 'new_occ'    => $newOcc,
						 
					 ]);
				 }else{
					 foreach ($activeCoverages as $activeCoverage) {
						 $occ      = IndustryJob::where('industry_id',$industryId)->where('id',$jobId)->first();
						 $canCover = TRUE;
		 
						 if($activeCoverage->product->name == 'Death'){
							 $occ_loading = $occ->death;
						 }elseif($activeCoverage->product->name == 'Accident'){
							 $occ_loading = $occ->Accident;
							 if($occ_loading == -1){
								 $canCover = FALSE;
							 }
						 }elseif($activeCoverage->product->name == 'Medical'){
							 $occ_loading = $occ->Medical;
							 if($occ_loading == -1){
								 $canCover = FALSE;
							 }
		 
						 }elseif($activeCoverage->product->name == 'Disability'){
							 $occ_loading = $occ->TPD;
							 if($occ_loading == -1){
								 $canCover = FALSE;
							 }
						 }else{
							 $occ_loading = NULL;
						 }
		 
						 $deductible  = $activeCoverage->product_name == 'Medical' ? $activeCoverage->deductible : NULL;
						 $newPrice    = $activeCoverage->product->getPrice($request->user()->profile,$activeCoverage->coverage,$occ_loading,$currentAge,$deductible,$currentGender)[0];
						 $newAnnually = round($newPrice,2);
						 $oldAnnually = $activeCoverage->payment_annually;
						 $newMonthly  = $activeCoverage->product->covertAnnuallyToMonthly($newPrice);
						 $oldMonthly  = $activeCoverage->payment_monthly;
						 $oldCoverage = $activeCoverage->coverage;
						 $newCoverage = $activeCoverage->coverage;
		 
						 $firstPaymentOn      = Carbon::parse($activeCoverage->first_payment_on)->toDateTimeString();
						 $nextPaymentOn       = Carbon::parse($activeCoverage->next_payment_on)->toDateTimeString();
						 $lastPaymentOn       = Carbon::parse($activeCoverage->last_payment_on)->toDateTimeString();
						 $currrentPaymentTerm = $activeCoverage->payment_term;
						 // if($newAnnually > $oldAnnually){
						 // 	$spo_application=SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
		 
						 // }
						 if($canCover){
							 if($newAnnually > $oldAnnually){ // payed more
								 array_push($actions,[
									 'methods'          => [Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
									 'coverage_id'      => $activeCoverage->id,
									 'product_name'     => $activeCoverage->product->name,
									 'old_coverage'     => $oldCoverage,
									 'new_coverage'     => $newCoverage,
									 'payment_term'     => $activeCoverage->payment_term,
									 'old_annually'     => $oldAnnually,
									 'new_annually'     => $newAnnually,
									 'old_monthly'      => $oldMonthly,
									 'new_monthly'      => $newMonthly,
									 'old_occ'          => $oldOcc,
									 'new_occ'          => $newOcc,
									 'pro_rate'         => TRUE,
									 'changed_at'       => $changedAt,
									 'first_payment_on' => $firstPaymentOn,
									 'next_payment_on'  => $nextPaymentOn,
									 'last_payment_on'  => $lastPaymentOn,
									 'industryId'       => $industryId,
									 'jobId'            => $jobId,
								 ]);
		 
								 array_push($coverageIds,$activeCoverage->id);
							 }elseif($newAnnually < $oldAnnually){ // payed less
								 if($activeCoverage->product->isMedical()){
									 if($currrentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
										 array_push($actions,[
											 'methods'          => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
											 'coverage_id'      => $activeCoverage->id,
											 'product_name'     => $activeCoverage->product->name,
											 'old_coverage'     => $oldCoverage,
											 'new_coverage'     => $newCoverage,
											 'payment_term'     => $activeCoverage->payment_term,
											 'old_annually'     => $oldAnnually,
											 'new_annually'     => $newAnnually,
											 'old_monthly'      => $oldMonthly,
											 'new_monthly'      => $newMonthly,
											 'old_occ'          => $oldOcc,
											 'new_occ'          => $newOcc,
											 'changed_at'       => $changedAt,
											 'first_payment_on' => $firstPaymentOn,
											 'next_payment_on'  => $nextPaymentOn,
											 'last_payment_on'  => $lastPaymentOn,
											 'industryId'       => $industryId,
											 'jobId'            => $jobId,
										 ]);
		 
										 array_push($coverageIds,$activeCoverage->id);
									 }else{
										 array_push($actions,[
											 'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
											 'coverage_id'      => $activeCoverage->id,
											 'product_name'     => $activeCoverage->product->name,
											 'old_coverage'     => $oldCoverage,
											 'new_coverage'     => $newCoverage,
											 'payment_term'     => $activeCoverage->payment_term,
											 'old_annually'     => $oldAnnually,
											 'new_annually'     => $newAnnually,
											 'old_monthly'      => $oldMonthly,
											 'new_monthly'      => $newMonthly,
											 'old_occ'          => $oldOcc,
											 'new_occ'          => $newOcc,
											 'pro_rate'         => TRUE,
											 'changed_at'       => $changedAt,
											 'first_payment_on' => $firstPaymentOn,
											 'next_payment_on'  => $nextPaymentOn,
											 'last_payment_on'  => $lastPaymentOn,
											 'industryId'       => $industryId,
											 'jobId'            => $jobId,
										 ]);
		 
										 array_push($coverageIds,$activeCoverage->id);
									 }
		 
								 }else{
									 if($currrentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
										 array_push($actions,[
											 'methods'          => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
											 'coverage_id'      => $activeCoverage->id,
											 'product_name'     => $activeCoverage->product->name,
											 'old_coverage'     => $oldCoverage,
											 'new_coverage'     => $newCoverage,
											 'payment_term'     => $activeCoverage->payment_term,
											 'old_annually'     => $oldAnnually,
											 'new_annually'     => $newAnnually,
											 'old_monthly'      => $oldMonthly,
											 'new_monthly'      => $newMonthly,
											 'old_occ'          => $oldOcc,
											 'new_occ'          => $newOcc,
											 'changed_at'       => $changedAt,
											 'first_payment_on' => $firstPaymentOn,
											 'next_payment_on'  => $nextPaymentOn,
											 'last_payment_on'  => $lastPaymentOn,
											 'industryId'       => $industryId,
											 'jobId'            => $jobId,
										 ]);
									 }else{
										 array_push($actions,[
											 'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
											 'coverage_id'      => $activeCoverage->id,
											 'product_name'     => $activeCoverage->product->name,
											 'old_coverage'     => $oldCoverage,
											 'new_coverage'     => $newCoverage,
											 'payment_term'     => $activeCoverage->payment_term,
											 'old_annually'     => $oldAnnually,
											 'new_annually'     => $newAnnually,
											 'old_monthly'      => $oldMonthly,
											 'new_monthly'      => $newMonthly,
											 'old_occ'          => $oldOcc,
											 'new_occ'          => $newOcc,
											 'pro_rate'         => TRUE,
											 'changed_at'       => $changedAt,
											 'first_payment_on' => $firstPaymentOn,
											 'next_payment_on'  => $nextPaymentOn,
											 'last_payment_on'  => $lastPaymentOn,
											 'industryId'       => $industryId,
											 'jobId'            => $jobId,
										 ]);
									 }
									 array_push($coverageIds,$activeCoverage->id);
								 }
							 }else{
								 array_push($actions,[
									 'methods'          => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
									 'coverage_id'      => $activeCoverage->id,
									 'product_name'     => $activeCoverage->product->name,
									 'old_coverage'     => $oldCoverage,
									 'new_coverage'     => $newCoverage,
									 'payment_term'     => $activeCoverage->payment_term,
									 'old_annually'     => $oldAnnually,
									 'new_annually'     => $newAnnually,
									 'old_monthly'      => $oldMonthly,
									 'new_monthly'      => $newMonthly,
									 'old_occ'          => $oldOcc,
									 'new_occ'          => $newOcc,
									 'changed_at'       => $changedAt,
									 'first_payment_on' => $firstPaymentOn,
									 'next_payment_on'  => $nextPaymentOn,
									 'last_payment_on'  => $lastPaymentOn,
									 'industryId'       => $industryId,
									 'jobId'            => $jobId,
								 ]);
		 
								 array_push($coverageIds,$activeCoverage->id);
							 }
						 }else{ // not cover
							 array_push($actions,[
								 'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
								 'coverage_id'      => $activeCoverage->id,
								 'product_name'     => $activeCoverage->product->name,
								 'old_coverage'     => $oldCoverage,
								 'new_coverage'     => $newCoverage,
								 'payment_term'     => $activeCoverage->payment_term,
								 'old_annually'     => $oldAnnually,
								 'new_annually'     => $newAnnually,
								 'old_monthly'      => $oldMonthly,
								 'new_monthly'      => $newMonthly,
								 'old_occ'          => $oldOcc,
								 'new_occ'          => $newOcc,
								 'pro_rate'         => TRUE,
								 'changed_at'       => $changedAt,
								 'first_payment_on' => $firstPaymentOn,
								 'next_payment_on'  => $nextPaymentOn,
								 'last_payment_on'  => $lastPaymentOn,
								 'industryId'       => $industryId,
								 'jobId'            => $jobId,
							 ]);
		 
							 array_push($coverageIds,$activeCoverage->id);
						 }
					 }
				 }
				 
				 
				 if(empty($actions)){
					 array_push($actions,[
						 'methods'    => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
						 'old_occ'    => $oldOcc,
						 'new_occ'    => $newOcc,
						 'industryId' => $industryId,
						 'jobId'      => $jobId,
					 ]);
				 }
				 $action = auth()
						 ->user()
						 ->actions()
						 ->create([
									  'user_id' =>$request->user()->profile->user_id,
									  'type'       => Enum::ACTION_TYPE_AMENDMENT,
									  'event'   => Enum::ACTION_EVENT_CHANGE_OCCUPATION,
									  'actions' => $actions,
									  'execute_on' => Carbon::now(),
									  'status'     => Enum::ACTION_STATUS_EXECUTED,
								  ]);
					 $action->coverages()->attach($coverageIds);


					 //$action                   = Action::whereUuid($uuid)->first();
		$fullRefundActions        = [];
		$partialRefundActions     = [];
		$reduceCoverageActions    = [];
		$additionalPremiumActions = [];
		$otherActions             = [];
		//$actiontype=$actions['actions'];
		$increaseinpremium = false;
		//dd($action);
		foreach ($action->actions as $actionItem) {
			if(!empty($actionItem['methods']) && count($actionItem['methods']) > 1){
				foreach ($actionItem['methods'] as $method) {
					if($method == Enum::ACTION_METHOD_CHANGE_OCCUPATION){
						if($actionItem['new_annually']>$actionItem['old_annually']){
							$spo_application=SpoCharityFundApplication::where('user_id',$action->user->profile->user_id)->where('status','QUEUE')->first();
							if($spo_application){
							$spo_application->submitted_on =Carbon::now();
							$spo_application->form_expiry =Carbon::now()->addMonths(6);
							$spo_application->save();
							$increaseinpremium = true;
							}

						}
						
					}
				}
			}
		}
		//dd($set);

		foreach ($action->actions as $actionItem) {
			if(!empty($actionItem['methods'])){
				foreach ($actionItem['methods'] as $method) {
					if($method == Enum::ACTION_METHOD_FULL_REFUND){
						array_push($fullRefundActions,$actionItem);
					}elseif($method == Enum::ACTION_METHOD_PARTIAL_REFUND){
						array_push($partialRefundActions,$actionItem);
					}elseif($method == Enum::ACTION_METHOD_REDUCE_COVERAGE){
						array_push($reduceCoverageActions,$actionItem);
					}elseif($method == Enum::ACTION_METHOD_ADDITIONAL_PREMIUM){
						array_push($additionalPremiumActions,$actionItem);
					}else{
						array_push($otherActions,$actionItem);
					}
				}
			}
		}

		$totalRefund            = 0;
		$totalAdditionalPremium = 0;

		if(!empty($fullRefundActions)){
			$method      = Enum::ACTION_METHOD_FULL_REFUND;
			$totalRefund += $this->$method($fullRefundActions);

			// terminate
			$profile        = $action->user->profile;
			$coveragesOwner = Coverage::where('owner_id',$profile->id)->get();
			$method         = Enum::ACTION_METHOD_TERMINATE;
			$this->$method($coveragesOwner);
		}

		if(!empty($partialRefundActions)){
			$coverageIds = [];
			foreach ($action->actions as $actions) {
				if(in_array(Enum::ACTION_METHOD_PARTIAL_REFUND,$actions['methods'])){
					array_push($coverageIds,$actions['coverage_id']);
				}
			}

			$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();

			$coverageStatusGrace = Coverage::whereIn('id',$coverageIds)->whereIn('status',[Enum::COVERAGE_STATUS_GRACE_UNPAID,Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID])->get();

			if(count($coverageStatusGrace) == 0){ // status 'active' & 'active-increase'
				// refund
				$method      = Enum::ACTION_METHOD_PARTIAL_REFUND;
				$totalRefund += $this->$method($action,$partialRefundActions);

				// renew
				$method = Enum::ACTION_METHOD_RENEW_COVERAGE;
				$this->$method($action,$coveragesOwner);

				// terminate
				$method = Enum::ACTION_METHOD_TERMINATE;
				$this->$method($coveragesOwner);
			}else{ // status 'grace-unpaid' & 'grace-increase-unpaid'

				$coveragesOrders = CoverageOrder::whereIn("coverage_id",$coverageIds)->get()->pluck('order_id');
				$orders          = Order::whereIn("id",$coveragesOrders)->get();

				foreach ($orders as $order) {
					$total          = 0;
					$newCoverageIds = [];
					foreach ($order->coverages as $coverage) {
						// calc total
						foreach ($action->actions as $item) {
							if($item['coverage_id'] == $coverage->id){
								if($item['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
									$total += $item['new_monthly'];
									continue;
								}else{
									$total += $item['new_annually'];
									continue;
								}
							}
						}

						// renew
						$method        = Enum::ACTION_METHOD_RENEW_COVERAGE;
						$newCoverageId = $this->$method($action,[$coverage]);

						array_push($newCoverageIds,$newCoverageId);

						// terminate
						$method = Enum::ACTION_METHOD_TERMINATE;
						$this->$method([$coverage]);
					}

					// renew order
					$newOrder              = $order->replicate();
					$newOrder->parent_id   = $order->id;
					$newOrder->amount      = $total;
					$newOrder->true_amount = $total;
					$newOrder->save();

					// unsuccefull old order
					$order->update([
									   'status' => Enum::ORDER_UNSUCCESSFUL
								   ]);

					// renew coverage_order
					$newCoverageIds = collect($newCoverageIds)->flatten();
					$newOrder->coverages()->attach($newCoverageIds);
				}
			}

			// terminate with status 'decrease-unpaid'
			$coveragesOwnerDecrease = Coverage::where('status',Enum::COVERAGE_STATUS_DECREASE_UNPAID)
											  ->where('owner_id',$action->user_id)
											  ->get();
			$method                 = Enum::ACTION_METHOD_TERMINATE;
			$this->$method($coveragesOwnerDecrease);
		}

		if(!empty($reduceCoverageActions)){
			//dd($action, $reduceCoverageActions);
			$method = Enum::ACTION_METHOD_REDUCE_COVERAGE;
			$this->$method($action,$reduceCoverageActions);
		}

		if(!empty($changePaymentTermCoverageActions)){
			$method                 = Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE;
			$totalAdditionalPremium += $this->$method($action,$changePaymentTermCoverageActions);
		}

		if(!empty($additionalPremiumActions)){
			$method                 = Enum::ACTION_METHOD_ADDITIONAL_PREMIUM;
			$totalAdditionalPremium += $this->$method($action,$additionalPremiumActions);

			$coverageIds = [];
			foreach ($action->actions as $actions) {
				if(in_array(Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,$actions['methods'])){
					array_push($coverageIds,$actions['coverage_id']);
				}
			}

			$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();

			$coverageStatusGrace = Coverage::whereIn('id',$coverageIds)->whereIn('status',[Enum::COVERAGE_STATUS_GRACE_UNPAID,Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID])->get();

			if(count($coverageStatusGrace) == 0){ // status 'active' & 'active-increase'
				// renew
				$method = Enum::ACTION_METHOD_RENEW_COVERAGE;
				$this->$method($action,$coveragesOwner);

				// terminate
				$method = Enum::ACTION_METHOD_TERMINATE;
				$this->$method($coveragesOwner);
			}else{ // status 'grace-unpaid' & 'grace-increase-unpaid'

				$coveragesOrders = CoverageOrder::whereIn("coverage_id",$coverageIds)->get()->pluck('order_id');
				$orders          = Order::whereIn("id",$coveragesOrders)->get();

				foreach ($orders as $order) {
					$total          = 0;
					$newCoverageIds = [];
					foreach ($order->coverages as $coverage) {
						// calc total

						foreach ($action->actions as $item) {
							if($item['coverage_id'] == $coverage->id){
								if($item['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
									$total += $item['new_monthly'];
									continue;
								}else{
									$total += $item['new_annually'];
									continue;
								}
							}
						}

						// renew
						$method        = Enum::ACTION_METHOD_RENEW_COVERAGE;
						$newCoverageId = $this->$method($action,[$coverage]);

						array_push($newCoverageIds,$newCoverageId);

						// terminate
						$method = Enum::ACTION_METHOD_TERMINATE;
						$this->$method([$coverage]);
					}

					// renew order
					$newOrder              = $order->replicate();
					$newOrder->parent_id   = $order->id;
					$newOrder->amount      = $total;
					$newOrder->true_amount = $total;
					$newOrder->save();

					// unsuccefull old order
					$order->update([
									   'status' => Enum::ORDER_UNSUCCESSFUL
								   ]);

					// renew coverage_order
					$newCoverageIds = collect($newCoverageIds)->flatten();
					$newOrder->coverages()->attach($newCoverageIds);
				}
			}

			// terminate with status 'decrease-unpaid'
			$coveragesOwnerDecrease = Coverage::where('status',Enum::COVERAGE_STATUS_DECREASE_UNPAID)
											  ->where('owner_id',$action->user_id)
											  ->get();
			$method                 = Enum::ACTION_METHOD_TERMINATE;
			$this->$method($coveragesOwnerDecrease);
		}

		if(!empty($totalRefund)){
			$bankAccount = $action->user->profile->bankAccounts()->first()->account_no;
			$this->createRefund($action,$bankAccount,$totalRefund);
		}

		if(!empty($totalAdditionalPremium)){
			Credit::create([
							   'from_id'      => $action->user_id,
							   'amount'       => $totalAdditionalPremium,
							   'type'         => Enum::CREDIT_TYPE_ACTION,
							   'type_item_id' => $action->id,
						   ]);
		}

		if(!empty($otherActions)){
			foreach ($otherActions as $otherAction) {
				foreach ($otherAction['methods'] as $method) {
					if(
						$method != Enum::ACTION_METHOD_FULL_REFUND &&
						$method != Enum::ACTION_METHOD_PARTIAL_REFUND &&
						$method != Enum::ACTION_METHOD_REDUCE_COVERAGE &&
						$method != Enum::ACTION_METHOD_ADDITIONAL_PREMIUM
						//$method != Enum::ACTION_METHOD_TERMINATE &&
						//$method != Enum::ACTION_METHOD_RENEW_COVERAGE
					){
						$this->$method($action,$jobId);
						break;
					}
				}
				break;
			}
		}

      return $increaseinpremium;
	}

	public function changeOccupation($action,$jobId)
	{
		//$groupId = $this->getGroupId();

		$columnName = 'industry';
		$industryId = IndustryJob::find($action->user->profile->occ)->industry_id;
		$oldValue   = Industry::find($industryId)->name;
		//echo("Action:".$action);
		$industryId = IndustryJob::find($jobId)->industry_id;
		$newValue   = Industry::find($industryId)->name;
		//$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName  = 'occ';
		$columnAlias = 'job';
		$oldValue    = IndustryJob::find($action->user->profile->occ)->name;
		$newValue    = IndustryJob::find($jobId)->name;
		//$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias);
		//$this->updateProfile($this->jobId,$columnName);
	}

	public function fullRefund($actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;
		foreach ($actions as $actionItem) {
			if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
				$totalMonthly += $actionItem['payment_monthly'];
			}else{
				$totalAnnually += $actionItem['payment_annually'];
			}
		}

		$total = $totalAnnually + $totalMonthly;

		// less thanksgiving 10% = 0.1
		return round($total - ($total * 0.1),2);
	}

	public function terminate($coverages)
	{
		foreach ($coverages as $coverage) {
			$coverage->update(
				[
					'state'  => Enum::COVERAGE_STATE_INACTIVE,
					'status' => Enum::COVERAGE_STATUS_TERMINATE,
				]);
		}
	}

	public function renewCoverage($action,$coverages)
	{
		$actions        = $action->actions;
		$newCoverageIds = [];
		foreach ($coverages as $coverage) {
			foreach ($actions as $actionItem) {
				if($coverage->id == $actionItem['coverage_id']){
					$newCoverage                   = $coverage->replicate();
					$newCoverage->parent_id        = $actionItem['coverage_id'];
					$newCoverage->payment_monthly  = $actionItem['new_monthly'];
					$newCoverage->payment_annually = $actionItem['new_annually'];

					if($actionItem['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
						$newCoverage->deductible = $actionItem['new_coverage'];
					}else{
						$newCoverage->coverage = $actionItem['new_coverage'];
					}

					$newCoverage->save();

					array_push($newCoverageIds,$newCoverage->id);
				}
				continue;
			}
		};

		return $newCoverageIds;
	}

	public function partialRefund($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;

		foreach ($actions as $actionItem) {
			if(isset($actionItem['pro_rate'])){
				$startDate = Carbon::parse($actionItem['changed_at']);
				$endDate   = Carbon::parse($actionItem['next_payment_on']);
				$difffDays = $startDate->diffInDays($endDate);

				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['old_monthly'] > $actionItem['new_monthly']){
						$diffPrice    = ($actionItem['old_monthly'] - $actionItem['new_monthly']) / 30;
						$totalMonthly += $diffPrice * $difffDays;
					}
				}else{
					if($actionItem['old_annually'] > $actionItem['new_annually']){
						$diffPrice    = ($actionItem['old_annually'] - $actionItem['new_annually']) / 365;
						$totalMonthly += $diffPrice * $difffDays;
					}
				}
			}else{
				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['old_monthly'] > $actionItem['new_monthly']){
						$totalMonthly += $actionItem['old_monthly'] - $actionItem['new_monthly'];
					}
				}else{
					if($actionItem['old_annually'] > $actionItem['new_annually']){
						$totalAnnually += $actionItem['old_annually'] - $actionItem['new_annually'];
					}
				}
			}
		}

		$total = $totalAnnually + $totalMonthly;

		// less thanksgiving 10%
		return round($total - ($total * 0.1),2);
	}

	public function reduceCoverage($action,$actions)
	{
		$coverageIds = [];
		foreach ($action->actions as $actions) {
			if(in_array(Enum::ACTION_METHOD_REDUCE_COVERAGE,$actions['methods'])){
				array_push($coverageIds,$actions['coverage_id']);
			}
		}

		$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();

		$coverageStatusGrace = Coverage::whereIn('id',$coverageIds)->whereIn('status',[Enum::COVERAGE_STATUS_GRACE_UNPAID,Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID])->get();

		if(count($coverageStatusGrace) == 0){ // status 'active' & 'active-increase'
			// renew
			$method = Enum::ACTION_METHOD_RENEW_COVERAGE;
			$this->$method($action,$coveragesOwner);

			// terminate
			$method = Enum::ACTION_METHOD_TERMINATE;
			$this->$method($coveragesOwner);
		}else{ // status 'grace-unpaid' & 'grace-increase-unpaid'

			$coveragesOrders = CoverageOrder::whereIn("coverage_id",$coverageIds)->get()->pluck('order_id');
			$orders          = Order::whereIn("id",$coveragesOrders)->get();

			foreach ($orders as $order) {
				$total          = 0;
				$newCoverageIds = [];
				foreach ($order->coverages as $coverage) {
					// calc total
					foreach ($action->actions as $item) {
						if($item['coverage_id'] == $coverage->id){
							if($item['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
								$total += $item['new_monthly'];
								continue;
							}else{
								$total += $item['new_annually'];
								continue;
							}
						}
					}

					// renew
					$method        = Enum::ACTION_METHOD_RENEW_COVERAGE;
					$newCoverageId = $this->$method($action,[$coverage]);

					array_push($newCoverageIds,$newCoverageId);

					// terminate
					$method = Enum::ACTION_METHOD_TERMINATE;
					$this->$method([$coverage]);
				}

				// renew order
				$newOrder              = $order->replicate();
				$newOrder->parent_id   = $order->id;
				$newOrder->amount      = $total;
				$newOrder->true_amount = $total;
				$newOrder->save();

				// unsuccefull old order
				$order->update([
								   'status' => Enum::ORDER_UNSUCCESSFUL
							   ]);

				// renew coverage_order
				$newCoverageIds = collect($newCoverageIds)->flatten();
				$newOrder->coverages()->attach($newCoverageIds);
			}
		}

		// terminate with status 'decrease-unpaid'
		$coveragesOwnerDecrease = Coverage::where('status',Enum::COVERAGE_STATUS_DECREASE_UNPAID)
										  ->where('owner_id',$action->user_id)
										  ->get();
		$method                 = Enum::ACTION_METHOD_TERMINATE;
		$this->$method($coveragesOwnerDecrease);

		/*foreach ($actions as $actionItem) {
			if($actionItem['current_state'] == Enum::COVERAGE_STATE_ACTIVE && $actionItem['current_status'] == Enum::COVERAGE_STATUS_ACTIVE){
				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_TERMINATE,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'            => Enum::COVERAGE_STATE_ACTIVE,
					'status'           => Enum::COVERAGE_STATUS_ACTIVE,
					'parent_id'        => $actionItem['coverage_id'],
					'coverage'         => $actionItem['new_coverage'],
					'payment_monthly'  => $actionItem['new_monthly'],
					'payment_annually' => $actionItem['new_annually'],
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();
			}elseif($actionItem['current_state'] == Enum::COVERAGE_STATE_ACTIVE && $actionItem['current_status'] == Enum::COVERAGE_STATUS_ACTIVE_INCREASED){
				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_TERMINATE,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'            => Enum::COVERAGE_STATE_ACTIVE,
					'status'           => Enum::COVERAGE_STATUS_ACTIVE_INCREASED,
					'parent_id'        => $actionItem['coverage_id'],
					'coverage'         => $actionItem['new_coverage'],
					'payment_monthly'  => $actionItem['new_monthly'],
					'payment_annually' => $actionItem['new_annually'],
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();

			}elseif($actionItem['current_state'] == Enum::COVERAGE_STATE_ACTIVE && $actionItem['current_status'] == Enum::COVERAGE_STATUS_GRACE_UNPAID){
				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_TERMINATE,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'            => Enum::COVERAGE_STATE_ACTIVE,
					'status'           => Enum::COVERAGE_STATUS_GRACE_UNPAID,
					'parent_id'        => $actionItem['coverage_id'],
					'coverage'         => $actionItem['new_coverage'],
					'payment_monthly'  => $actionItem['new_monthly'],
					'payment_annually' => $actionItem['new_annually'],
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();

				//todo change old order

				// todo create new order
				// todo coverage_order
			}elseif($actionItem['current_state'] == Enum::COVERAGE_STATE_ACTIVE && $actionItem['current_status'] == Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID){
				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_TERMINATE,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'            => Enum::COVERAGE_STATE_ACTIVE,
					'status'           => Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID,
					'parent_id'        => $actionItem['coverage_id'],
					'coverage'         => $actionItem['new_coverage'],
					'payment_monthly'  => $actionItem['new_monthly'],
					'payment_annually' => $actionItem['new_annually'],
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();

				//todo change old order
				// todo create new order
				// todo coverage_order

			}
		}*/
	}

	public function additionalPremium($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;

		foreach ($actions as $actionItem) {
			if(isset($actionItem['pro_rate'])){
				$startDate = Carbon::parse($actionItem['changed_at']);
				$endDate   = Carbon::parse($actionItem['next_payment_on']);
				$difffDays = $startDate->diffInDays($endDate);

				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['new_monthly'] > $actionItem['old_monthly']){
						//$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / 30;
						//$totalMonthly += $diffPrice * $difffDays;
						$totalMonthly += $actionItem['new_monthly'] - $actionItem['old_monthly'];
					}
				}else{
					if($actionItem['new_annually'] > $actionItem['old_annually']){
					//	$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / 365;
					//	$totalAnnually += $diffPrice * $difffDays;
					$totalAnnually += $actionItem['new_annually'] - $actionItem['old_annually'];
					}
				}
			}else{
				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['new_monthly'] > $actionItem['old_monthly']){
						$totalMonthly += $actionItem['new_monthly'] - $actionItem['old_monthly'];
					}
				}else{
					if($actionItem['new_annually'] > $actionItem['old_annually']){
						$totalAnnually += $actionItem['new_annually'] - $actionItem['old_annually'];
					}
				}
			}
		}

		return round($totalAnnually + $totalMonthly,2);
	}

	private function createRefund($action,$bankAccount,$total): void
	{
		Refund::create([
						   'action_id'       => $action->id,
						   'payer'           => Enum::REFUND_PAYER_DEARTIME,
						   'user_id'         => $action->user_id,
						   'bank_account_id' => $bankAccount,
						   'amount'          => $total,
						   'status'          => Enum::REFUND_STATUS_PENDING,
					   ]);
	}

	//public function applyCharity(Request $request){}
}
