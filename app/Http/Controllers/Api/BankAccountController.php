<?php     

namespace App\Http\Controllers\Api;


use App\Helpers;
use App\Helpers\NextPage;
use App\BankAccount;
use App\Coverage;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\SpoCharityFundApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;



class BankAccountController extends Controller
{

    /**
     * @api {get} api/initialBankCard initial bank card
     * @apiVersion 1.0.0
     * @apiName InitialBankCard
     * @apiGroup Bank
     *
     * @apiDescription It get bank account detail
     *
     * @apiUse AuthHeaderToken
     *
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[fund_source]
     * @apiSuccess (Response (200) ) {String} data[auto_debit]
     * @apiSuccess (Response (200) ) {Array} data[banks]
     * @apiSuccess (Response (200) ) {String} data[authenticate_url]
     * @apiSuccess (Response (200) ) {String} data[accounts]
     * @apiSuccess (Response (200) ) {Array} data[cards]
     * @apiSuccess (Response (200) ) {Boolean} data[is_charity]
     * @apiSuccess (Response (200) ) {Boolean} data[skipBankDetails]
     * @apiSuccess (Response (200) ) {String} data[terms_conditions]
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

	public function banksList()
	{
		$user = Auth::user()->profile;
		//        $auth_url = 'http://dev.deartime.com/payment/return?status_id=0&order_id=&token=0&cc_num=0000&cc_type=xx&msg=Failed_to_validate_payment_with_card._Please_check_with_your_bank._Thank_you.&hash=b0dc648a6cb6344f54a6aa6b6c02373f45b7842cb911405d6e52ec332f4b16f9';

		$auth_url = url('/payment/authenticate/' . Auth::user()->uuid . '/mobile');
		$is_charity = (boolean)$user->is_charity();
		$skipBankDetails = !$user->needBankAccount();
		$auto_debit = $user->bankCards()->latest()->first()->auto_debit ?? 0;
		return [
			'status' => 'success',
			'data'   => [
				'fund_source'      => $user->fund_source,
				'auto_debit'       => $auto_debit,
				'banks'            => config('static.banks'),
				'authenticate_url' => $auth_url,
				//'accounts' => $user->bankAccounts,
				'accounts'         => $user->bankAccounts()->latest()->get(),
				'cards'            => $user->bankCards()->latest()->get(),
				'is_charity'       => $is_charity,
				'skipBankDetails'  => $skipBankDetails,
				'terms_conditions' => route('page.index',['CreditCardTerms','mobile' => '1'])
			]
		];
	}

	public function getList()
	{
		return 'awd';
		//   $user = Auth::user()->profile;
		//   return ['status' => 'success', 'data' => ['accounts' => $user->bankAccounts, 'cards' => $user->bankCards] ];
	}

    /**
     * @api {post} api/bankAccounts add bank account
     * @apiVersion 1.0.0
     * @apiName AddBankAccount
     * @apiGroup Bank
     *
     * @apiDescription It create bank account
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} fund_source
     * @apiParam (Request) {String} page
     * @apiParam (Request) {String} only_fund_source
     * @apiParam (Request) {String} account
     * @apiParam (Request) {String} bank
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[next_page]
     * @apiSuccess (Response (200) ) {String} data[next_page_params]
     * @apiSuccess (Response (200) ) {Object} data[account]
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

	public function add(Request $request)
	{
		$user             = Auth::user();
		$type             = $user->type;
		$profile          = $user->profile;
		$next_page_params = [];
		if($request->filled('fund_source')){
			$profile->fund_source = $request->input('fund_source');
		}
		$profile->save();

		if($request->has('only_fund_source')){
			return ['status' => 'success','message' => __('web/messages.bank_account_added')];
		}

		$skipBankDetails = !$profile->needBankAccount();


		if(($type == 'individual') && $request->input('page') != 'card'){
			if(!$skipBankDetails){
				$request->validate(
					[
						'account' => ['required','max:20','min:8',Rule::Unique('bank_accounts','account_no')->ignore($profile->id,'owner_id')],
						'bank'    => 'required|string|max:100',
					]);
			}else{
				$request->validate(
					[
						'account' => ['nullable','max:20','min:8',Rule::Unique('bank_accounts','account_no')->ignore($profile->id,'owner_id')],
						'bank'    => 'nullable|string|max:100',
					]);
			}

			$oldAccountNo = $profile->bankAccounts()->latest()->first()->account_no ?? NULL;
			$oldBankName  = $profile->bankAccounts()->latest()->first()->bank_name ?? NULL;

			if(empty($oldAccountNo) && empty($oldBankName)){
				$bankAccount = $this->createBankAccount($request,$profile);

				// add action
				$actions = [
					'methods'        => '',
					'new_account_no' => $request->account,
					'new_bank_name'  => $request->bank,
					'added_at'       => Carbon::now()->format(config('static.datetime_format')),
				];

				$user->actions()->create(
					[
						'user_id'    => $user->id,
						'type'       => Enum::ACTION_TYPE_AMENDMENT,
						'event'      => Enum::ACTION_EVENT_ADD_BANK_ACCOUNT,
						'actions'    => $actions,
						'execute_on' => Carbon::now(),
						'status'     => Enum::ACTION_STATUS_EXECUTED
					]);

			}elseif(($oldAccountNo != $request->account) && ($oldBankName != $request->bank)){
				$profile->bankAccounts()->delete();
				$bankAccount = $this->createBankAccount($request,$profile);

				// add action
				$actions = [
					'methods'        => '',
					'new_account_no' => $request->account,
					'old_account_no' => $oldAccountNo,
					'new_bank_name'  => $request->bank,
					'old_bank_name'  => $oldBankName,
					'added_at'       => Carbon::now()->format(config('static.datetime_format')),
				];

				$user->actions()->create(
					[
						'user_id'    => $user->id,
						'type'       => Enum::ACTION_TYPE_AMENDMENT,
						'event'      => Enum::ACTION_EVENT_ADD_BANK_ACCOUNT,
						'actions'    => $actions,
						'execute_on' => Carbon::now(),
						'status'     => Enum::ACTION_STATUS_EXECUTED
					]);
			}
			if($profile->is_charity()){
				$spo_coverage =Coverage::where('payer_id',$profile->user_id)->where('sponsored',1)->where('status','unpaid')->get();
				$spo_da_coverage=Coverage::where('payer_id',$profile->user_id)->where('sponsored',1)->whereIn('product_name',[Enum::PRODUCT_NAME_ACCIDENT,Enum::PRODUCT_NAME_DEATH])->get();
				if($profile->verification && $spo_coverage->isNotEmpty() && $profile->thanksgiving()->count() != 0 && ($profile->underwritings()->count()!=0) && ($spo_da_coverage->isNotEmpty()? ($profile->beneficiaries()->count()!= 0):true)){
					$underwriting =$profile->underwritings()->first();
				if(($underwriting->death =='1')||($underwriting->disability =='1')||($underwriting->ci =='1')||($underwriting->medical =='1')){
				$spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['PENDING','SUBMITTED','QUEUE'])->first();
				if($spo_application->status != 'QUEUE'){
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
			
			}else{
				$next_page = $profile->verification ? 'order_review_page' : 'verification_page';
				}
				

		   }else{
		   $next_page = $profile->verification ? 'order_review_page' : 'verification_page';
		   }
			//$next_page = $profile->isVerified() ? 'order_review_page' : 'verification_page';
		}else{
			if($request->input('page') == 'card'){
				$bnk = $profile->bankCards->first();
				if(!empty($bnk)){
					$bnk->auto_debit = 1;
					$bnk->save();
				}
			}
			if($request->input('page') == 'card' && !$skipBankDetails){
				$next_page = 'payment_details_account_page';
			}else{
				if($type == 'individual'){
					if($profile->is_charity()){
						$spo_coverage =Coverage::where('payer_id',$profile->user_id)->where('sponsored',1)->where('status','unpaid')->get();
						$spo_da_coverage=Coverage::where('payer_id',$profile->user_id)->where('sponsored',1)->whereIn('product_name',[Enum::PRODUCT_NAME_ACCIDENT,Enum::PRODUCT_NAME_DEATH])->get();
				        if($profile->verification && $spo_coverage->isNotEmpty() && $profile->thanksgiving()->count() != 0 && ($profile->underwritings()->count()!=0) && ($spo_da_coverage->isNotEmpty()? ($profile->beneficiaries()->count()!= 0):true)){
							$underwriting =$profile->underwritings()->first();
						if(($underwriting->death =='1')||($underwriting->disability =='1')||($underwriting->ci =='1')||($underwriting->medical =='1')){
						$spo_application=SpoCharityFundApplication::where('user_id',$profile->user_id)->whereIn('status',['PENDING','SUBMITTED','QUEUE'])->first();
						if($spo_application->status != 'QUEUE'){
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
					}else{
						$next_page = $profile->verification ? 'order_review_page' : 'verification_page';
						}
						
		
				   }else{
					$next_page = $profile->verification ? 'order_review_page' : 'verification_page';
					}

				}else{
					$next_page = 'corporate_order_review_page';
					$next_page_params = ['pkg_id' => $request->input('pkg_id')];
				}
			}
		}

		if($request->input('page') != 'card' && $profile->isOld()){
			$next_page = 'verification_page';
		}

		return [
			'status'  => 'success',
			'message' => __('web/messages.bank_account_added'),
			'data'    => [
				'next_page'        => $next_page,
				'charity' =>$profile->is_charity(),
				'next_page_params' => $next_page_params,
				'account'          => $bankAccount ?? []
			]
		];
	}

	/**
	 * @param Request $request
	 * @param $user
	 * @return BankAccount
	 */
	private function createBankAccount(Request $request,$profile): BankAccount
	{
		$bankAccount = new BankAccount();
		$bankAccount->account_no = $request->account;
		$bankAccount->bank_name = $request->bank;
		$profile->bankAccounts()->save($bankAccount);
		return $bankAccount;
	}

    /**
     * @api {post} api/deleteAccount delete bank account
     * @apiVersion 1.0.0
     * @apiName DeleteBankAccount
     * @apiGroup Bank
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} uuid
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} Message
     * @apiSuccess (Response (200) ) {Object} Data user bank accounts

     */

	public function delete(Request $request)
	{
		$request->validate([
			'uuid' => 'required|string|exists:bank_accounts,uuid',
		]);
		$user = Auth::user()->profile;
		$user->bankAccounts()->whereUuid($request->uuid)->delete();
		return [
			'status'  => 'success',
			'message' => __('web/messages.bank_account_removed'),
			'data'    => $user->bankAccounts
		];
	}
}
