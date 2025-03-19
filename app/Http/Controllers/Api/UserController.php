<?php

namespace App\Http\Controllers\Api;

namespace App\Http\Controllers\Api;
use App\SpoHouseholdMembers;
use App\SpoCharityFundApplication;
use App\Coverage;
use App\Helpers;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\CustomerVerification;
use App\CustomerVerificationDetail;
use App\Individual;
use App\Notification;
use App\IndustryJob;
use App\Industry;
use App\User;
use App\UserModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Helpers\NextPage;

class UserController extends Controller
{
	public function getTerms()
	{
		return
			[
				'status' => 'success',
				'data' =>
				[
					'important_notice' => route('page.index', ['importantNotice', 'mobile' => '1']),
					'declaration' => route('page.index', ['declaration', 'mobile' => '1']),
					'term_of_use' => route('page.index', ['termsOfUse', 'mobile' => '1']),
					'privacy_statement' => route('page.index', ['privacyStatement', 'mobile' => '1']),
					'privacy_policy' => route('page.index', ['privacy-policy', 'mobile' => '1']),
					'personal_data_protection' => route('page.index', ['personal-data-protection', 'mobile' => '1']),
				],

			];
	}


	/**
	 * @api {get} api/setLocale get status
	 * @apiVersion 1.0.0
	 * @apiName setLocale
	 * @apiGroup User
	 *
	 * @apiDescription It changes language. Customer can select en/ch/bm
	 *
	 * @apiParam (Request) {String} locale_name en/ch/bm
	 * @apiParam (Request) {String} client web/mobile
	 *
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {String} message
	 * @apiSuccess (Response (200) ) {String} data
	 *
	 * @apiSuccessExample {json} Success Response:
	 *{
	 *   "status": "success",
	 *   "message": "",
	 *   "data":""
	 *}
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */

	public function setLocale(Request $request)
	{

		App::setLocale($request->locale_name ?? 'en');

		//   Cache::pull('lang.js');

		$user = $request->user();
		if (empty($user)) // what is this?
			$user = auth()->user();
		if (empty($user))
			$user = auth('api')->user();

		if (!empty($user)) {
			$user->setLocale($request->locale_name);
		}

		return $this->lang($request->client ?? 'web');
	}

	public function lang($client)
	{
		$strings = Cache::remember('lang.js', 1, function () use ($client) {
			$lang = config('app.locale');

			$files   = glob(resource_path('lang/' . $lang . '/*.php'));
			$strings = new \stdClass();

			foreach ($files as $file) {
				if (basename($file, '.php') === $client) {
					$name = basename($file, '.php');
					//                    $strings[$name] = require $file;
					$strings = require $file;
				}
			}

			return $strings;
		});

		return ['status' => 'success', 'message' => '', 'data' => json_encode($strings)];
		//   header('Content-Type: text/javascript');
		//   echo('window.i18n = ' . json_encode($strings) . ';');
		//    exit();
	}

	public function updateProfile(Request $request)
	{
		if (Auth::user()->active != '1') {
			return ['status' => 'error', 'message' => __('web/messages.account_disabled')];
		}
		if(!Auth::user()->isIndividual()){
			return CompanyController::updateProfile($request);
		}
	}

	/**
	 * @api {get} api/getStatus get status
	 * @apiVersion 1.0.0
	 * @apiName GetStatus
	 * @apiGroup User
	 *
	 * @apiDescription It checks app version
	 *
	 * @apiParam (Request) {String} version
	 * @apiParam (Request) {Object} [user]
	 *
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {String} message
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {String} data[next_page]
	 * @apiSuccess (Response (200) ) {String} data[config]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][dashboard_access]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][access_allowed]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][kyc_done]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][is_child]
	 * @apiSuccess (Response (200) ) {String} data[config][server_message]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][is_old]
	 * @apiSuccess (Response (200) ) {Boolean} data[config][has_new_notification]
	 *
	 * @apiSuccessExample {json} Success Response:
	 *{
	 *   "status": "success",
	 *   "message": "",
	 *   "data": {
	 *       "next_page": "login_page",
	 *       "config": {
	 *           "dashboard_access": false,
	 *           "access_allowed": true,
	 *           "kyc_done": false,
	 *           "server_message": "",
	 *           "is_child": false,
	 *           "is_old": false,
	 *           "has_new_notification": 0
	 *      }
	 *   }
	 *}
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */

	public function getStatus(Request $request, $user = NULL)
	{
		$auth       = auth('api');
		$user       = \auth('api')->user();
		$server_msg = '';
		$status     = 'success';
		$next_page  = '';
		if (!empty($request->input('version'))) {
			if ($request->input('version') < config('services.application_version')) {
				$server_msg = __('web/messages.app_outdated');
				$status     = 'success';
				$next_page  = 'shutdown';
				return [
					'status'         => $status,
					'server_message' => $server_msg,
					'data'           => [
						'next_page' => $next_page,
						'config'    => [
							'access_allowed' => FALSE,
						]
					]
				];
			}
		}

		if(!$auth->check() && empty($user)){
			$user = User::where("email",$request->input('email'))->orderBy("created_at","desc")->first();
			if($user != null){
				$kyc_1                = $user->profile->id;
				$kyc 				  = CustomerVerification::where("individual_id",$kyc_1)->where("status","Accepted")->orderBy("created_at","desc")->first();
				if($kyc == null){
					$kyc = FALSE;
					$dashboard_access     = FALSE;
				}else{
					$kyc = TRUE;
					$dashboard_access     = TRUE;
				}
				$access_allowed       = TRUE;
				$is_child             = FALSE;
				$is_old               = FALSE;
				$has_new_notification = 0;
				$bank_account_done    = 0;
				$nationality_allowed  = $user->profile->country_id == 135 ? TRUE : FALSE;
			}else{
				$next_page            = 'login_page';
				$kyc 				  = FALSE;
				$dashboard_access     = FALSE;
				$access_allowed       = TRUE;
				$is_child             = FALSE;
				$is_old               = FALSE;
				$has_new_notification = 0;
				$bank_account_done    = 0;
				$nationality_allowed  = FALSE;
			}
		}

		else{
			if(empty($user)){
				$next_page            = $auth->user()->getNextPage();
				$dashboard_access     = $auth->user()->dashboardAccess;
				$kyc                  = $auth->user()->profile->verification ?? NULL;
				$access_allowed       = $auth->user()->active == '1';
				$has_new_notification = $auth->user()->messages()->where("is_read", "0")->count();
				$nationality_allowed  = $auth->user()->profile->country_id == 135 ? TRUE : FALSE;

				if (!empty($auth->user()->profile)) {
					$is_child          = ($auth->user()->profile->age() ?? 20) < 16;
					$is_old            = ($auth->user()->profile->age() ?? 20) >= 65;
					$bank_account_done = $auth->user()->profile->bankAccounts()->count() ?? 0;
				} else {
					$is_child = $is_old = FALSE;
				}
				if (!$access_allowed) {
					$server_msg = __('web/messages.account_disabled');
					$status     = 'success';
					$next_page  = 'login_page';
				}
			} else {
				$next_page            = $user->getNextPage();
				$dashboard_access     = $user->dashboardAccess;
				$kyc                  = $user->profile->verification ?? NULL;
				$access_allowed       = $user->active == '1';
				$has_new_notification = $user->messages()->where("is_read", "0")->count();
				$is_child             = ($user->profile->age() ?? 20) < 16;
				$is_old               = ($user->profile->age() ?? 20) >= 65;
				$bank_account_done    = $user->profile->bankAccounts()->count() ?? 0;
				$nationality_allowed  = $user->profile->country_id == 135 ? TRUE : FALSE;

				if (!$access_allowed) {
					$server_msg = __('web/messages.account_disabled');
					$status     = 'success';
					$next_page  = 'login_page';
				}
			}
			
			$kyc = CustomerVerification::where("individual_id",$user->profile->id)->where("status","Accepted")->orderBy("created_at","desc")->first();
			 
				if($kyc == null){
					$kyc = FALSE;
				}else{
					$kyc = TRUE;
				}
			
			//$kyc = $kyc && $kyc->count() > 0;
		}


		return ['status' => $status, 'message' => '', 'data' => ['next_page' => $next_page, 'config' => [
			'dashboard_access'     => $dashboard_access,
			'access_allowed'       => $access_allowed,
			'kyc_done'             => $kyc,
			'server_message'       => $server_msg,
			'is_child'             => $is_child,
			'is_old'               => $is_old,
			'has_new_notification' => $has_new_notification,
			'chat_id'              => $auth->user()->chat_id ?? '',
			'bank_account_done'    => $bank_account_done ?? 0,
			'nationality_allowed'  => $nationality_allowed,
		]]];
	}


	/**
	 * @api {get} api/get-time-tube get timetube
	 * @apiVersion 1.0.0
	 * @apiName GetTimeTube
	 * @apiGroup User
	 *
	 * @apiUse AuthHeaderToken
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {Number} time_tube second
	 * @apiSuccess (Response (200) ) {Object} active_coverages number of active coverages
	 *
	 */

	public function getTimeTube()
	{
		$user                 = \auth('api')->user();
		$profile              = Individual::where('user_id', $user->id)->first();
		$timeTube             = $this->calcTimeTube($profile);
		$countActiveCoverages = Coverage::where('covered_id', $profile->id)
			->where('status', Enum::COVERAGE_STATUS_ACTIVE)->count();
		return [
			'status'           => 'success',
			'time_tube'        => $timeTube,
			'active_coverages' => $countActiveCoverages
		];
	}

	public function calcTimeTube($user)
	{
		$timeTube        = 0;
		$activeCoverages = Coverage::where('covered_id', $user->id)
			->where('status', Enum::COVERAGE_STATUS_ACTIVE)
			->groupBy('last_payment_on')->get();

		$fulfilledCoverages = Coverage::where('covered_id', $user->id)
			->where(function ($q) {
				$q->where('status', Enum::COVERAGE_STATUS_FULFILLED);
			})
			->orderBy('next_payment_on', 'desc')
			->groupBy('last_payment_on');

		$dateNow     = now();
		$overlapDays = 0;

		for ($i = 0; $i < count($activeCoverages); $i++) {
			for ($j = $i + 1; $j < count($activeCoverages); $j++) {
				$overlapDays += $this->datesOverlap($activeCoverages[$i]->last_payment_on, $dateNow, $activeCoverages[$j]->last_payment_on, $dateNow);
			}
		}

		foreach ($activeCoverages as $coverage) {
			$timeTube += Carbon::parse($coverage->last_payment_on)->diffInSeconds($dateNow);
		}

		$coverages = $fulfilledCoverages->get();
		for ($i = 0; $i < count($coverages); $i++) {
			for ($j = $i + 1; $j < count($coverages); $j++) {
				$overlapDays += $this->datesOverlap($coverages[$i]->last_payment_on, $coverages[$i]->next_payment_on, $coverages[$j]->last_payment_on, $coverages[$j]->next_payment_on);
			}
		}

		foreach ($coverages as $coverage) {
			$timeTube += Carbon::parse($coverage->last_payment_on)->diffInSeconds($coverage->next_payment_on);
		}

		return ($timeTube - $overlapDays);
	}

	public function datesOverlap($startOne, $endOne, $startTwo, $endTwo)
	{

		if (Carbon::parse($startOne) <= Carbon::parse($endTwo) && Carbon::parse($endOne) >= Carbon::parse($startTwo)) { //If the dates overlap
			$res = min(Carbon::parse($endOne), Carbon::parse($endTwo))->diffInSeconds(max(Carbon::parse($startTwo), Carbon::parse($startOne)));
			return $res; //return how many seconds overlap
		}

		return 0; //Return 0 if there is no overlap
	}


	/**
	 * @api {get} api/message-history get message history
	 * @apiVersion 1.0.0
	 * @apiName MessageHistory
	 * @apiGroup User
	 *
	 * @apiUse AuthHeaderToken
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {Object} data messages
	 *
	 */

	public function getMessageHistory(Request $request)
	{
		$history = $request->user()->messages()->where('show', 1)->orderBy("created_at", "desc")->paginate(10);
		//        $request->user()->messages()->where("auto_read","1")->update(["is_read"=>'1']);
		return ['status' => 'success', 'data' => $history];
	}

	public function checkUser(Request $request)
	{
		$email    = $request->input('email');
		$dob      = $request->input('dob');
		$is_child = FALSE;

		$passport = str_replace("-", "", $request->input('passport'));

		if (!empty($dob)) {
			$age      = Carbon::createFromFormat("d/m/Y", $request->input('dob'))->age ?? 17;
			$is_child = $age <= 16;
		}

		if (!empty($email) && (empty($age) || $age > 16)) {
			$check = User::WithPendingPromoted()->whereEmail($email)->first();
			if (!empty($check)) {
				if ($check->profile->nric != $passport && !empty($passport)) {
					return ['status' => 'error', 'message' => __('web/product.nric_invalid'), 'is_child' => $is_child];
				}
			}
		} elseif (!empty($dob) && !empty($passport)) {
			//child
			if ($age <= 16 && $request->input('title') == 'buy_for_others_title') {
				$check = Individual::OnlyChild()->where('nric', $passport)->first();

				if (empty($check)) {
					return ['status' => 'error', 'message' => __('web/messages.child_register'), 'is_child' => $is_child];
				}

				if (!empty($email)) {
					return ['status' => 'error', 'message' => __('web/messages.child_register_email'), 'is_child' => $is_child];
				}

				return ['status' => 'success', 'data' => ['name' => $check->name, 'uuid' => $check->uuid, 'type' => 'child'], 'is_child' => $is_child];
			} else {
				$check = User::WithPendingPromoted()->whereEmail($email)->first();
			}
		} elseif (!empty($passport)) {
			$check = Individual::withChild()->where('nric', $passport)->first();

			if (empty($check)) {
				return [
					'status'   => 'error',
					//'message'  => __('web/messages.child_register'),
					'is_child' => $is_child
				];
			}

			return [
				'status'   => 'success',
				'data'     => [
					'name'     => $check->name,
					'dob'      => Carbon::parse($check->dob)->format('m/d/Y'),
					'passport' => $check->nric,
					//Dev 512,514
					'nationality' =>  $check->nationality,
					'uuid'     => NULL
				],
				'is_child' => $is_child
			];
		}

		if (!empty($check)) {
			return [
				'status'   => 'success',
				'data'     => [
					'name'     => $check->name,
					'dob'      =>Carbon::parse($check->profile->dob)->format('m/d/Y'),
					'passport' => $check->profile->nric,
					//Dev 512,514
					'nationality' =>  $check->profile->nationality,
					'uuid'     => $check->uuid,
					//dev-484 - Beneficiary details -> for given email -> not autopopulate issue for the fields
					// email and passport
					//If passport_expiry_date -> UI blocks further proceeding. So setting a default date temp
					'email'    => $check->email,
					'passport_expiry_date' => Carbon::parse($check->profile->passport_expiry_date)->format('d/m/Y')

				],
				'is_child' => $is_child
			];
		}

		return [
			'status'   => 'success',
			'data'     => [
				'name' => '',
				'uuid' => ''
			],
			'is_child' => $is_child
		];
	}

	/*public function checkUser(Request $request)
	{
		$email = $request->input('email');
		$dob = $request->input('dob');
		$is_child = false;

		$passport = str_replace("-","",$request->input('passport'));
		if(!empty($dob)) {
			$age = Carbon::createFromFormat("d/m/Y", $request->input('dob'))->age ?? 17;
			$is_child = $age <= 16;
		}

		if(!empty($email) && (empty($age) || $age > 16)) {

			$check = User::WithPendingPromoted()->whereEmail($email)->first();
			if(!empty($check)){
				if($check->profile->nric != $passport && !empty($passport))
					return ['status' => 'error', 'message' => __('web/product.nric_invalid'),'is_child'=>$is_child];
			}

		} elseif(!empty($dob) && !empty($passport)){
			//child

			if($age <= 16 && $request->input('title') == 'buy_for_others_title'){
				$check = Individual::OnlyChild()->where('nric',$passport)->first();
				if(empty($check))
					return ['status' => 'error', 'message' => __('web/messages.child_register'),'is_child'=>$is_child];

				if(!empty($email))
					return ['status' => 'error', 'message' => __('web/messages.child_register_email'),'is_child'=>$is_child];


				return ['status' => 'success', 'data' => ['name'=>$check->name,'uuid'=>$check->uuid,'type'=>'child'],'is_child'=>$is_child];
			}else
				$check = User::WithPendingPromoted()->whereEmail($email)->first();

		}
		if(!empty($check))
			return ['status' => 'success', 'data' => ['name'=>$check->name,'uuid'=>$check->uuid],'is_child'=>$is_child];

		return ['status' => 'success', 'data' => ['name'=>'' ,'uuid'=>''],'is_child'=>$is_child];
	}*/

	public function msgAction(Request $request)
	{
		$user = $request->user();
		$msg  = Notification::whereUuid($request->input('uuid'))->first();
		if (empty($msg))
			abort(404);

		//accept/reject nominee
		//accept/reject pay_other

		$data = json_decode($msg->data);
		switch ($request->input('action')) {
			case 'accept_nominee':
				$msg->is_read    = '1';
				$data            = json_decode($msg->data, TRUE);
				$data['buttons'] = [];
				$msg->data       = json_encode($data);
				$msg->save();

				return ['status' => 'success', 'data' => ['info' => 'nominee_accepted']];
				break;
			case 'reject_nominee':

				$msg->is_read    = '1';
				$data            = json_decode($msg->data, TRUE);
				$data['buttons'] = [];
				$msg->data       = json_encode($data);
				$msg->save();

				return ['status' => 'success', 'data' => ['info' => 'nominee_rejected']];
				break;
			case 'accept_pay_other':

				$data = json_decode($msg->data, TRUE);

				$payer_profile = User::where("uuid", $data['page_data']['payer_id'])->first()->profile ?? NULL;
				$msg->is_read  = '1';

				$data['buttons'] = [];
				$msg->data       = json_encode($data);

				$msg->save();

				$pay_individual = $data['page_data']['user_id'] ?? 0;

				if (!empty($payer_profile)) {
					$payer_user = $payer_profile->user;
					if (!empty($payer_user)) {
						$user->profile->coverages_owner()->where("payer_id", $payer_user->id)->update(['is_accepted_by_owner' => "1"]);
						/*$payer_user->sendNotification(
							__('notification.pay_other_accepted.title'),
							__('notification.pay_other_accepted.body'),
							[
								'command'   => 'next_page',
								'page_data' => [
									'fill_type' => 'pay_for_others',
									'user_id'   => $pay_individual
								],
								'data'      => 'order_review_page',
								'id'        => 'pay_other_accept',
								'buttons'   => [
									['title' => 'pay', 'action' => 'pay_now_pay_other'],
									['title' => 'decline', 'action' => 'cancel_pay_other']
								],
								'auto_read' => TRUE
							]
						);*/
					}
				}
				return ['status' => 'success', 'data' => ['next_page' => NextPage::POLICIES], 'next_page' => NextPage::POLICIES];
				break;
			case 'reject_pay_other_confirm':
				return ['status' => 'success', 'data' => ['msg' => __('mobile.reject_offer_confirm'), "buttons" => [
					[
						"title"  => __('web/messages.yes'),
						"action" => 'reject_pay_other',
						"type"   => "api",
					],
					[
						"title"  => __('web/messages.no'),
						"action" => '',
						"type"   => "",
					],
				]]];
				//return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
				break;
			case 'reject_pay_other':
				$data = json_decode($msg->data, TRUE);

				$payer_profile = User::where("uuid", $data['page_data']['payer_id'])->first()->profile ?? NULL;
				$msg->is_read  = '1';

				$data['buttons'] = [];
				$msg->data       = json_encode($data);

				$msg->save();

				$pay_individual = $data['page_data']['user_id'] ?? 0;

				if (!empty($payer_profile)) {
					$payer_user = $payer_profile->user;
					if (!empty($payer_user)) {

						$user->profile->coverages_owner()->where("payer_id", $payer_user->id)
							->where('status', Enum::COVERAGE_STATUS_INCREASE_UNPAID)
							->where('corporate_user_status',null)
							->update([
								'status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE,
								'state' => Enum::COVERAGE_STATE_INACTIVE
							]);

						$user->profile->coverages_owner()->where("payer_id", $payer_user->id)
							->where('status', Enum::COVERAGE_STATUS_UNPAID)
							->where('corporate_user_status',null)
							->update([
								'status' => Enum::COVERAGE_STATUS_TERMINATE,
								'state' => Enum::COVERAGE_STATE_INACTIVE
							]);

						$emailText = __('mobile.payor_owner_rejected_purchase', [
							'payer_name' => $payer_profile->name,
							'owner_name' => $user->profile->name,
							'him_her'	 => ($user->profile->gender == 'female') ? 'her' : 'him' ?? 'him',
						]);
						$payer_user->sendNotification(__('mobile.payor_owner_rejected_purchase_title'),strip_tags($emailText),['command' => 'next_page','data' => 'policies_page']);
						$payer_user->notify(new \App\Notifications\Email($emailText, ['subject' => __('mobile.payor_owner_rejected_purchase_title')]));
					}
				}
				return ['status' => 'success', 'data' => ['info' => 'payment_rejected'], 'next_page' => NextPage::POLICIES];
				break;
			case 'pay_now_pay_other':

				$msg->is_read    = '1';
				$data            = json_decode($msg->data, TRUE);
				$data['buttons'] = [];
				$msg->data       = json_encode($data);
				$msg->save();

				return [
					'status' => 'success',
					'data'   => [
						'info'             => 'payment_accepted',
						'next_page'        => 'order_review_page',
						'next_page_params' => ['fill_type' => 'pay_for_others', 'user_id' => ($data['page_data']['user_id'] ?? '')]
					],
					'url'    => Helpers::route('pay_for_other_confirm', [$data['page_data']['user_id'] ?? '']),

				];
				break;
				case 'accept_payeroffer_confirm_spo':
					return ['status' => 'success', 'data' => ['msg' => __('mobile.corporateoffer_spo_confirm'), "buttons" => [
						[
							"title"  => __('web/messages.yes'),
							"action" => 'delete_spo',
							"type"   => "api",
						],
						[
							"title"  => __('web/messages.no'),
							"action" => '',
							"type"   => "",
						],
					]]];
				
				  break;
					
				case 'delete_spo':
	
					$data = json_decode($msg->data, TRUE);
	
					$msg->is_read  = '1';
	
					$data['buttons'] = [];
					$msg->data       = json_encode($data);
	
					$msg->save();

					
	
					$spo_application=SpoCharityFundApplication::where('user_id',$user->id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
	
					if($spo_application){
			  
					$spo_application->status ='CANCELLED';
					$spo_application->active =0;
					if($spo_application->Corporate_SPO_confirm==1){
					$spo_application->remark = 'This application is cancelled due to corporate payor offer';
					}
					$spo_application->save();
					//$request->user()->profile->housemember()->delete();
					//$spo_application->delete();
					$sop_coverages=Coverage::where('payer_id',$user->id)->where('owner_id',$user->profile->id)->where('status','unpaid')->get();
					 if($sop_coverages->isNotEmpty()){
						foreach ($sop_coverages as $sop_coverage){
						   $sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
						   $sop_coverage->save();
							  }
							}
						}
	
	
					return ['status' => 'success', 'data' => ['next_page' => NextPage::POLICIES], 'next_page' => NextPage::POLICIES];
					break;
			default:
				return [
					'status' => 'success',
					'data'   => [
						'next_page'        => 'dashboard_page',
						'next_page_params' => []
					],
					'url'    => Helpers::route('dashboard_page'),

				];
				break;
		}
	}


	public function setChat(Request $request)
	{
		$user          = $request->user();
		$user->chat_id = $request->chat_id;
		$user->save();
		return [
			'status'  => 'success',
			'message' => 'add successfully'
		];
	}

    public function updateNotification(Request $request)
	{
		$uuid	=	$request->input('message_id');
		if (!empty($uuid) && $request->user()->messages()->where("uuid", $uuid)->update(["is_read" => 1])) {
			return [
				'status'  => 'success',
				'message' => 'Notification updated successfully.'
			];
		}

		return [
			'status'  => 'error',
			'message' => 'Something went wrong.'
		];
	}

	public function checkuserspo(Request $request){
              
		$test=$request->user();
		
		
		$email = $request->email;
		$mobile =$request->mobile;
		$application =SpoCharityFundApplication::where('user_id',auth()->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
		if(!empty($email)){
		   $user =User::where('email',$email)->first();
		
		   if($user){
			   $indv=Individual::where('user_id',$user->id)->first();
			   $occ =IndustryJob::where('id',$indv->occ)->first();
			   $industry=Industry::where('id',$occ->industry_id)->first();
			
			   if($request->input('mobile') == $indv->mobile){
				  $checkmember = SpoHouseholdMembers::where('individual_id',auth()->user()->profile->id)->where('sop_id',$application->id)->where('email',$email)->first();
				  
				
				  if($checkmember){
					$memberexist =true;
				   return [
					   'status'   => 'error',
					   'message' => ('email already exist'),
					   'memberexist'=> $memberexist,
				   ];
				  }else{
				   return [
					   'status'   => 'success',
					   'data'     => [
						   'name'     => $indv->name,
						   'nric' => $indv->nric,
						   'email'    => $user->email,
						   'mobile'   =>$indv->mobile,
						   'occ' =>$occ,
						   'industry'=>$industry,
						   'personal_income'=>$indv->personal_income,
						   
														   
					   ],
		   
				   ];
			   }
		   }
		   else{
			$modal = [
					
		 
				
				"body"    => ('Incorrect matching of Email & Mobile. Please check and rectify'),
				"buttons" => [
					[
						"title"  => __('ok'),
						"action" => '',
						"type"   => "",
					],
					
	 
				]
			];
			return Helpers::response('error',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
		}
		   }else{
			$checkmember =  SpoHouseholdMembers::where('individual_id',auth()->user()->profile->id)->where('sop_id',$application->id)->where('email',$email)->first();
				  
				
			if($checkmember){
			  $memberexist =true;
			 return [
				 'status'   => 'error',
				 'message' => ('email already exist'),
				 'memberexist'=> $memberexist,
			 ];
			}
		   }
		   
		}

		if(!empty($mobile)){

		   $indv=Individual::where('mobile',$mobile)->first();
		   if($indv){
			   $user=user::where('id',$indv->user_id)->first();
			   $occ =IndustryJob::where('id',$indv->occ)->first();
			   $industry=Industry::where('id',$occ->industry_id)->first();

			   if($request->input('email') == $user->email){
				  $checkmember = SpoHouseholdMembers::where('individual_id',$request->auth()->user()->profile->id)->where('sop_id',$application->id)->where('mobile',$mobile)->first();
				  if($checkmember){
				   return [
					   'status'   => 'error',
					   'message' => ('mobile already exist')
				   ];
				  }else{
				   return [
					   'status'   => 'success',
					   'data'     => [
						   'name'     => $indv->name,
						   'nric' => $indv->nric,
						   'email'    => $user->email,
						   'mobile'   =>$indv->mobile,
						   'occ' =>$occ,
						   'industry'=>$industry,
						   'personal_income'=>$indv->personal_income,
																					   
					   ],
					   
				   ];
			   }
		   }
		   else{
				
			$modal = [
					
		 
				
				"body"    => ('Incorrect matching of Email & Mobile. Please check and rectify'),
				"buttons" => [
					[
						"title"  => __('ok'),
						"action" => '',
						"type"   => "",
					],
					
	 
				]
			];
			return Helpers::response('error',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
		}
		   }else{
			$checkmember = SpoHouseholdMembers::where('individual_id',auth()->user()->profile->id)->where('sop_id',$application->id)->where('mobile',$mobile)->first();
				  
				
			if($checkmember){
			  $memberexist =true;
			 return [
				 'status'   => 'error',
				 'message' => ('mobile already exist'),
				 'memberexist'=> $memberexist,
			 ];
			}
		   }
		   
		}

		return [
			'status'   => 'success',
			
		];

   }
	public function getOwnerInfo(Request $request)
	{
		$request->validate([
			'email' => 'required',
			'passport' => 'required',
			'mobile' => 'required',
		]);

		try {
			$user       = 	$request->user();
			$email		=	$request->input('email');
			$passport	=	str_replace("-","",$request->input('passport'));
			$mobile     =    $request->input('mobile');
			$ownerInfo	=	User::where(['email' => $email, 'type' => 'individual'])->first();

			if (!$ownerInfo) {
			$non_dt_info = UserModel::where('email',$email)->where('type','individual')->first() ?? null;

			if($non_dt_info != null){
            $indv_info = Individual::where('user_id',$non_dt_info->id)->where('nric',$passport)->where('mobile',$mobile)->first() ?? null;

			if($indv_info != null){
			return [
				'status' => 'success',
				'data'   => [
					'name' => $indv_info->name ?? '',
					'dob'      => Carbon::parse($indv_info->dob)->format('m/d/Y'),
					'passport' => $indv_info->nric,
					'nationality' =>  $indv_info->nationality,
					'uuid'     => $non_dt_info->uuid,
					'email'    => $non_dt_info->email,
					'gender'    => $indv_info->gender,
					'mobile'    => $indv_info->mobile,
					'household_income'    => $indv_info->household_income,
					'personal_income'    => $indv_info->personal_income,
					'occupation_industry'    => $indv_info->occupation,
					'address'    => $indv_info->address,
					'country'	=> $indv_info->country,
					'passport_expiry_date' => Carbon::parse($indv_info->passport_expiry_date)->format('d/m/Y'),
					'user_non_dt' =>True,
				]
				];
				}else{
				return [
					'status'   => 'error',
					'message'  => '',
                    'message_modal' => __('mobile.nric_mobile_invalid')
				];
			}
			}else{
				return [
					'status'   => 'success',
					'data'     => [
						'name' => '',
						'uuid' => ''
					]
				];
			}
		}

			$profile	=	$ownerInfo->profile()->where('nric', $passport)->where('mobile',$mobile)->first();


			if (!$profile) {
				return [
					'status'   => 'error',
					'message'  => '',
                    'message_modal' => __('mobile.nric_mobile_invalid')
				];
			}

			return [
				'status' => 'success',
				'message' => 'OK',
				'data' => [
					'name'     => $ownerInfo->name,
					'dob'      => Carbon::parse($ownerInfo->profile->dob)->format('m/d/Y'),
					'passport' => $ownerInfo->profile->nric,
					'nationality' =>  $ownerInfo->profile->nationality,
					'uuid'     => $ownerInfo->uuid,
					'email'    => $ownerInfo->email,
					'gender'    => $ownerInfo->profile->gender,
					'mobile'    => $ownerInfo->profile->mobile,
					'household_income'    => $ownerInfo->profile->household_income,
					'personal_income'    => $ownerInfo->profile->personal_income,
					'occupation_industry'    => $ownerInfo->profile->occupation,
					'address'    => $ownerInfo->profile->address,
					'country'	=> $ownerInfo->profile->country,
					'passport_expiry_date' => Carbon::parse($ownerInfo->profile->passport_expiry_date)->format('d/m/Y')
				]
			];
		} catch (\Throwable $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
	
	public function rejectPayOther(Request $request)
	{
		try {
			$user			=	$request->user();
			$payer_id		=	$request->input('covered_payer_id');
			$payer_profile 	= 	User::find($payer_id)->profile ?? NULL;
			if (!empty($payer_profile)) {
				$payer_user = $payer_profile->user;
				if (!empty($payer_user)) {
					$user->profile->coverages_owner()->where("payer_id", $payer_id)
						->where('status', Enum::COVERAGE_STATUS_INCREASE_UNPAID)
						->where('corporate_user_status',null)
						->update([
							'status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE,
							'state' => Enum::COVERAGE_STATE_INACTIVE
						]);

					$user->profile->coverages_owner()->where("payer_id", $payer_id)
						->where('status', Enum::COVERAGE_STATUS_UNPAID)
						->where('corporate_user_status',null)
						->update([
							'status' => Enum::COVERAGE_STATUS_TERMINATE,
							'state' => Enum::COVERAGE_STATE_INACTIVE
						]);
					$payer_user->sendNotification('mobile.pay_other_rejected_title', 'mobile.pay_other_rejected_body', [
						'translate_data' => ['payor_name' => $payer_user->name,'owner_name' => $user->profile->name,'him_her' => ($user->profile->gender == 'female') ? 'her' : 'him' ?? 'him'],
						'command' => 'next_page',
						'data' => 'dashboard_page',
						'id' => 'pay_other_rejected',
						'buttons' => [],
						'auto_read' => TRUE,

					]);
				}
			}
			return ['status' => 'success', 'data' => ['info' => 'payment_rejected'], 'next_page' => NextPage::POLICIES];
		} catch (\Throwable $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}

	public function sendMail(Request $request)
	{
		try {
			$user	=	$request->user();
			/*$emailText = __('mobile.payer_owner_completed_purchase', [
				'payer_name' => $user->profile->name,
				'owner_name' => $user->profile->name,
				'him_her'	 => ($user->profile->gender == 'female') ? 'her' : 'him' ?? 'him',
			]);
			$user->sendNotification(__('notification.payor_owner_completed_purchase.title'),strip_tags($emailText),['command' => 'next_page','data' => 'policies_page']);
			$user->notify(new \App\Notifications\Email($emailText, ['subject' => __('notification.payor_owner_completed_purchase.title')]));
			*/
			$emailText = __('mobile.payor_owner_agreement', [
				'payer_name' => $user->profile->name,
				'owner_name' => $user->profile->name,
			]);
			$user->sendNotification(__('notification.payor_owner_agreement.title'),strip_tags($emailText),['command' => 'next_page','data' => 'policies_page']);
			$user->notify(new \App\Notifications\Email($emailText, ['subject' => __('notification.payor_owner_agreement.title')]));
			return ['status' => 'success', 'data' => ['info' => 'Email sent successfully!']];
		} catch (\Throwable $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
}
