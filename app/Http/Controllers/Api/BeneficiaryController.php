<?php

namespace App\Http\Controllers\Api;

use App\Beneficiary;
use App\Country;
use App\SpoCharityFundApplication;
use App\Coverage;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\Controller;
use App\Individual;
use App\Notifications\Email;
use App\Notifications\Sms;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use PHPUnit\TextUI\Help;
use Symfony\Component\Console\Helper\Helper;


class BeneficiaryController extends Controller
{

	/**
	 * @api {post} api/beneficiary add beneficiary
	 * @apiVersion 1.0.0
	 * @apiName AddBeneficiary
	 * @apiGroup Beneficiary
	 * @apiDescription It adds beneficiary. Notice, if the beneficiaries percentage is less than 100, the rest will be donated to charity.
	 * @apiUse AuthHeaderToken
	 *
	 * @apiParam (Request) {Json} payload
	 * @apiParam (Request) {Array} payload[nominees]
	 * @apiParam (Request) {String} payload[nominees][name]
	 * @apiParam (Request) {String} payload[nominees][nric]
	 * @apiParam (Request) {Date} payload[nominees][dob] format d/m/y
	 * @apiParam (Request) {String} payload[nominees][email]
	 * @apiParam (Request) {String} payload[nominees][gender] male/female
	 * @apiParam (Request) {Date} payload[nominees][passport_expiry_date] format d/m/y
	 * @apiParam (Request) {String} payload[nominees][relationship] spouse/child/parent/parent_in_law/sibling/sibling_in_law/grandparent/other
	 * @apiParam (Request) {String} payload[nominees][type] trustee/hibah
	 * @apiParam (Request) {Number} payload[nominees][percentage]
	 * @apiParam (Request) {File} [payload[nominees][birth_cert]] format jpg/jpeg/png/bmp/pdf max:5000
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {String} message
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {String} data[next_page]
	 * @apiSuccess (Response (200) ) {Boolean} data[ask_child_spouse]
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */

	public function set(Request $request)
	{
		$user = $request->user()->profile;


		/********************* Check for Purchased Product (Death or Accident) for Email Notification for Nominees **********/

		$email_validation  = User::where("email", $user->user->email)->first()->id;

		$email_validation_2 = Coverage::where("payer_id", $email_validation)->where("product_id", 1)->where("status", 'active')->where("state", 'active')->count();

		/********************* Check for Purchased Product (Death or Accident) for Email Notification for Nominees **********/


		$payload = json_decode($request->json('payload'), TRUE);

		$ask_child_spouse = FALSE;
		$rules            = [
			'nominees'                        => 'required|array',
			'nominees.*.name'                 => 'required|string|',
			'nominees.*.nric'                 => 'required_without:nominees.*.uuid',
			'nominees.*.dob'                  => 'bail|required_without:nominees.*.uuid|nullable|date_format:m/d/Y',
			//            'nominees.*.nationality' => 'required_without:nominees.*.uuid',
			'nominees.*.email'                => 'required|email',
			'nominees.*.gender'               => 'bail|required_without:nominees.*.uuid|nullable|in:male,female',
			'nominees.*.passport_expiry_date' => 'nullable|date_format:d/m/Y',
			'nominees.*.relationship'         => 'required_without:nominees.*.uuid|in:spouse,child,parent,parent_in_law,sibling,sibling_in_law,grandparent,other',
			'nominees.*.type'                 => 'in:trustee,hibah',
			//sdsd
			'nominees.*.percentage'           => 'required_without:nominees.*.uuid|numeric',
			'birth_cert.*'                    => 'required_without:nominees.*.uuid|mimes:jpg,jpeg,png,bmp,pdf|max:5000',
		];
		$messages         = [
			'nominees.*.name.*'                 => 'Name is Invalid.',
			'nominees.*.nric.*'                 => 'Nric is Invalid.',
			'nominees.*.dob.*'                  => 'Date of birth is Invalid.',
			'nominees.*.nationality.*'          => 'Nationality is invalid.',
			'nominees.*.email.*'                => 'Email is invalid.',
			'nominees.*.gender.*'               => 'Gender is invalid.',
			'nominees.*.passport_expiry_date.*' => 'Passport Expiry Date is invalid.',
			'nominees.*.relationship.*'         => 'Relationship is invalid.',
			'nominees.*.type.*'                 => 'Type is invalid.',
			'nominees.*.percentage.*'           => 'Percentage is invalid.',
			'birth_cert.*'                      => 'Birth Certificate is invalid.',
		];

		Validator::make($payload, $rules, $messages)->validate();

				//added for remove card in corporate flow
				$check_coverage_offered = $user->coverages_owner()->whereIn('status',['unpaid','increase-unpaid','decrease-unpaid'])->where('state','inactive')->latest()->first()->payer_id ?? null;
				$check_coverage_by_corp = User::where('id',$check_coverage_offered)->first()->corporate_type ?? null;
				$check_user_id = $user->id;
				if($check_coverage_offered != null){
					$corp_individual_check = ($check_coverage_offered != $user->id) && $check_coverage_by_corp=='payorcorporate';
				}else { $corp_individual_check = false; }
		
			
		$total_percentage = 0;

		$payload = json_decode(json_encode($payload));

		$newBenEmails	=	[];
		foreach ($payload->nominees as $nm) {
			$newBenEmails[]		=	$nm->email;
		}

		$nominees	=	$user->nominees()->get();
		foreach($nominees as $n)
		{
            if(!in_array($n->email, $newBenEmails))
				$user->nominees()->where('email',$n->email)->delete();
		}

		foreach ($payload->nominees as $nm) {

			$total_percentage += $nm->percentage;

			// check nominee yourself
			if (strtolower($user->user->email) == strtolower($nm->email)) {
				return ['status' => 'error', 'message' => __('mobile.nominee_yourself')];
			}

			// check unique nric for new user
			$existsUser       = User::where('email', $nm->email)->exists();
			$nric             = str_replace("-", '', $nm->nric);
			$existsIndividual = Individual::where('nric', $nric)->exists();

			if (!$existsUser && $existsIndividual) {
				return ['status' => 'error', 'message' => 'The nric (' . $nric . ') has already been taken.'];
			}

			$nomineeUser = User::whereEmail($nm->email)->orWhere("uuid", $nm->uuid ?? NULL)->first() ?? NULL;
			if($nomineeUser) {
				$checkIsPayer = $user->coverages_owner()->where('payer_id', '=', $nomineeUser->id)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID,Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DEACTIVATE])->count();
				if ($checkIsPayer) {
					return ['status' => 'error', 'message' =>__('mobile.payor_as_nominee_reject',['email'=>$nm->email])];
				}
			}
		}

		if ($total_percentage > 100) {
			return ['status' => 'error', 'message' => __('mobile.nominee_total_over_100')];
		}

		foreach ($payload->nominees as $nm) {
			$nominee_id = NULL;
			$old_ben =Beneficiary::where("email", $nm->email)->where("individual_id", $user->id)->first();
			$status     = Enum::BENEFICIARY_STATUS_PENDING;
			if ($nm->type == 'hibah') {
				$status     = 'registered';
			}
			$nu         = User::whereEmail($nm->email)->orWhere("uuid", $nm->uuid ?? NULL)->first();
			$textNotif  =  __('web/messages.nominee_text_notif', ['name' => $user->name]);
			$textEmail  = __('web/messages.nominee_add_email', ['name' => $user->name]);

			$beneficiary = NULL;
			$isNewBen	=	false;
			if (!empty($nm->uuid)) {
				$nu = User::whereEmail($nm->email)->orWhere("uuid", $nm->uuid ?? NULL)->first();

				$beneficiary = Beneficiary::where("email", $nm->email)->where("individual_id", $user->id)->first();


				if ($beneficiary) {

					$beneficiary->individual_id           = $user->id;
					$beneficiary->nominee_id              = $nu->profile->id ?? NULL;
					$beneficiary->name                    = $nu->name;
					$beneficiary->email                   = $nu->email;
					$beneficiary->nationality             = $nu->profile->country_id ?? '';
					$beneficiary->nric                    = $nu->profile->nric ?? '';
					$beneficiary->gender                 = strtolower($nu->profile->gender ?? 'male');
					$beneficiary->passport_expiry_date    = (empty($nu->profile->passport_expiry_date) ? NULL : Carbon::parse($nu->profile->passport_expiry_date)->format('d/m/Y')) ?? NULL;
					$beneficiary->dob                     = Carbon::parse($nu->profile->dob)->format('m/d/Y');
					$beneficiary->relationship            = $nm->relationship;
					$beneficiary->type                    = $nm->type ?? 'trustee';
					$beneficiary->has_living_spouse_child = $nm->has_living_spouse_child ?? '0';
					//'status'                  => $nu->profile->isVerified() ? 'done' : 'pendin',
					$beneficiary->status                  = $status;
					$beneficiary->percentage              = $nm->percentage;
					//'verification_status'     => $nu && $nu->isIndividual()??$nu->profile->verification_status,


					$beneficiary->save();
					$isNewBen	=	false;
				} else {

					$beneficiary = Beneficiary::create([
						'individual_id'           => $user->id,
						'nominee_id'              => $nu->profile->id ?? NULL,
						'name'                    => $nu->name,
						'email'                   => $nu->email,
						'nationality'             => $nu->profile->country_id ?? '',
						'nric'                    => $nu->profile->nric ?? '',
						'gender'                  => strtolower($nu->profile->gender ?? 'male'),
						'passport_expiry_date'    => (empty($nu->profile->passport_expiry_date) ? NULL : Carbon::parse($nu->profile->passport_expiry_date)->format('d/m/Y')) ?? NULL,
						'dob'                     => Carbon::parse($nu->profile->dob)->format('m/d/Y'),
						'relationship'            => $nm->relationship,
						'type'                    => $nm->type ?? 'trustee',
						'has_living_spouse_child' => $nm->has_living_spouse_child ?? '0',
						//'status'                  => $nu->profile->isVerified() ? 'done' : 'pendin',
						'status'                  => $status,
						'percentage'              => $nm->percentage,
						//'verification_status'     => $nu && $nu->isIndividual()??$nu->profile->verification_status,

					]);
					$isNewBen	=	true;
				}
			} else {
				//$country = Country::where("nationality",'Malaysian')->first()->id ?? 0;
				//if(empty($country))
				$country = Country::where("nationality", $nm->nationality)->first()->id ?? 0;

				$beneficiary = Beneficiary::where("email", $nm->email)->where("individual_id", $user->id)->first();


				if ($beneficiary) {

					$beneficiary->individual_id           = $user->id;
					$beneficiary->nominee_id              = $nominee_id;
					$beneficiary->name                    = $nm->name;
					$beneficiary->email                   = $nm->email;
					$beneficiary->nationality             = $country ?? 0;
					$beneficiary->nric                    = str_replace("-", "", $nm->nric);
					$beneficiary->gender                 = $nm->gender;
					$beneficiary->passport_expiry_date    = $nm->passport_expiry_date ?? NULL;
					$beneficiary->dob                     = $nm->dob;
					$beneficiary->relationship            = $nm->relationship;
					$beneficiary->type                    = $nm->type ?? 'trustee';
					$beneficiary->has_living_spouse_child = $nm->has_living_spouse_child ?? '0';
					//'status'                  => $nu->profile->isVerified() ? 'done' : 'pendin',
					$beneficiary->status                  = $status;
					$beneficiary->percentage              = $nm->percentage;
					//'verification_status'     => $nu && $nu->isIndividual()??$nu->profile->verification_status,


					$beneficiary->save();
					$isNewBen	=	false;
				} else {

					$beneficiary = Beneficiary::create([
						'individual_id'           => $user->id,
						'nominee_id'              => $nominee_id,
						'name'                    => $nm->name,
						'email'                   => $nm->email,
						'nationality'             => $country ?? 0,
						'nric'                    => str_replace("-", "", $nm->nric),
						'gender'                  => $nm->gender,
						'passport_expiry_date'    => $nm->passport_expiry_date ?? NULL,
						'dob'                     => $nm->dob,
						'relationship'            => $nm->relationship,
						'type'                    => $nm->type ?? 'trustee',
						'has_living_spouse_child' => $nm->has_living_spouse_child ?? '0',
						'status'                  => $status,
						'percentage'              => $nm->percentage,



					]);
					$isNewBen	=	true;
				}
			}

			if ($nu && $nu->isIndividual()) {
				if ($nu->profile->isVerified()) {

					$beneficiary->update([
					     // nominee id updation
						'nominee_id' => $nu->profile->id ?? NULL,
						'status'     => "registered",
					]);
				}
				
			if(!$isNewBen && $old_ben->relationship != $beneficiary->relationship){
				$nomination_ask = ['parent', 'spouse', 'child'];
			    if (in_array($nm->relationship, $nomination_ask) && $user->religion == 'non_muslim') {
				    $ask_child_spouse = TRUE;
			 }
			}
			
			if ($isNewBen) {
				$nomination_ask = ['parent', 'spouse', 'child'];
				   if (in_array($nm->relationship, $nomination_ask) && $user->religion == 'non_muslim') {
					   $ask_child_spouse = TRUE;
				}
			   }

             if ($isNewBen) {
                    $nu->sendNotification('mobile.nominee_title', 'mobile.nominee_text_notification', [
                        'translate_data' => ['name' => $user->name],
                        'buttons' => [
                            ['title' => 'ok'],
                            /*['title' => 'reject','endpoint' => 'beneficiary/add','data' => [
                                'individual_uuid' => $nu->profile->uuid,
                                'beneficiary_id'  => $beneficiary->id,
                                'response'        => 'reject'
                            ]]*/
                        ],

                    ]);

					// echo("this is line 286 ".$user->user->profile->nric);
                }

			/********************* Nominees Email To Send only for Purchased Product (Death or Accident) for Registered User **********/

				if($isNewBen){
				if ($beneficiary->status == "registered") {

					$data['title'] = __('web/messages.ben_email_title');
				        $data['subject'] = __('web/messages.ben_email_subject');

					$textEmail  = __('web/messages.nominee_text_notification_email', ['nominee' => $beneficiary->name, 'nominator' => $beneficiary->individual->name]);

					if ($email_validation_2 > 0) {

						$nu->notify(new Sms($textNotif));
						$nu->notify(new Email($textEmail, $data));
					}
				} else {

					$data['title'] = __('web/messages.ben_email_title');
				        $data['subject'] = __('web/messages.ben_email_subject');

					$textEmail  = __('mobile.payment_success_nominee', ['nominee' => $beneficiary->name, 'nominator' => $beneficiary->individual->name]);

					if ($email_validation_2 > 0) {

						Notification::route('mail', $nm->email)->notify(new Email($textEmail, $data));
					}
				}
			}
		}

			/********************* Nominees Email To Send only for Purchased Product (Death or Accident) for Non Registered User **********/

			else {
			 
			 if($isNewBen){

				if ($beneficiary->status != "registered") {

					if ($email_validation_2 > 0) {

						 $data['title'] = __('web/messages.ben_email_title');
						 $data['subject'] = __('web/messages.ben_email_subject');

						$textEmail  = __('mobile.payment_success_nominee', ['nominee' => $beneficiary->name, 'nominator' => $beneficiary->individual->name]);

						Notification::route('mail', $nm->email)->notify(new Email($textEmail, $data));
					}
				}
			}
		}

			/********************* Nominees Email To Send only for Purchased Product (Death or Accident)**********/

		/**************************** Dev707 -  Notification Email for Serving Team *****************************************/


		$nominees_test = Beneficiary::where("individual_id", $user->id)->get();

		foreach ($nominees_test as $nominees) {

			if(($nominees->relationship == 'child') || ($nominees->relationship == 'spouse') || ($nominees->relationship == 'parent'))
			{

				$data['title'] = __('web/messages.nominee_servicingteam_email_title');

				$data['subject'] = __('web/messages.nominee_servicingteam_email_subject',['owner_name' => $user->user->profile->name, 'coverage_ref_no' => $user->user->ref_no]);

				$content = __('web/messages.nominee_servicingteam_email_content',['coverage_ref_no' => $user->user->ref_no,'owner_name' => $user->user->profile->name,'email_id' => $user->user->email,'mobile_no' => $user->user->profile->mobile, 'nric_no' => $user->user->profile->nric]);

				$email = __('mobile.test_recipient');

				try {
					Notification::route('mail', $email)->notify(new Email($content, $data));
				}catch (\Exception $e){
				}
			}

			elseif (($nominees->relationship == 'Sponsored Insurance') || ($nominees->email == "Charity@Deartime.com" )){

				$data['title'] = __('web/messages.appointment_trustee_servicingteam_email_title');

				$data['subject'] = __('web/messages.appointment_trustee_servicingteam_email_subject',['owner_name' => $user->user->profile->name, 'coverage_ref_no' => $user->user->ref_no]);

				$content = __('web/messages.appointment_trustee_servicingteam_email_content',['coverage_ref_no' => $user->user->ref_no,'owner_name' => $user->user->profile->name,'email_id' => $user->user->email,'mobile_no' => $user->user->profile->mobile, 'nric_no' => $user->user->profile->nric]);

				$email = __('mobile.test_recipient');

				try {
					Notification::route('mail', $email)->notify(new Email($content, $data));
				}catch (\Exception $e){
				}

			}
		}
		
		/**************************** Dev707 -  Notification Email for Serving Team *****************************************/

			
		}

		if ($user->religion == 'muslim')
			$ask_child_spouse = FALSE;

		if ($user->thanksgiving()->count() == 0) {
			$nextPage = NextPage::THANKSGIVING;
		} else if ((($user->bankCards()->count() == 0 || $user->bankAccounts()->count() == 0)) && !$user->is_charity() && $corp_individual_check) {
			$nextPage = NextPage::PAYMENT_DETAIL;
		} else {
			if($user->is_charity()){
                //$spo_coverage =Coverage::where('payer_id',$user->user_id)->where('status','unpaid')->get();
				$spo_coverage =Coverage::where('payer_id',$user->user_id)->where('sponsored',1)->where('status','unpaid')->get();
				$spo_da_coverage=Coverage::where('payer_id',$user->user_id)->where('sponsored',1)->whereIn('product_name',[Enum::PRODUCT_NAME_ACCIDENT,Enum::PRODUCT_NAME_DEATH])->get();
				if($user->verification && $spo_coverage->isNotEmpty() && $user->thanksgiving()->count() != 0 && ($user->underwritings()->count()!=0) && ($spo_da_coverage->isNotEmpty()? ($user->beneficiaries()->count()!= 0):true)){
					$underwriting =$user->underwritings()->first();
				if(($underwriting->death =='1')||($underwriting->disability =='1')||($underwriting->ci =='1')||($underwriting->medical =='1')){
				$spo_application=SpoCharityFundApplication::where('user_id',$user->user_id)->whereIn('status',['PENDING','SUBMITTED','QUEUE'])->first();
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
			}
		}
				
				
			}{
			if (!$user->isVerified()){
				$nextPage = NextPage::VERIFICATION;
			}
			else{
				$nextPage = NextPage::ORDER_REVIEW;
			}
			}
		}

		
		/*if($ask_child_spouse){
		    $modal = [
                "body" => __('mobile.non_muslim_spouse_child_parent'),
                "buttons" => [
                    [
                        "title" => __('mobile.ok'),
                        "action" => $nextPage,
                        "type" => "page",
                    ]
                ]
            ];
            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);

        }*/

		return [
			'status' => 'success',
			'data'   =>
			[
				'next_page'        => $nextPage,
				'ask_child_spouse' => $ask_child_spouse
			]
		];
	}

	/**
	 * @api {get} api/beneficiary get beneficiaries
	 * @apiVersion 1.0.0
	 * @apiName GetBeneficiaries
	 * @apiGroup Beneficiary
	 * @apiDescription It gets user's nominees
	 * @apiUse AuthHeaderToken
	 *
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {Array} data[nominees]
	 * @apiSuccess (Response (200) ) {Boolean} data[has_only_parent]
	 * @apiSuccess (Response (200) ) {Boolean} data[is_muslim]
	 * @apiSuccess (Response (200) ) {Boolean} data[has_living_spouse_child]
	 *
	 * @apiSuccessExample {json} Success Response:
	 * {
	 *    "status": "success",
	 *    "data": {
	 *        "nominees": [
	 *            {
	 *                "name": "Emiii",
	 *                "email": "Emi@gmail.com",
	 *                "nric": "900708111111",
	 *                "gender": "male",
	 *                "passport_expiry_date": "",
	 *                "dob": "08/07/1990",
	 *                "relationship": "parent",
	 *                "type": "trustee",
	 *                "percentage": 40,
	 *                "status": "pending",
	 *                "NationalityName": "Malaysian"
	 *            },
	 *            {
	 *                "name": "Sema",
	 *                "email": "Se@gmail.com",
	 *                "nric": "980102111111",
	 *                "gender": "male",
	 *                "passport_expiry_date": "",
	 *                "dob": "02/01/1998",
	 *                "relationship": "spouse",
	 *                "type": "trustee",
	 *                "percentage": 50,
	 *                "status": "pending",
	 *                "NationalityName": "Malaysian"
	 *            },
	 *            {
	 *                "name": "Charity Insurance",
	 *                "email": "Charity@Deartime.com",
	 *                "nric": "950101000000",
	 *                "gender": "male",
	 *                "passport_expiry_date": null,
	 *                "dob": "01/01/1995",
	 *                "relationship": "Gift Recipient",
	 *                "type": "hibah",
	 *                "percentage": 10,
	 *                "status": "sent-email",
	 *                "NationalityName": "Malaysian"
	 *            }
	 *        ],
	 *        "has_only_parent": false,
	 *        "is_muslim": true,
	 *        "has_living_spouse_child": false
	 *    }
	 *}
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */

	public function get(Request $request)
	{
		$nominees = $request->user()->profile->nominees;
		foreach ($nominees as $nu) {
			$nominee = User::whereEmail($nu->email)->orWhere("uuid", $nu->uuid ?? NULL)->first();
			$beneficiary = $nu;
			
			if ($nominee && $nominee->isIndividual()) {
				$beneficiary->name                    = $nominee->profile->name;
				$beneficiary->email                   = $nominee->email;
				$beneficiary->nationality             = $nominee->profile->country_id ?? 0;
				$beneficiary->nric                    = str_replace("-", "", $nominee->profile->nric);
				$beneficiary->gender                 = $nominee->profile->gender;
				$beneficiary->passport_expiry_date    = Carbon::parse($nominee->profile->passport_expiry_date)->format('d/m/Y');
				$beneficiary->dob                     = Carbon::parse($nominee->profile->dob)->format('m/d/Y');

				$beneficiary->save();
				
			} 
			
			if ($nominee && $nominee->isIndividual() && $nominee->profile->isVerified()) {

				$beneficiary->status = 'registered';
				$beneficiary->save();
			} else {
				if ($nu->type == 'hibah') {
					$beneficiary->status   = 'registered';
					$beneficiary->save();
				} else {
					$beneficiary->status = 'pending';
					$beneficiary->save();
				}
			}
		}
		return [
			'status' => 'success',
			'data'   => [
				'nominees'                => $nominees,
				'has_only_parent'         => $request->user()->profile->hasOnlyParentNominee(),
				'is_muslim'               => $request->user()->profile->religion == 'muslim',
				'has_living_spouse_child' => $request->user()->profile->has_living_spouse_child == '1',
			]
		];
	}

	public function add(Request $request)
	{
		// validation
		$request->validate([
			'individual_uuid'   => 'required|exists:individuals,uuid',
			'notification_uuid' => 'required|exists:notifications,uuid',
			'beneficiary_id'    => 'required|exists:beneficiaries,id',
			//'response'          => 'required|in:accept,reject',
		]);

		$individualUuid   = $request->get('individual_uuid');
		$notificationUuid = $request->get('notification_uuid');
		$beneficiaryId    = $request->get('beneficiary_id');
		//$response         = $request->get('response');

		$individual   = Individual::where('uuid', $individualUuid)->first();
		$notification = \App\Notification::where('uuid', $notificationUuid)->first();
		$beneficiary  = Beneficiary::find($beneficiaryId);

		/*if($response == 'accept'){
			$beneficiary->update([
									 'nominee_id' => $individual->id,
									// 'status'     => Enum::BENEFICIARY_STATUS_APPROVE,
								 ]);

			$notification->update([
									  'show'       => 0,
									  'execute_on' => Carbon::now()
								  ]);

			return [
				'status'  => 'success',
				//'message' => 'you accept nominee'
			];
		}elseif($response == 'reject'){
			$beneficiary->update([
									 'status' => Enum::BENEFICIARY_STATUS_DECLINED,
								 ]);

			$beneficiary->delete();

			$notification->update([
									  'show'       => 0,
									  'execute_on' => Carbon::now()
								  ]);

			return [
				'status'  => 'success',
				'message' => 'you dont accept nominee'
			];
		}else{
			return [
				'status'  => 'error',
				'message' => 'error'
			];
		}*/
	}
}
