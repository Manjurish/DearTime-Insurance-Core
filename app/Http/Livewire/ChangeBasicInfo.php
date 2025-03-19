<?php     

namespace App\Http\Livewire;
use App\SpoCharityFundApplication;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Notifications\Email;
use App\Notifications\Sms;
use Illuminate\Support\Facades\Notification;
use App\Action;
use App\Address;
use App\City;
use App\Country;
use App\Coverage;
use App\User;
use App\CoverageThanksgiving;
use App\Jobs\ProcessPayment;
use App\CoverageOrder;
use App\Thanksgiving;
use App\SpoCharityFunds;
use App\Credit;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\Modal;
use App\Individual;
use App\Underwriting;
use App\Industry;
use App\IndustryJob;
use App\Transaction;
use App\Order;
use App\ParticularChange;
use App\PostalCode;
use App\Product;
use App\Refund;
use App\State;
use Carbon\Carbon;
use Livewire\Component;

class ChangeBasicInfo extends Component
{
	public $name;
	public $user;
	public $profile;
	public $country;
	public $country_id;
	public $states;
	public $address1;
	public $address2;
	public $address3;
	public $state_uuid;
	public $cities;
	public $city_uuid;
	public $labelNric;
	public $zipcodes;
	public $zipcode_uuid;
	public $dob;
	public $gender;
	public $industries;
	public $industryId;
	public $jobs;
	public $jobId;
	public $nric;
	public $passport_expiry_date;
	public $showNric;
	public $action;
	public $paymentTerm;
	public $firstActiveCoverage;
	public $reCalculateStatus = FALSE;

	protected $listeners = [
		'refresh' => '$refresh','getCities','getZipCode','executeAction','recalculateAction','cancelAction',
	];

	public function mount()
	{
		// name
		$this->name = $this->profile->name;
		// gender
		$this->gender = $this->profile->gender;
		// nationality
		$this->country    = Country::where('is_allowed','1')->get();
		$this->country_id = $this->profile->country_id;
		$this->nric       = $this->profile->nric;
		// address
		$this->address1   = $this->profile->address->address1 ?? NULL;
		$this->address2   = $this->profile->address->address2 ?? NULL;
		$this->address3   = $this->profile->address->address3 ?? NULL;
		$this->states     = State::get();
		$this->state_uuid = $this->profile->address->state ?? NULL;
		$this->getCities();
		$this->city_uuid = $this->profile->address->city ?? NULL;
		$this->getZipCode();
		$this->zipcode_uuid = $this->profile->address->postcode ?? NULL;
		// dob
		//$this->dob = Carbon::parse($this->profile->dob)->format('d/m/y');

		// occupation
		$this->industries = Industry::all();
		$this->industryId = IndustryJob::find($this->profile->occ)->industry_id;
		$this->jobs       = IndustryJob::where('industry_id',$this->industryId)->get();
		$this->jobId      = $this->profile->occ;
	}

	public function getCities()
	{
		if(empty($this->state_uuid)){
			$this->addError('state_uuid',__('web/messages.required_field'));
			$this->reset('city_uuid','zipcode_uuid');
		}else{
			$this->resetErrorBag();
			$stateId      = State::where('uuid',$this->state_uuid)->first()->id;
			$this->cities = City::where('state_id',$stateId)->get();
		}
	}

	public function getZipCode()
	{
		if(empty($this->city_uuid)){
			$this->addError('city_uuid',__('web/messages.required_field'));
			$this->reset('zipcode_uuid');
		}else{
			$this->resetErrorBag();
			$cityId         = City::where('uuid',$this->city_uuid)->first()->id;
			$this->zipcodes = PostalCode::where('city_id',$cityId)->get();
		}
	}

	public function render()
	{
		if($this->country_id == 135){
			$this->showNric  = TRUE;
			$this->labelNric = __('web/profile.mykad');
			$this->reset('passport_expiry_date');
			$this->dispatchBrowserEvent('inputmask:nric');
		}else{
			$this->showNric  = FALSE;
			$this->labelNric = __('web/profile.passport');
			$this->dispatchBrowserEvent('inputmask:passport');
		}

		$this->dispatchBrowserEvent('dob');
		$this->dispatchBrowserEvent('ped');

		return view('livewire.change-basic-info');
	}

	public function addNameAction()
	{
		$messages = [
			'required' => __('web/messages.required_field'),
			'not_in'   => __('web/messages.should_be_different'),
		];

		$this->validate([
							'name' => "required|regex:/^[\pL\s\-\@\'\/]+$/u|max:80|not_in:" . $this->profile->name,
						],$messages);

		$actions = [];

		array_push($actions,[
			'methods'  => [Enum::ACTION_METHOD_CHANGE_NAME],
			'old_name' => $this->profile->name,
			'new_name' => strtoupper($this->name),
		]);

		auth('internal_users')->user()->actions()->create([
															  'user_id' => $this->profile->user->id,
															  'type'       => Enum::ACTION_TYPE_AMENDMENT,
															  'event'   => Enum::ACTION_EVENT_CHANGE_NAME,
															  'actions' => $actions,
															  'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
														  ]);

		Modal::success($this,__('web/messages.succecfully_added'));
		$this->emit('tableRefresh');
		$this->dispatchBrowserEvent('dob');
	}

	public function changeName($action)
	{
		$actionParams = collect($action->actions)->first();
		$groupId      = $this->getGroupId();
		$columnName   = 'name';
		$oldValue     = $actionParams['old_name'];
		$newValue     = $actionParams['new_name'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);
		$this->updateProfile($newValue,$columnName);
	}

	private function getGroupId(): int
	{
		$group = ParticularChange::where('individual_id',$this->profile->id)->latest()->first();
		return empty($group) ? 1 : $group->group_id + 1;
	}

	private function saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias = NULL): void
	{
		if($oldValue == $newValue){
			return;
		}

		$particularChange                = new ParticularChange;
		$particularChange->individual_id = $this->profile->id;
		$particularChange->group_id      = $groupId;
		$particularChange->action_id     = $action->id;
		$particularChange->created_by    = auth('internal_users')->id();
		$particularChange->column_name   = empty($columnAlias) ? $columnName : $columnAlias;
		$particularChange->old_value     = $oldValue;
		$particularChange->new_value     = $newValue;
		$particularChange->save();
	}

	private function updateProfile($newValue,$columnName): void
	{
		$this->profile->$columnName = $newValue;
		$this->profile->save();
	}

	public function addNationalityAction()
	{
		$this->nric = str_replace("-","",$this->nric);

		$messages = [
			'required'        => __('web/messages.required_field'),
			'required_unless' => __('web/messages.required_unless_passport_expiry_date'),
			'not_in'          => __('web/messages.should_be_different'),
		];

	

		if($this->country_id == 135){
			
			$nric = $this->nric;
			
			$individual_check =Individual::where('nric',$nric)->latest()->first();
		
			if($individual_check){
			$pending_nric =$individual_check->user->isPendingPromoted();
            if($pending_nric){
			
			$this->validate([
				'country_id' => 'required|numeric',
				'nric'       => 'required|not_in:' . $this->profile->nric . ''
			],$messages);

		   }else{
			
			$this->validate([
				'country_id' => 'required|numeric',
				'nric'       => 'required|not_in:' . $this->profile->nric . '|unique:individuals,nric',
			],$messages);
		   }
		}else{
			
			$this->validate([
				'country_id' => 'required|numeric',
				'nric'       => 'required|not_in:' . $this->profile->nric . '|unique:individuals,nric',
			],$messages);
		   }
		   
		}else{
			$this->validate([
								'country_id'           => 'required|numeric',
								'nric'                 => 'required|unique:individuals,nric',
								'passport_expiry_date' => 'required_unless:country_id,135',
							],$messages);
		}

		$actions = [];

		array_push($actions,[
			'methods'  => [Enum::ACTION_METHOD_CHANGE_NATIONALITY],
			'old_nric' => $this->profile->nric,
			'new_nric' => $this->nric,
		]);

		if($this->profile->country_id != $this->country_id){
			$actions[0]['old_country_id']   = $this->profile->country_id;
			$actions[0]['new_country_id']   = $this->country_id;
			$actions[0]['old_country_name'] = Country::find($this->profile->country_id)->nationality;
			$actions[0]['new_country_name'] = Country::find($this->country_id)->nationality;
		}

		if(!$this->showNric){
			$actions[0]['old_passport_expiry_date']        = Carbon::parse($this->profile->passport_expiry_date)->format('d/m/y');
			$actions[0]['new_passport_expiry_date']        = Carbon::parse($this->passport_expiry_date)->format('d/m/y');
			$actions[0]['new_passport_expiry_date_format'] = $this->passport_expiry_date;
		}

		auth('internal_users')->user()->actions()->create([
															  'user_id' => $this->profile->user->id,
															  'type'       => Enum::ACTION_TYPE_AMENDMENT,
															  'event'   => Enum::ACTION_EVENT_CHANGE_NATIONALITY,
															  'actions' => $actions,
															  'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
														  ]);

		Modal::success($this,__('web/messages.succecfully_added'));
		$this->emit('tableRefresh');
	}

	public function changeNationality($action)
	{
		$actionParams = collect($action->actions)->first();

		$groupId = $this->getGroupId();

		$columnName = 'nric';
		$oldValue   = $actionParams['old_nric'];
		$newValue   = $actionParams['new_nric'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);
		$this->updateProfile($newValue,$columnName);

		if(isset($actionParams['new_country_name'])){
			$columnName  = 'country_id';
			$columnAlias = 'country';
			$oldValue    = $actionParams['old_country_name'];
			$newValue    = $actionParams['new_country_name'];
			$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias);
			$this->updateProfile($actionParams['new_country_id'],$columnName);
		}

		if(isset($actionParams['new_passport_expiry_date'])){
			$columnName  = 'passport_expiry_date';
			$columnAlias = 'passport expiry date';
			$oldValue    = $actionParams['old_passport_expiry_date'];
			$newValue    = $actionParams['new_passport_expiry_date'];
			$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias);
			$this->updateProfile($actionParams['new_passport_expiry_date_format'],$columnName);
		}
	}

	public function addAddressAction()
	{
		$messages = [
			'required' => __('web/messages.required_field'),
		];

		$this->validate([
							'address1'     => 'required|regex:/^[\pL\s\-\@\'\/]+$/u',
							'address2'     => 'nullable|regex:/^[\pL\s\-\@\'\/]+$/u',
							'address3'     => 'nullable|regex:/^[\pL\s\-\@\'\/]+$/u',
							'state_uuid'   => 'required',
							'city_uuid'    => 'required',
							'zipcode_uuid' => 'required',
						],$messages);

		$actions = [];

		array_push($actions,[
			'methods'           => [Enum::ACTION_METHOD_CHANGE_ADDRESS],
			'old_state_uuid'    => $this->profile->address->state,
			'new_state_uuid'    => $this->state_uuid,
			'old_state_name'    => State::where('uuid',$this->profile->address->state)->first()->name,
			'new_state_name'    => State::where('uuid',$this->state_uuid)->first()->name,
			'old_city_uuid'     => $this->profile->address->city,
			'new_city_uuid'     => $this->city_uuid,
			'old_city_name'     => City::where('uuid',$this->profile->address->city)->first()->name,
			'new_city_name'     => City::where('uuid',$this->city_uuid)->first()->name,
			'old_postcode_uuid' => $this->profile->address->postcode,
			'new_postcode_uuid' => $this->zipcode_uuid,
			'old_postcode_name' => PostalCode::where('uuid',$this->profile->address->postcode)->first()->name,
			'new_postcode_name' => PostalCode::where('uuid',$this->zipcode_uuid)->first()->name,
			'old_address1'      => $this->profile->address->address1,
			'new_address1'      => $this->address1,
			'old_address2'      => $this->profile->address->address2,
			'new_address2'      => $this->address2,
			'old_address3'      => $this->profile->address->address3,
			'new_address3'      => $this->address3,
		]);

		auth('internal_users')
			->user()
			->actions()
			->create([
						 'user_id' => $this->profile->user->id,
						 'type'       => Enum::ACTION_TYPE_AMENDMENT,
						 'event'   => Enum::ACTION_EVENT_CHANGE_ADDRESS,
						 'actions' => $actions,
						 'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
					 ]);

		Modal::success($this,__('web/messages.succecfully_added'));
		$this->emit('tableRefresh');
	}

	public function changeAddress($action)
	{
		$actionParams = collect($action->actions)->first();

		$groupId = $this->getGroupId();

		$columnName = 'state';
		$oldValue   = $actionParams['old_state_name'];
		$newValue   = $actionParams['new_state_name'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName = 'city';
		$oldValue   = $actionParams['old_city_name'];
		$newValue   = $actionParams['new_city_name'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName = 'postcode';
		$oldValue   = $actionParams['old_postcode_name'];
		$newValue   = $actionParams['new_postcode_name'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$address = Address::create([
									   'type'     => Enum::ADDRESS_TYPE_RESIDENTIAL,
									   'address1' => $actionParams['new_address1'],
									   'address2' => $actionParams['new_address2'],
									   'address3' => $actionParams['new_address3'],
									   'state'    => $actionParams['new_state_uuid'],
									   'city'     => $actionParams['new_city_uuid'],
									   'postcode' => $actionParams['new_postcode_uuid'],
								   ]);

		$columnName = 'address1';
		$oldValue   = $actionParams['old_address1'];
		$newValue   = $actionParams['new_address1'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName = 'address2';
		$oldValue   = $actionParams['old_address2'];
		$newValue   = $actionParams['new_address2'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName = 'address3';
		$oldValue   = $actionParams['old_address3'];
		$newValue   = $actionParams['new_address3'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$this->profile->address_id = $address->id;
		$this->profile->save();
	}

	public function getJobs()
	{
		$this->jobs = IndustryJob::where('industry_id',$this->industryId)->get();
	}

	public function executeAction($uuid)
	{
		$action                   = Action::whereUuid($uuid)->first();
		$fullRefundActions        = [];
		$refundActions            = [];
		$partialRefundActions     = [];
		$reduceCoverageActions    = [];
		$additionalPremiumActions = [];
		$otherActions             = [];
		$terminateActions         = [];

		$all_methods =[];
		$execute_action=false;
  
		
		Modal::success($this,__('executed'));

		foreach($action->actions as $actionItem){
			foreach ($actionItem['methods'] as $method) {
				$all_methods[] =$method;
			}
		
		}

		foreach ($action->actions as $actionItem) {
			if(!empty($actionItem['methods']) && count($actionItem['methods']) > 1){
				foreach ($actionItem['methods'] as $method) {
					if($method == Enum::ACTION_METHOD_CHANGE_OCCUPATION){
						if($actionItem['new_annually']>$actionItem['old_annually']){
							$spo_application=SpoCharityFundApplication::where('user_id',$action->user->profile->user_id)->where('status','QUEUE')->first();
							if($spo_application){
							$spo_application->submitted_at =Carbon::now();
							$spo_application->save();
							}

						}


						
					}
				}
			}

			foreach ($actionItem['methods'] as $method) {
				if($method == Enum::ACTION_METHOD_CHANGE_OCCUPATION){
				if($action->user->profile->is_charity()){
				$jobcheck =IndustryJob::where('id',$actionItem['jobId'] )->first();
				if($jobcheck->death==-1 || $jobcheck->Accident ==-1 || $jobcheck->TPD ==-1 || $jobcheck->Medical ==-1){
						$applicantuser =User::where('id',$action->user->profile->user_id)->first();
						$applicantuser->sendNotification('Attention', 'mobile.occupation_reject', [
								'buttons' => [
									['title' => 'ok'],
									],
					
							]);
						}
					}
				}
				//dd($jobcheck);
			}
		}

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
					}elseif($method == Enum::ACTION_METHOD_REFUND){
							array_push($refundActions,$actionItem);
					}elseif($method == Enum::ACTION_METHOD_TERMINATE){
						array_push($terminateActions,$actionItem);
				     }else{
						array_push($otherActions,$actionItem);
					}
				}
			}
		}

		$totalRefund            = 0;
		$totalAdditionalPremium = 0;
		$true_amount =0;


		if(in_array(Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,$all_methods)){
			
			if(!empty($additionalPremiumActions)){

				$method                 = Enum::ACTION_METHOD_ADDITIONAL_PREMIUM;
				$totalAdditionalPremium += $this->$method($action,$additionalPremiumActions)[0];
				$true_amount +=$this->$method($action,$additionalPremiumActions)[1];
	    
				//dd($additionalPremiumActions);
				$coverageIds = [];
				foreach ($action->actions as $actions) {
					if(in_array(Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,$actions['methods'])){
						array_push($coverageIds,$actions['coverage_id']);
					}
				}
	
				$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();
                
				//dd($coveragesOwner);
				
				$coverageStatusGrace = Coverage::whereIn('id',$coverageIds)->whereIn('status',[Enum::COVERAGE_STATUS_GRACE_UNPAID,Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID])->get();
	
				if(count($coverageStatusGrace) == 0){ // status 'active' & 'active-increase'
					// renew
					// $method = Enum::ACTION_METHOD_RENEW_COVERAGE;
					// $this->$method($action,$coveragesOwner);
				 $execute_action = $this->autodebit_addPremium($action,$coveragesOwner,$totalAdditionalPremium,$true_amount);
					
					
	
					
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
			
		}else{
			$execute_action =true;
		}

		if($execute_action){
			$totalRefund            = 0;
			$totalAdditionalPremium = 0;

			
	
			if(!empty($fullRefundActions)){
				$coverageIds = [];
				$methods =[];
				foreach ($action->actions as $actions) {
					if(in_array(Enum::ACTION_METHOD_FULL_REFUND,$actions['methods'])){
						array_push($coverageIds,$actions['coverage_id']);
					}
	
					foreach($actions['methods'] as $method){
						$methods[] =$method;
					}
				}
	
				// dd($actions);
				$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();
	
				$method      = Enum::ACTION_METHOD_FULL_REFUND;
				$totalRefund += $this->$method($action,$fullRefundActions);
				// dd($actions);
				$Coverage_orders =CoverageOrder::whereIn('coverage_id',$coverageIds)->get()->pluck('order_id')->toArray();
				$Coverage_credits=Credit::whereIn('order_id',$Coverage_orders)->get();
				foreach($Coverage_credits as $cov_credit){
					$thanksgiving = Thanksgiving::where('id',$cov_credit->type_item_id)->where('type','charity')->first();
					if($thanksgiving){
						$new_credit = $cov_credit->replicate();
						$new_credit->amount = -round($totalRefund*($thanksgiving->percentage/1000),2);
						$new_credit->save();
						$charity_fund =SpoCharityFunds::where('order_id',$cov_credit->order_id)->first();
						$fund =$charity_fund->replicate();
						$fund->amount =$totalRefund;
						$fund->charity_fund =-round($totalRefund*($thanksgiving->percentage/1000),2);
						$fund->save();
					}
				}
				 $method         = Enum::ACTION_METHOD_TERMINATE;
				 $this->$method($coveragesOwner);
				 if(in_array(Enum::ACTION_METHOD_CHANGE_OCCUPATION,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to Occupation change']);
	
				 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_DOB,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to dob change']);
	
				 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_GENDER,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to gender change']);
				 }
			}
	

			//dd($totalRefund);
			if(!empty($refundActions)){
				$coverageIds = [];
				$all_methods =[];
				$methods =[];
				foreach ($action->actions as $actions) {
					if(in_array(Enum::ACTION_METHOD_REFUND,$actions['methods'])){
						array_push($coverageIds,$actions['coverage_id']);
					}
	
					foreach($actions['methods'] as $method){
						$methods[] =$method;
					}
				}
	
				// dd($actions);
				$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();
	
				$method      = Enum::ACTION_METHOD_REFUND;
				$totalRefund += $this->$method($action,$refundActions);
				// dd($actions);
				 $method         = Enum::ACTION_METHOD_TERMINATE;
				 $this->$method($coveragesOwner);
				 if(in_array(Enum::ACTION_METHOD_CHANGE_OCCUPATION,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to Occupation change']);
	
				 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_DOB,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to dob change']);
	
				 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_GENDER,$methods)){
					Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to gender change']);
				 }
			}
	
			if(!empty($partialRefundActions)){
				$coverageIds = [];
				$methods =[];
				foreach ($action->actions as $actions) {
					if(in_array(Enum::ACTION_METHOD_PARTIAL_REFUND,$actions['methods'])){
						array_push($coverageIds,$actions['coverage_id']);
					}
					foreach($actions['methods'] as $method){
                        $methods[] =$method;
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
					if(in_array(Enum::ACTION_METHOD_CHANGE_OCCUPATION,$methods)){
						Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to Occupation change']);
					 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_DOB,$methods)){
						Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to dob change']);
					 }elseif(in_array(Enum::ACTION_METHOD_CHANGE_GENDER,$methods)){
						Coverage::whereIn('id',$coverageIds)->update(['reason_terminate' => 'terminate due to gender change']);
					 }
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
	
			
	
			if(!empty($totalRefund)){
				$bankAccount = $action->user->profile->bankAccounts()->first()->account_no;
				$this->createRefund($action,$bankAccount,$totalRefund);
				$coverageIds = [];
				$refund_methods=[Enum::ACTION_METHOD_REFUND,Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_FULL_REFUND];
				
				foreach ($action->actions as $actions) {
				   
					foreach($actions['methods'] as $method){
						if(in_array($method,$refund_methods)){
							array_push($coverageIds,$actions['coverage_id']);
						}
					}
	
				}
	
	
	
	
				$refund_id = Refund::where('action_id',$action->id)->first()->id;
				Coverage::whereIn('id',$coverageIds)->update(['refund_id' => $refund_id]);
			}
	
			if(!empty($totalAdditionalPremium)){
				Credit::create([
								   'from_id'      => $action->user_id,
								   'amount'       => $totalAdditionalPremium,
								   'type'         => Enum::CREDIT_TYPE_ACTION,
								   'type_item_id' => $action->id,
							   ]);
			}
	
			if(!empty($terminateActions)){
				$coverageIds = [];
				foreach($terminateActions as $terminateAction){
					array_push($coverageIds,$terminateAction['coverage_id']);
				}
				$coveragesOwner = Coverage::whereIn('id',$coverageIds)->get();
	
				// dd($coveragesOwner);
				
				$method         = Enum::ACTION_METHOD_TERMINATE;
				$this->$method($coveragesOwner);
			}
			//dd($otherActions);
	
			if(!empty($otherActions)){
				foreach ($otherActions as $otherAction) {
					foreach ($otherAction['methods'] as $method) {
						if(
							$method != Enum::ACTION_METHOD_FULL_REFUND &&
							$method != Enum::ACTION_METHOD_REFUND &&
							$method != Enum::ACTION_METHOD_PARTIAL_REFUND &&
							$method != Enum::ACTION_METHOD_REDUCE_COVERAGE &&
							$method != Enum::ACTION_METHOD_ADDITIONAL_PREMIUM
							//$method != Enum::ACTION_METHOD_TERMINATE &&
							//$method != Enum::ACTION_METHOD_RENEW_COVERAGE
						){
							$this->$method($action);
							//dd($this->$method);
							break;
						}
					}
					break;
				}
			}
	
		}

		// excute method = event
		//$methodName = $action->event;
		//$this->$methodName($action);

		// update action
		$action->update([
							'status'     => Enum::ACTION_STATUS_EXECUTED,
							'execute_on' => Carbon::now()
						]);


		if(in_array(Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,$all_methods)){
			if(!empty($additionalPremiumActions)){
					if(!$execute_action){
							Modal::success($this,__('payment failed'));
							}
						}
					}

		$this->emit('tableRefresh');
	}

	public function cancelAction($uuid)

	{
		$action = Action::whereUuid($uuid)->first();

		$action->update([
			'status'     => Enum::ACTION_STATUS_REJECTED,
			'execute_on' => Carbon::now()
		]);

		$this->emit('tableRefresh');
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

	public function recalculateAction($uuid)
	{
		$this->action = Action::whereUuid($uuid)->first();

		if($this->action->event == Enum::ACTION_EVENT_CHANGE_DOB){
			$this->dob               = collect($this->action->actions)->first()['new_dob'];
			$this->reCalculateStatus = TRUE;
			$this->addDobAction();
		}elseif($this->action->event == Enum::ACTION_EVENT_CHANGE_GENDER){
			$this->gender            = collect($this->action->actions)->first()['new_gender'];
			$this->reCalculateStatus = TRUE;
			$this->addGenderAction();
		}elseif($this->action->event == Enum::ACTION_EVENT_CHANGE_OCCUPATION){
			$this->industryId        = collect($this->action->actions)->first()['industryId'];
			$this->jobId             = collect($this->action->actions)->first()['jobId'];
			$this->reCalculateStatus = TRUE;
			$this->addOccupationAction();
		}
	}

	public function addDobAction()
	{
		$messages = [
			'required' => __('web/messages.required_field'),
			'not_in'   => __('web/messages.before_after_dob'),
		];

		//$this->dob = Carbon::parse($this->profile->dob)->format('d/m/y');

		$this->validate([
							'dob' => 'required|date|not_in:' . Carbon::parse($this->profile->dob)->format('d/m/y'),
						],$messages);

		// create action
		$this->createActionForAge();

		$this->dispatchBrowserEvent('dob');
		//$this->dob = Carbon::parse($this->profile->dob)->format('Y/m/d');

		$this->emit('tableRefresh');
	}

	private function createActionForAge()
	{
		$oldAge = Carbon::parse($this->profile->dob)->age;
		//dd($this->profile->dob,$oldAge,Carbon::parse($this->profile->dob)->toDateString());
		$newAge = Carbon::parse($this->dob)->age;
		//$oldDob      = $this->profile->dob->toDateString();
		$oldDob      = Carbon::parse($this->profile->dob)->toDateString();
		$newdob      = $this->dob;
		$actions     = [];
		$coverageIds = [];

		/*$coverage = Coverage::query()
			->where('covered_id', $this->profile->id)
			->exists();*/

			$activeCoverages_medical =  Coverage::query()
			->where('covered_id',$this->profile->id)
			->where('payer_id','=',$this->profile->user_id)
			->orderBy('product_id','desc')
			->where('state',Enum::COVERAGE_STATE_ACTIVE)
			->where('product_name', 'Medical')
			->get();
	
			
			$med_monthly=0;
			$med_annually=0;
			$med_without_loading=0;
			// $med_payment_annually_new=0;
			// $med_payment_monthly_new=0;
			$med_coverage_id = []; 
	
			foreach ($activeCoverages_medical as $activeCoverage_medical){
				$med_monthly+=$activeCoverage_medical->payment_monthly;
				$med_annually+=$activeCoverage_medical->payment_annually;
				$med_without_loading+=$activeCoverage_medical->payment_without_loading;
				// $med_payment_annually_new+=$activeCoverage_medical->payment_annually_new;
				// $med_payment_monthly_new+=$activeCoverage_medical->payment_monthly_new;
				array_push($med_coverage_id,$activeCoverage_medical->id);
			}
	
			$latset_activeCoverage_medical =  Coverage::query()
			->where('covered_id',$this->profile->id)
			->where('payer_id','=',$this->profile->user_id)
			//->where('product_id',$product->id)
			//->whereNotNull('last_payment_on')
			->orderBy('product_id','desc')
			->where('state',Enum::COVERAGE_STATE_ACTIVE)
			->where('product_name', 'Medical')
			->latest()->first();
	
			$latset_activeCoverage_medical_id = $latset_activeCoverage_medical->id ?? null;
		
		// check age limit
		$products = Product::all();
		foreach ($products as $product) {
			$coverage = Coverage::query()
								->where('covered_id',$this->profile->id)
								->where('payer_id','=',$this->profile->user_id)
								->where('product_id',$product->id)
								->whereNotNull('last_payment_on')
								->orderBy('last_payment_on')
								->first();

			if(empty($coverage)){
				continue;
			}

			$first = $coverage->first_payment_on;

			$tr = Carbon::parse($first);
			$ts = Carbon::now();
			$tu = $tr->diffInDays($ts);

			$ageFirstPayment = date_diff(date_create($coverage->owner->dob), date_create($coverage->first_payment_on))->format('%y');
			$diffAge         = $newAge - $oldAge;
			$maxAge          = json_decode($coverage->product->options,TRUE)['max_age'];

			$old_occ  =IndustryJob::where('id',$this->profile->occ)->first();

			if(($ageFirstPayment + $diffAge) > $maxAge){
				if($tu > 15){
				$coverages = Coverage::query()
									 ->where('covered_id',$this->profile->id)
									 ->where('payer_id','=',$this->profile->user_id)
									 ->where('product_id',$product->id)
					//->whereNotNull('last_payment_on')
									 ->orderBy('product_id','desc')
									 ->where('state',Enum::COVERAGE_STATE_ACTIVE)
									 ->get();

				foreach ($coverages as $coverage) {

						if($coverage->product->name == 'Death'){
							$occ_loading = $old_occ->death;
						}elseif($coverage->product->name == 'Accident'){
							$occ_loading = $old_occ->Accident;
						}elseif($coverage->product->name == 'Medical'){
							$occ_loading = $old_occ->Medical;
						}elseif($coverage->product->name == 'Disability'){
							$occ_loading = $old_occ->TPD;
						}else{
							$occ_loading = NULL;
						}

					$underwriting =Underwriting::where('id',$coverage->uw_id)->first();

					$deductible     = $coverage->product_name == 'Medical' ? $coverage->deductible : NULL;
					$newPrice       = $coverage->product->getPrice($this->profile,$coverage->coverage,$occ_loading,$oldAge,$deductible,$underwriting,null)[0];
					
					$newAnnually    = Helpers::round_up($newPrice,2);
					$oldAnnually    = $coverage->payment_annually;
					$oldMonthly     = $coverage->payment_monthly;
					$newMonthly     = $coverage->product->covertAnnuallyToMonthly($newPrice);
					$oldCoverage    = $coverage->coverage;
					$newCoverage    = $coverage->coverage;
					$currentState   = $coverage->state;
					$currentStatus  = $coverage->status;
					$without_loading = round($coverage->product->getPrice($this->profile,$coverage->coverage,$occ_loading,$oldAge,$deductible,$underwriting,null)[3],2);
					if($coverage->payment_term =='monthly'){
						$without_loading =(Helpers::round_up($without_loading * 0.085, 2));	
					}
					$changedAt      = Carbon::now()->toDateTimeString();
					$firstPaymentOn = Carbon::parse($coverage->first_payment_on)->toDateTimeString();
					$nextPaymentOn  = Carbon::parse($coverage->next_payment_on)->toDateTimeString();
					$lastPaymentOn  = Carbon::parse($coverage->last_payment_on)->toDateTimeString();
					array_push($actions,[
						'methods'          => [Enum::ACTION_METHOD_REFUND,Enum::ACTION_METHOD_CHANGE_DOB],
						'coverage_id'      => $coverage->id,
						'product_name'     => $coverage->product->name,
						'payment_term'     => $coverage->payment_term,
						'payment_monthly'  => $coverage->payment_monthly,
						'payment_annually' => $coverage->payment_annually,
						'old_coverage'     => $oldCoverage,
						'new_coverage'     => $newCoverage,
						'old_annually'     => $oldAnnually,
						'new_annually'     => $newAnnually,
						'old_monthly'      => $oldMonthly,
						'new_monthly'      => $newMonthly,
						'without_loading'  => $without_loading,
						'current_status'   => $currentStatus,
						'current_state'    => $currentState,
						'old_age'          => $oldAge,
						'new_age'          => $newAge,
						'old_dob'          => $oldDob,
						'new_dob'          => $newdob,
						'changed_at'       => $changedAt,
						'first_payment_on' => $firstPaymentOn,
						'next_payment_on'  => $nextPaymentOn,
						'last_payment_on'  => $lastPaymentOn,
						'pro_rate'         => TRUE,
					]);
					array_push($coverageIds,$coverage->id);
				}
			}
				else{
						$coverages = Coverage::query()
											 ->where('covered_id',$this->profile->id)
											 ->where('payer_id','=',$this->profile->user_id)
											 ->where('product_id',$product->id)
							//->whereNotNull('last_payment_on')
											 ->orderBy('product_id','desc')
											 ->where('state',Enum::COVERAGE_STATE_ACTIVE)
											 ->get();
		
						foreach ($coverages as $coverage) {
						
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_FULL_REFUND,Enum::ACTION_METHOD_CHANGE_DOB],
								'product_name'     => $coverage->product_name,
								'coverage_id'      => $coverage->id,
								'payment_term'     => $coverage->payment_term,
								'payment_monthly'  => $coverage->payment_monthly,
								'payment_annually' => $coverage->payment_annually,
								'without_loading'  => $coverage->payment_without_loading,
								'new_annually'     => $coverage->payment_annually,
								'new_monthly'      => $coverage->payment_monthly,
								'old_age'          => $oldAge,
								'new_age'          => $newAge,
								'old_dob'          => $oldDob,
								'new_dob'          => $newdob,
							]);
							array_push($coverageIds,$coverage->id);
		}
	}
}
			else{
				$activeCoverages = Coverage::query()
										   ->where('covered_id',$this->profile->id)
										   ->where('payer_id','=',$this->profile->user_id)
										   ->where('product_id',$product->id)
					//->whereNotNull('last_payment_on')
										   ->orderBy('product_id','desc')
										   ->where('state',Enum::COVERAGE_STATE_ACTIVE)
										   ->get();

						   if($product->name == 'Medical'){
											
								foreach($activeCoverages as $activeCoverage){
										
										if($activeCoverage->id == $latset_activeCoverage_medical->id)
										{
											$activeCoverage->payment_monthly=$med_monthly;				
											$activeCoverage->payment_annually=$med_annually;
											$activeCoverage->payment_without_loading=$med_without_loading;
											// $activeCoverage->payment_annually_new=$med_payment_annually_new;
											// $activeCoverage->payment_monthly_new=$med_payment_monthly_new;	
												
										}
										// $activeCoverages->push($activeCoverage_medical);
									}
									   
												   }

												   
				foreach ($activeCoverages as $activeCoverage) {

					if($activeCoverage->product->name == 'Death'){
						$occ_loading = $old_occ->death;
					}elseif($activeCoverage->product->name == 'Accident'){
						$occ_loading = $old_occ->Accident;
					}elseif($activeCoverage->product->name == 'Medical'){
						$occ_loading = $old_occ->Medical;
					}elseif($activeCoverage->product->name == 'Disability'){
						$occ_loading = $old_occ->TPD;
					}else{
						$occ_loading = NULL;
					}
					$underwriting =Underwriting::where('id',$activeCoverage->uw_id)->first();

					$deductible     = $activeCoverage->product_name == 'Medical' ? $activeCoverage->deductible : NULL;
					$newPrice       = $activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$occ_loading,$newAge,$deductible,$underwriting,null)[0];
					$newAnnually    = Helpers::round_up($newPrice,2);
					$oldAnnually    = $activeCoverage->payment_annually;
					$oldMonthly     = $activeCoverage->payment_monthly;
					$newMonthly     = $activeCoverage->product->covertAnnuallyToMonthly($newPrice);
					$oldCoverage    = $activeCoverage->coverage;
					$newCoverage    = $activeCoverage->coverage;
					$currentState   = $activeCoverage->state;
					$currentStatus  = $activeCoverage->status;
					$without_loading =round($activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$occ_loading,$newAge,$deductible,$underwriting,null)[3],2);
					if($activeCoverage->payment_term =='monthly'){
						$without_loading =(Helpers::round_up($without_loading * 0.085, 2));	
					}
					$changedAt      = Carbon::now()->toDateTimeString();
					$firstPaymentOn = Carbon::parse($activeCoverage->first_payment_on)->toDateTimeString();
					$nextPaymentOn  = Carbon::parse($activeCoverage->next_payment_on)->toDateTimeString();
					$lastPaymentOn  = Carbon::parse($activeCoverage->last_payment_on)->toDateTimeString();
					$renewal_date = Carbon::parse($activeCoverage->renewal_date)->toDateTimeString();


					if($oldAnnually < $newAnnually){
						if($activeCoverage->product->isMedical()){
							if($latset_activeCoverage_medical_id == $activeCoverage->id){
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,Enum::ACTION_METHOD_CHANGE_DOB],
								'coverage_id'      => $activeCoverage->id,
								'product_name'     => $activeCoverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $activeCoverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'current_status'   => $currentStatus,
								'current_state'    => $currentState,
								'pro_rate'         => TRUE,
								'old_age'          => $oldAge,
								'new_age'          => $newAge,
								'old_dob'          => $oldDob,
								'new_dob'          => $newdob,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								'renewal_date'  => $renewal_date,
							]);
						} else {
							
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_DOB],
								'coverage_id'      => $activeCoverage->id,
								'product_name'     => $activeCoverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $activeCoverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $oldAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $oldMonthly,
								'without_loading'  => $without_loading,
								'current_status'   => $currentStatus,
								'current_state'    => $currentState,
								'pro_rate'         => TRUE,
								'old_age'          => $oldAge,
								'new_age'          => $newAge,
								'old_dob'          => $oldDob,
								'new_dob'          => $newdob,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
							]);
						}

							array_push($coverageIds,$activeCoverage->id);
						}else{
						{/*	do {
								$newCoverage = $newCoverage - 1;
								$newPrice    = $activeCoverage->product->getPrice($this->profile,$newCoverage,$occ_loading,$newAge,NULL);;
								$newAnnually = number_format($newPrice,2);
							} while ($newAnnually > $oldAnnually); */}

							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,Enum::ACTION_METHOD_CHANGE_DOB],
								'coverage_id'      => $activeCoverage->id,
								'product_name'     => $activeCoverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $activeCoverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'without_loading'  => $without_loading,
								'new_monthly'      => $activeCoverage->product->covertAnnuallyToMonthly($newPrice),
								'current_status'   => $currentStatus,
								'current_state'    => $currentState,
								'old_age'          => $oldAge,
								'new_age'          => $newAge,
								'old_dob'          => $oldDob,
								'new_dob'          => $newdob,
								'changed_at'       => $changedAt,
								'pro_rate'         => TRUE,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								'renewal_date'  => $renewal_date,
							]);

							array_push($coverageIds,$activeCoverage->id);
						}
					}elseif($oldAnnually > $newAnnually){
						if($activeCoverage->product->isMedical()){
							if($latset_activeCoverage_medical_id == $activeCoverage->id){
								array_push($actions,[
									'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_DOB],
									'coverage_id'      => $activeCoverage->id,
									'product_name'     => $activeCoverage->product->name,
									'payment_term'     => $activeCoverage->payment_term,
									'payment_monthly'  => $activeCoverage->payment_monthly,
									'payment_annually' => $activeCoverage->payment_annually,
									'old_coverage'     => $oldCoverage,
									'new_coverage'     => $newCoverage,
									'old_annually'     => $oldAnnually,
									'new_annually'     => $newAnnually,
									'old_monthly'      => $oldMonthly,
									'new_monthly'      => $newMonthly,
									'without_loading'  => $without_loading,
									'current_status'   => $currentStatus,
									'current_state'    => $currentState,
									'old_age'          => $oldAge,
									'new_age'          => $newAge,
									'old_dob'          => $oldDob,
									'new_dob'          => $newdob,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
								]);
							}else{
								array_push($actions,[
									'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_DOB],
									'coverage_id'      => $activeCoverage->id,
									'product_name'     => $activeCoverage->product->name,
									'payment_term'     => $activeCoverage->payment_term,
									'payment_monthly'  => $activeCoverage->payment_monthly,
									'payment_annually' => $activeCoverage->payment_annually,
									'old_coverage'     => $oldCoverage,
									'new_coverage'     => $newCoverage,
									'old_annually'     => $oldAnnually,
									'new_annually'     => $oldAnnually,
									'old_monthly'      => $oldMonthly,
									'new_monthly'      => $oldMonthly,
									'without_loading'  => $without_loading,
									'current_status'   => $currentStatus,
									'current_state'    => $currentState,
									'old_age'          => $oldAge,
									'new_age'          => $newAge,
									'old_dob'          => $oldDob,
									'new_dob'          => $newdob,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
								]);

							} 
						}
							else {
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_DOB],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,
							'payment_term'     => $activeCoverage->payment_term,
							'payment_monthly'  => $activeCoverage->payment_monthly,
							'payment_annually' => $activeCoverage->payment_annually,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'current_status'   => $currentStatus,
							'current_state'    => $currentState,
							'old_age'          => $oldAge,
							'new_age'          => $newAge,
							'old_dob'          => $oldDob,
							'new_dob'          => $newdob,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
						]);
					         }

						array_push($coverageIds,$activeCoverage->id);
					}else{
						if($activeCoverage->product->isMedical()){
							if($latset_activeCoverage_medical_id == $activeCoverage->id){
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_CHANGE_DOB],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,	
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'current_status'   => $currentStatus,
							'current_state'    => $currentState,
							'old_age'          => $oldAge,
							'new_age'          => $newAge,
							'old_dob'          => $oldDob,
							'new_dob'          => $newdob,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
						]);
					}else{
						// dd($activeCoverage->id);
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_DOB],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,
							'payment_term'     => $activeCoverage->payment_term,
							'payment_monthly'  => $activeCoverage->payment_monthly,
							'payment_annually' => $activeCoverage->payment_annually,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $oldAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $oldMonthly,
							'without_loading'  => $without_loading,
							'current_status'   => $currentStatus,
							'current_state'    => $currentState,
							'old_age'          => $oldAge,
							'new_age'          => $newAge,
							'old_dob'          => $oldDob,
							'new_dob'          => $newdob,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
						]);
					}  
				} else{
				
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_CHANGE_DOB],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,	
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'current_status'   => $currentStatus,
							'current_state'    => $currentState,
							'old_age'          => $oldAge,
							'new_age'          => $newAge,
							'old_dob'          => $oldDob,
							'new_dob'          => $newdob,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
						]);
					}
						array_push($coverageIds,$activeCoverage->id);
					}
				}
			}
		}

		if(empty($actions)){
			array_push($actions,[
				'methods' => [Enum::ACTION_METHOD_CHANGE_DOB],
				'old_age' => $oldAge,
				'new_age' => $newAge,
				'old_dob' => $oldDob,
				'new_dob' => $newdob,
			]);
		}

		if(!empty($actions)){
			if($this->reCalculateStatus){
				$this->action->update([
										  'actions'    => $actions,
										  'updated_at' => Carbon::now()
									  ]);
				$this->reCalculateStatus = FALSE;
				Modal::success($this,__('web/messages.succecfully_recalculated'));
			}else{
				$action = auth('internal_users')->user()->actions()->create([
																				'user_id' => $this->profile->user->id,
																				'type'       => Enum::ACTION_TYPE_AMENDMENT,
																				'event'   => Enum::ACTION_EVENT_CHANGE_DOB,
																				'actions' => $actions,
																				'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
																			]);
				$action->coverages()->attach($coverageIds);
				Modal::success($this,__('web/messages.succecfully_added'));
			}
		}
	}

	public function addGenderAction()
	{
		$messages = [
			'not_in' => __('web/messages.should_be_different'),
		];

		$this->validate([
							'gender' => 'required|in:male,female|not_in:' . $this->profile->gender,
						],$messages);

		$actions     = [];
		$coverageIds = [];
		$coverages = Coverage::query()
		                    ->where('covered_id',$this->profile->id)
							->where('payer_id','=',$this->profile->user_id)
		                    //->where('product_id',$product->id)
                            //->whereNotNull('last_payment_on')
		                    ->orderBy('product_id','desc')
		                    ->where('state',Enum::COVERAGE_STATE_ACTIVE)
							->where('product_name','!=', 'Medical')
		                    ->get();

		// $coverages = Coverage::query()
		// 				->where('covered_id',$this->profile->id)
		// 				->whereNotNull('last_payment_on')
		// 				->orderBy('last_payment_on')
		// 				->where('state',Enum::COVERAGE_STATE_ACTIVE)
		// 				->get();

		$oldGender = $this->profile->gender;
		$newGender = $this->gender;

		$old_occ  =IndustryJob::where('id',$this->profile->occ)->first();
		
		$activeCoverages_medical =  Coverage::query()
		->where('covered_id',$this->profile->id)
		->where('payer_id','=',$this->profile->user_id)
		->orderBy('product_id','desc')
		->where('state',Enum::COVERAGE_STATE_ACTIVE)
		->where('product_name', 'Medical')
		->get();

			$med_monthly=0;
			$med_annually=0;
			$med_without_loading=0;
			// $med_payment_annually_new=0;
			// $med_payment_monthly_new=0;
            $med_coverage_id = []; 

		foreach ($activeCoverages_medical as $activeCoverage_medical){
			$med_monthly+=$activeCoverage_medical->payment_monthly;
			$med_annually+=$activeCoverage_medical->payment_annually;
			$med_without_loading+=$activeCoverage_medical->payment_without_loading;
			// $med_payment_annually_new+=$activeCoverage_medical->payment_annually_new;
			// $med_payment_monthly_new+=$activeCoverage_medical->payment_monthly_new;
			array_push($med_coverage_id,$activeCoverage_medical->id);
		}
		
				$latset_activeCoverage_medical =  Coverage::query()
				->where('covered_id',$this->profile->id)
				->where('payer_id','=',$this->profile->user_id)
				//->where('product_id',$product->id)
				//->whereNotNull('last_payment_on')
				->orderBy('product_id','desc')
				->where('state',Enum::COVERAGE_STATE_ACTIVE)
				->where('product_name', 'Medical')
				->latest()->first();

				// $latset_activeCoverage_medical->payment_monthly=$med_monthly;				
				// $latset_activeCoverage_medical->payment_annually=$med_annually;
				// $latset_activeCoverage_medical->payment_without_loading=$med_without_loading;
				// $latset_activeCoverage_medical->payment_annually_new=$med_payment_annually_new;
				// $latset_activeCoverage_medical->payment_monthly_new=$med_payment_monthly_new;

				foreach($activeCoverages_medical as $activeCoverage_medical ){
					$occ      = IndustryJob::where('industry_id',$this->industryId)->where('id',$this->jobId)->first();
					$old_occ  =IndustryJob::where('id',$this->profile->occ)->first();
					$occ_loading_med = $occ->Medical;
					$old_loading_med = $old_occ->Medical;

					if($activeCoverage_medical->id == $latset_activeCoverage_medical->id && $oldGender != $newGender)
					{
						$activeCoverage_medical->payment_monthly=$med_monthly;				
						$activeCoverage_medical->payment_annually=$med_annually;
						$activeCoverage_medical->payment_without_loading=$med_without_loading;
						// $activeCoverage_medical->payment_annually_new=$med_payment_annually_new;
						// $activeCoverage_medical->payment_monthly_new=$med_payment_monthly_new;			
					}
					$coverages->push($activeCoverage_medical);
				}

				$latset_activeCoverage_medical_id = $latset_activeCoverage_medical->id ?? null;

		if(!empty($coverages)){
			$currentAge = Carbon::parse($this->profile->dob)->age;

			foreach ($coverages as $coverage) {
				if($coverage->product->name == 'Death'){
					$occ_loading = $old_occ->death;
				}elseif($coverage->product->name == 'Accident'){
					$occ_loading = $old_occ->Accident;
				}elseif($coverage->product->name == 'Medical'){
					$occ_loading = $old_occ->Medical;
				}elseif($coverage->product->name == 'Disability'){
					$occ_loading = $old_occ->TPD;
				}else{
					$occ_loading = NULL;
				}
				$underwriting =Underwriting::where('id',$coverage->uw_id)->first();

				$deductible    = $coverage->product_name == 'Medical' ? $coverage->deductible : NULL;
				$newPrice      = $coverage->product->getPrice($this->profile,$coverage->coverage,$occ_loading,$currentAge,$deductible,$underwriting,$newGender)[0];
				$newAnnually   = Helpers::round_up($newPrice,2);
				$oldAnnually   = $coverage->payment_annually;
				$newMonthly    = $coverage->product->covertAnnuallyToMonthly($newPrice);
				$oldMonthly    = $coverage->payment_monthly;
				$oldCoverage   = $coverage->coverage;
				$newCoverage   = $coverage->coverage;
				$currentState  = $coverage->state;
				$currentStatus = $coverage->status;
				$without_loading =round($coverage->product->getPrice($this->profile,$coverage->coverage,$occ_loading,$currentAge,$deductible,$newGender)[3],2);
				if($coverage->payment_term =='monthly'){
					$without_loading =(Helpers::round_up($without_loading * 0.085, 2));	
				}
				$changedAt      = Carbon::now()->toDateTimeString();
				$firstPaymentOn = Carbon::parse($coverage->first_payment_on)->toDateTimeString();
				$nextPaymentOn  = Carbon::parse($coverage->next_payment_on)->toDateTimeString();
				$lastPaymentOn  = Carbon::parse($coverage->last_payment_on)->toDateTimeString();
				$renewal_date = Carbon::parse($coverage->renewal_date)->toDateTimeString();

				if($coverage->product->isMedical() && $latset_activeCoverage_medical_id != $coverage->id ){
					$newAnnually =$coverage->payment_annually;
					$newMonthly  =$coverage->payment_monthly;
					$without_loading=$coverage->payment_without_loading;
				}
				// payed more
				if($oldAnnually > $newAnnually){
					if($coverage->product->isMedical()){
						if($latset_activeCoverage_medical_id == $coverage->id){
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_GENDER],
								'coverage_id'      => $coverage->id,
								'product_name'     => $coverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $coverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'old_gender'   => $oldGender,
							    'new_gender'   => $newGender,
								'pro_rate'         => TRUE,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								
							]);
						} else {
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_GENDER],
								'coverage_id'      => $coverage->id,
								'product_name'     => $coverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $coverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'old_gender'   => $oldGender,
							    'new_gender'   => $newGender,
								'pro_rate'         => TRUE,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
							
							]);
						}

							array_push($coverageIds,$coverage->id);
					}else{
						array_push($actions,[
							'methods'      => [Enum::ACTION_METHOD_PARTIAL_REFUND,Enum::ACTION_METHOD_CHANGE_GENDER],
							'coverage_id'  => $coverage->id,
							'product_name' => $coverage->product->name,
							'payment_term' => $coverage->payment_term,
							'old_coverage' => $oldCoverage,
							'new_coverage' => $newCoverage,
							'payment_term' => $coverage->payment_term,
							'without_loading'=>$without_loading,
							'old_annually' => $oldAnnually,
							'new_annually' => $newAnnually,
							'old_monthly'  => $oldMonthly,
							'new_monthly'  => $newMonthly,
							'old_gender'   => $oldGender,
							'new_gender'   => $newGender,
							'pro_rate'     => TRUE,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
	
						]);
					
						array_push($coverageIds,$coverage->id);
					}
					

					//array_push($coverageIds,$coverage->id);
				}elseif($oldAnnually < $newAnnually){ // payed less
					if($coverage->product->isMedical()){
						if($latset_activeCoverage_medical_id == $coverage->id){

							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,Enum::ACTION_METHOD_CHANGE_GENDER],
								'coverage_id'      => $coverage->id,
								'product_name'     => $coverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $coverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'old_gender'   => $oldGender,
							    'new_gender'   => $newGender,
								'pro_rate'         => TRUE,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								'renewal_date'  => $renewal_date,
							]);
						} else {

							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_GENDER],
								'coverage_id'      => $coverage->id,
								'product_name'     => $coverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $coverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'old_gender'   => $oldGender,
							    'new_gender'   => $newGender,
								'pro_rate'         => TRUE,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								
							]);
							
						}
						array_push($coverageIds,$coverage->id);
					}else{
						array_push($actions,[
							'methods'        => [Enum::ACTION_METHOD_ADDITIONAL_PREMIUM,Enum::ACTION_METHOD_CHANGE_GENDER],
							'coverage_id'    => $coverage->id,
							'product_name'   => $coverage->product->name,
							'payment_term'   => $coverage->payment_term,
							'old_coverage'   => $oldCoverage,
							'new_coverage'   => $newCoverage,
							'payment_term'   => $coverage->payment_term,
							'without_loading'=>$without_loading,
							'old_annually'   => $oldAnnually,
							'new_annually'   => $newAnnually,
							'old_monthly'    => $oldMonthly,
							'new_monthly'    => $newMonthly,
							'old_gender'     => $oldGender,
							'new_gender'     => $newGender,
							'current_status' => $currentStatus,
							'current_state'  => $currentState,
							'changed_at'   => $changedAt,
							'pro_rate'     => TRUE,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
							'renewal_date'  => $renewal_date,
						]);

						array_push($coverageIds,$coverage->id);
					}
				}else{
					if($coverage->product->isMedical() && $oldGender != $newGender) {
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_GENDER],
							'coverage_id'      => $coverage->id,
							'product_name'     => $coverage->product->name,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'payment_term'     => $coverage->payment_term,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'old_gender'   => $oldGender,
							'new_gender'   => $newGender,
							'pro_rate'         => TRUE,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
							
						]);
					}else{
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_CHANGE_GENDER],
							'coverage_id'      => $coverage->id,
							'product_name'     => $coverage->product->name,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'payment_term'     => $coverage->payment_term,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'old_gender'   => $oldGender,
							'new_gender'   => $newGender,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
						
						]);
					}
					

					array_push($coverageIds,$coverage->id);
				}
			}
		}else{
			array_push($actions,[
				'methods'    => [Enum::ACTION_METHOD_CHANGE_GENDER],
				'old_gender' => $oldGender,
				'new_gender' => $newGender,
			]);
		}
		
		if(empty($actions)){
           array_push($actions,[
			  'methods'    => [Enum::ACTION_METHOD_CHANGE_GENDER],
			  'old_gender' => $oldGender,
			  'new_gender' => $newGender,
		    ]);
		   }


		if($this->reCalculateStatus){
			$this->action->update([
									  'actions'    => $actions,
									  'updated_at' => Carbon::now()
								  ]);
			$this->reCalculateStatus = FALSE;
			Modal::success($this,__('web/messages.succecfully_recalculated'));
		}else{
			$action = auth('internal_users')->user()->actions()->create([
																			'user_id' => $this->profile->user->id,
																			'type'       => Enum::ACTION_TYPE_AMENDMENT,
																			'event'   => Enum::ACTION_EVENT_CHANGE_GENDER,
																			'actions' => $actions,
																			'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
																		]);
			$action->coverages()->attach($coverageIds);
			Modal::success($this,__('web/messages.succecfully_added'));
		}

		$this->emit('tableRefresh');
	}

	public function addOccupationAction()
	{
		$messages = [
			'required' => __('web/messages.required_field'),
			'not_in'   => __('web/messages.should_be_different'),
		];

		$this->validate([
							'industryId' => 'required',
							'jobId'      => 'required|not_in:' . $this->profile->occ,
						],$messages);

		$actions     = [];
		$coverageIds = [];

		$activeCoverages = Coverage::query()
		                    ->where('covered_id',$this->profile->id)
							->where('payer_id','=',$this->profile->user_id)
		                    //->where('product_id',$product->id)
                            //->whereNotNull('last_payment_on')
		                    ->orderBy('product_id','desc')
		                    ->where('state',Enum::COVERAGE_STATE_ACTIVE)
							->where('product_name','!=', 'Medical')
		                    ->get();
		$currentAge      = Carbon::parse($this->profile->dob)->age;
		$currentGender   = $this->profile->gender;
		$changedAt       = Carbon::now()->toDateTimeString();
		$oldOcc          = IndustryJob::find($this->profile->occ)->name;
		$newOcc          = IndustryJob::find($this->jobId)->name;

		$activeCoverages_medical =  Coverage::query()
		->where('covered_id',$this->profile->id)
		->where('payer_id','=',$this->profile->user_id)
		->orderBy('product_id','desc')
		->where('state',Enum::COVERAGE_STATE_ACTIVE)
		->where('product_name', 'Medical')
		->get();

			$med_monthly=0;
			$med_annually=0;
			$med_without_loading=0;
			// $med_payment_annually_new=0;
			// $med_payment_monthly_new=0;
            $med_coverage_id = []; 

		foreach ($activeCoverages_medical as $activeCoverage_medical){
			$med_monthly+=$activeCoverage_medical->payment_monthly;
			$med_annually+=$activeCoverage_medical->payment_annually;
			$med_without_loading+=$activeCoverage_medical->payment_without_loading;
			// $med_payment_annually_new+=$activeCoverage_medical->payment_annually_new;
			// $med_payment_monthly_new+=$activeCoverage_medical->payment_monthly_new;
			array_push($med_coverage_id,$activeCoverage_medical->id);
		}
		
				$latset_activeCoverage_medical =  Coverage::query()
				->where('covered_id',$this->profile->id)
				->where('payer_id','=',$this->profile->user_id)
				//->where('product_id',$product->id)
				//->whereNotNull('last_payment_on')
				->orderBy('product_id','desc')
				->where('state',Enum::COVERAGE_STATE_ACTIVE)
				->where('product_name', 'Medical')
				->latest()->first();

				// $latset_activeCoverage_medical->payment_monthly=$med_monthly;				
				// $latset_activeCoverage_medical->payment_annually=$med_annually;
				// $latset_activeCoverage_medical->payment_without_loading=$med_without_loading;
				// $latset_activeCoverage_medical->payment_annually_new=$med_payment_annually_new;
				// $latset_activeCoverage_medical->payment_monthly_new=$med_payment_monthly_new;

				foreach($activeCoverages_medical as $activeCoverage_medical ){
					$occ      = IndustryJob::where('industry_id',$this->industryId)->where('id',$this->jobId)->first();
					$old_occ  =IndustryJob::where('id',$this->profile->occ)->first();
					$occ_loading_med = $occ->Medical;
					$old_loading_med = $old_occ->Medical;

					if($activeCoverage_medical->id == $latset_activeCoverage_medical->id && $occ_loading_med != $old_loading_med)
					{
						$activeCoverage_medical->payment_monthly=$med_monthly;				
						$activeCoverage_medical->payment_annually=$med_annually;
						$activeCoverage_medical->payment_without_loading=$med_without_loading;
						// $activeCoverage_medical->payment_annually_new=$med_payment_annually_new;
						// $activeCoverage_medical->payment_monthly_new=$med_payment_monthly_new;			
					}
					$activeCoverages->push($activeCoverage_medical);
				}

				$latset_activeCoverage_medical_id = $latset_activeCoverage_medical->id ?? null;

		if(empty($activeCoverages)){
			array_push($actions,[
				'methods'    => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
				'old_occ'    => $oldOcc,
				'new_occ'    => $newOcc,
				'industryId' => $this->industryId,
				'jobId'      => $this->jobId,
			]);
		}else{
			foreach ($activeCoverages as $activeCoverage) {
				$occ      = IndustryJob::where('industry_id',$this->industryId)->where('id',$this->jobId)->first();
				$canCover = TRUE;
				$old_occ  =IndustryJob::where('id',$this->profile->occ)->first();
				$canCover = TRUE;

				if($activeCoverage->product->name == 'Death'){
					$occ_loading = $occ->death;
					$old_loading = $old_occ->death;
					if($occ_loading == -1){
						$canCover = FALSE;
					}
				}elseif($activeCoverage->product->name == 'Accident'){
					$occ_loading = $occ->Accident;
					$old_loading = $old_occ->Accident;
					if($occ_loading == -1){
						$canCover = FALSE;
					}
				}elseif($activeCoverage->product->name == 'Medical'){
					$occ_loading = $occ->Medical;
					$old_loading = $old_occ->Medical;
					if($occ_loading == -1){
						$canCover = FALSE;
					}

				}elseif($activeCoverage->product->name == 'Disability'){
					$occ_loading = $occ->TPD;
					$old_loading = $old_occ->TPD;
					if($occ_loading == -1){
						$canCover = FALSE;
					}
				}else{
					$occ_loading = NULL;
				}

				$deductible  = $activeCoverage->product_name == 'Medical' ? $activeCoverage->deductible : NULL;
				$underwriting =Underwriting::where('id',$activeCoverage->uw_id)->first();

				if($canCover){
					$newPrice    = $activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$occ_loading,$currentAge,$deductible,$underwriting,$currentGender)[0];
					$without_loading =round($activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$occ_loading,$currentAge,$deductible,$underwriting,$currentGender)[3],2);
				}else{
					
				  
					$newPrice    = $activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$old_loading,$currentAge,$deductible,$underwriting,$currentGender)[0];
					$without_loading =round($activeCoverage->product->getPrice($this->profile,$activeCoverage->coverage,$old_loading,$currentAge,$deductible,$underwriting,$currentGender)[3],2);
					
				}

				if($activeCoverage->payment_term =='monthly'){
					$without_loading =(Helpers::round_up($without_loading * 0.085, 2));
				}

				if($activeCoverage->product->isMedical() && $occ_loading == $old_loading){
					$newAnnually =$activeCoverage->payment_annually;
					$newMonthly  =$activeCoverage->payment_monthly;
					$without_loading=$activeCoverage->payment_without_loading;
				}else{
				
					if($activeCoverage->product->isMedical() && $latset_activeCoverage_medical_id != $activeCoverage->id ){
						$newAnnually =$activeCoverage->payment_annually;
						$newMonthly  =$activeCoverage->payment_monthly;
						$without_loading=$activeCoverage->payment_without_loading;
					}else{
						$newAnnually = Helpers::round_up($newPrice,2);
						$newMonthly  = $activeCoverage->product->covertAnnuallyToMonthly($newPrice);
					}
					
				}
				
	

				
				$oldAnnually = $activeCoverage->payment_annually;
				$oldMonthly  = $activeCoverage->payment_monthly;
				$oldCoverage = $activeCoverage->coverage;
				$newCoverage = $activeCoverage->coverage;

				$firstPaymentOn      = Carbon::parse($activeCoverage->first_payment_on)->toDateTimeString();
				$nextPaymentOn       = Carbon::parse($activeCoverage->next_payment_on)->toDateTimeString();
				$lastPaymentOn       = Carbon::parse($activeCoverage->last_payment_on)->toDateTimeString();
				$currrentPaymentTerm = $activeCoverage->payment_term;
				$renewal_date = Carbon::parse($activeCoverage->renewal_date)->toDateTimeString();



				if($canCover){
					if($newAnnually > $oldAnnually){ // payed more
						if($activeCoverage->product->isMedical()){
							if($latset_activeCoverage_medical_id == $activeCoverage->id){

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
									'without_loading'  => $without_loading,
									'old_occ'          => $oldOcc,
									'new_occ'          => $newOcc,
									'pro_rate'         => TRUE,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
									'industryId'       => $this->industryId,
									'jobId'            => $this->jobId,
									'renewal_date'  => $renewal_date,
								]);
							} else {

								array_push($actions,[
									'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
									'coverage_id'      => $activeCoverage->id,
									'product_name'     => $activeCoverage->product->name,
									'old_coverage'     => $oldCoverage,
									'new_coverage'     => $newCoverage,
									'payment_term'     => $activeCoverage->payment_term,
									'old_annually'     => $oldAnnually,
									'new_annually'     => $newAnnually,
									'old_monthly'      => $oldMonthly,
									'new_monthly'      => $newMonthly,
									'without_loading'  => $without_loading,
									'old_occ'          => $oldOcc,
									'new_occ'          => $newOcc,
									'pro_rate'         => TRUE,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
									'industryId'       => $this->industryId,
									'jobId'            => $this->jobId,
								]);
								
							}
						}
						else {
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
							'without_loading'  => $without_loading,
							'old_occ'          => $oldOcc,
							'new_occ'          => $newOcc,
							'pro_rate'         => TRUE,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
							'industryId'       => $this->industryId,
							'jobId'            => $this->jobId,
							'renewal_date'     => $renewal_date,

							
						]);
					}

						array_push($coverageIds,$activeCoverage->id);
					}elseif($newAnnually < $oldAnnually){ // payed less
						if($activeCoverage->product->isMedical()){

							// Commented below lines on 20/11/23 to fix the std occ change issue reported (Dev-28 (refund not initiated even when there was change in occ loading))

							// if($currrentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
							// 	array_push($actions,[
							// 		'methods'          => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
							// 		'coverage_id'      => $activeCoverage->id,
							// 		'product_name'     => $activeCoverage->product->name,
							// 		'old_coverage'     => $oldCoverage,
							// 		'new_coverage'     => $newCoverage,
							// 		'payment_term'     => $activeCoverage->payment_term,
							// 		'old_annually'     => $oldAnnually,
							// 		'new_annually'     => $newAnnually,
							// 		'old_monthly'      => $oldMonthly,
							// 		'new_monthly'      => $newMonthly,
							// 		'without_loading'  => $without_loading,
							// 		'old_occ'          => $oldOcc,
							// 		'new_occ'          => $newOcc,
							// 		'changed_at'       => $changedAt,
							// 		'first_payment_on' => $firstPaymentOn,
							// 		'next_payment_on'  => $nextPaymentOn,
							// 		'last_payment_on'  => $lastPaymentOn,
							// 		'industryId'       => $this->industryId,
							// 		'jobId'            => $this->jobId,
							// 	]);

							// 	array_push($coverageIds,$activeCoverage->id);
							// }else{
								if($latset_activeCoverage_medical_id == $activeCoverage->id){
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
									'without_loading'  => $without_loading,
									'old_occ'          => $oldOcc,
									'new_occ'          => $newOcc,
									'pro_rate'         => TRUE,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
									'industryId'       => $this->industryId,
									'jobId'            => $this->jobId,
								]);
							} else {
								array_push($actions,[
									'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
									'coverage_id'      => $activeCoverage->id,
									'product_name'     => $activeCoverage->product->name,
									'old_coverage'     => $oldCoverage,
									'new_coverage'     => $newCoverage,
									'payment_term'     => $activeCoverage->payment_term,
									'old_annually'     => $oldAnnually,
									'new_annually'     => $newAnnually,
									'old_monthly'      => $oldMonthly,
									'new_monthly'      => $newMonthly,
									'without_loading'  => $without_loading,
									'old_occ'          => $oldOcc,
									'new_occ'          => $newOcc,
									'pro_rate'         => TRUE,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
									'industryId'       => $this->industryId,
									'jobId'            => $this->jobId,
								]);
							}

								array_push($coverageIds,$activeCoverage->id);
							//}

						}else{

							// Commented below lines on 20/11/23 to fix the std occ change issue reported (Dev-28 (refund not initiated even when there was change in occ loading))
							
							// if($currrentPaymentTerm == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
							// 	array_push($actions,[
							// 		'methods'          => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
							// 		'coverage_id'      => $activeCoverage->id,
							// 		'product_name'     => $activeCoverage->product->name,
							// 		'old_coverage'     => $oldCoverage,
							// 		'new_coverage'     => $newCoverage,
							// 		'payment_term'     => $activeCoverage->payment_term,
							// 		'old_annually'     => $oldAnnually,
							// 		'new_annually'     => $newAnnually,
							// 		'old_monthly'      => $oldMonthly,
							// 		'new_monthly'      => $newMonthly,
							// 		'without_loading'  => $without_loading,
							// 		'old_occ'          => $oldOcc,
							// 		'new_occ'          => $newOcc,
							// 		'changed_at'       => $changedAt,
							// 		'first_payment_on' => $firstPaymentOn,
							// 		'next_payment_on'  => $nextPaymentOn,
							// 		'last_payment_on'  => $lastPaymentOn,
							// 		'industryId'       => $this->industryId,
							// 		'jobId'            => $this->jobId,
							// 	]);
							// }else{
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
									'without_loading'  => $without_loading,
									'old_occ'          => $oldOcc,
									'new_occ'          => $newOcc,
									'pro_rate'         => TRUE,
									'changed_at'       => $changedAt,
									'first_payment_on' => $firstPaymentOn,
									'next_payment_on'  => $nextPaymentOn,
									'last_payment_on'  => $lastPaymentOn,
									'industryId'       => $this->industryId,
									'jobId'            => $this->jobId,
								]);
							//}
							array_push($coverageIds,$activeCoverage->id);
						}
					}else{
						if($activeCoverage->product->isMedical() && $occ_loading != $old_loading) {
							array_push($actions,[
								'methods'          => [Enum::ACTION_METHOD_TERMINATE,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
								'coverage_id'      => $activeCoverage->id,
								'product_name'     => $activeCoverage->product->name,
								'old_coverage'     => $oldCoverage,
								'new_coverage'     => $newCoverage,
								'payment_term'     => $activeCoverage->payment_term,
								'old_annually'     => $oldAnnually,
								'new_annually'     => $newAnnually,
								'old_monthly'      => $oldMonthly,
								'new_monthly'      => $newMonthly,
								'without_loading'  => $without_loading,
								'old_occ'          => $oldOcc,
								'new_occ'          => $newOcc,
								'pro_rate'         => TRUE,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								'industryId'       => $this->industryId,
								'jobId'            => $this->jobId,
							]);
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
								'without_loading'  => $without_loading,
								'old_occ'          => $oldOcc,
								'new_occ'          => $newOcc,
								'changed_at'       => $changedAt,
								'first_payment_on' => $firstPaymentOn,
								'next_payment_on'  => $nextPaymentOn,
								'last_payment_on'  => $lastPaymentOn,
								'industryId'       => $this->industryId,
								'jobId'            => $this->jobId,
							]);
						}
						
					 
						array_push($coverageIds,$activeCoverage->id);
					}
				}else{ // not cover
                    
					$first_payment =Coverage::where('product_id',$activeCoverage->product_id)->where('owner_id',$activeCoverage->owner_id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->orderBy('created_at','asc')->first()->first_payment_on;
                    $now =Carbon::now();
					$diff =$now->diffInDays(Carbon::parse($first_payment));
					//dd($diff);
					if($diff >15){
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_REFUND,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'payment_term'     => $activeCoverage->payment_term,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'old_occ'          => $oldOcc,
							'new_occ'          => $newOcc,
							'pro_rate'         => TRUE,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
							'industryId'       => $this->industryId,
							'jobId'            => $this->jobId,
						]);
	
						array_push($coverageIds,$activeCoverage->id);
					}else{
						array_push($actions,[
							'methods'          => [Enum::ACTION_METHOD_FULL_REFUND,Enum::ACTION_METHOD_CHANGE_OCCUPATION],
							'coverage_id'      => $activeCoverage->id,
							'product_name'     => $activeCoverage->product->name,
							'old_coverage'     => $oldCoverage,
							'new_coverage'     => $newCoverage,
							'payment_term'     => $activeCoverage->payment_term,
							'old_annually'     => $oldAnnually,
							'new_annually'     => $newAnnually,
							'old_monthly'      => $oldMonthly,
							'new_monthly'      => $newMonthly,
							'without_loading'  => $without_loading,
							'old_occ'          => $oldOcc,
							'new_occ'          => $newOcc,
							'pro_rate'         => TRUE,
							'changed_at'       => $changedAt,
							'first_payment_on' => $firstPaymentOn,
							'next_payment_on'  => $nextPaymentOn,
							'last_payment_on'  => $lastPaymentOn,
							'industryId'       => $this->industryId,
							'jobId'            => $this->jobId,
						]);
	
						array_push($coverageIds,$activeCoverage->id);
					}
					
				}
			}
		}
		
		
		if(empty($actions)){
			array_push($actions,[
				'methods'    => [Enum::ACTION_METHOD_CHANGE_OCCUPATION],
				'old_occ'    => $oldOcc,
				'new_occ'    => $newOcc,
				'industryId' => $this->industryId,
				'jobId'      => $this->jobId,
			]);
		}
		

		if($this->reCalculateStatus){
			$this->action->update([
									  'actions'    => $actions,
									  'updated_at' => Carbon::now()
								  ]);
			$this->reCalculateStatus = FALSE;
			Modal::success($this,__('web/messages.succecfully_recalculated'));
		}else{
			$action = auth('internal_users')
				->user()
				->actions()
				->create([
							 'user_id' => $this->profile->user->id,
							 'type'       => Enum::ACTION_TYPE_AMENDMENT,
							 'event'   => Enum::ACTION_EVENT_CHANGE_OCCUPATION,
							 'actions' => $actions,
							 'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
						 ]);
			$action->coverages()->attach($coverageIds);
			Modal::success($this,__('web/messages.succecfully_added'));
		}
		$this->emit('tableRefresh');
	}

	public function changeOccupation($action)
	{
		$groupId = $this->getGroupId();

		$columnName = 'industry';
		$industryId = IndustryJob::find($this->profile->occ)->industry_id;
		$oldValue   = Industry::find($industryId)->name;
		$industryId = IndustryJob::find($this->jobId)->industry_id;
		$newValue   = Industry::find($industryId)->name;
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);

		$columnName  = 'occ';
		$columnAlias = 'job';
		$oldValue    = IndustryJob::find($this->profile->occ)->name;
		$newValue    = IndustryJob::find($this->jobId)->name;
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias);
		$this->updateProfile($this->jobId,$columnName);
	}

	public function fullRefund($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;
		
		foreach ($actions as $actionItem) {
			//dd($actionItem);
			$amt = CoverageOrder::where('coverage_id',$actionItem['coverage_id'])->first();
			$thank_id_charity = Credit::where('order_id',$amt->order_id)->where('user_id','=',null)->first()->type_item_id ?? null;
			$thank_id = Credit::where('order_id',$amt->order_id)->where('user_id','!=',null)->first()->type_item_id ?? null;
			$thanks = Thanksgiving::where('id', $thank_id)->where('type', 'self')->first()->percentage ?? 0;
			$thanks_charity = Thanksgiving::where('id', $thank_id_charity)->where('type', 'charity')->first()->percentage ?? 0;
			
			if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
				$totalMonthly += $actionItem['new_monthly'];
			}else{
				$totalAnnually += $actionItem['new_annually'];
			}
		}

		$total = $totalAnnually + $totalMonthly;
	
		
		if ($thanks != 0){
			$total = round($total - ($total * $thanks/1000),2);
			}
	
			// dd($total);
		return round($total,2);
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
				if (isset($actionItem['coverage_id']) && $coverage->id == $actionItem['coverage_id']) {
					$newCoverage                   = $coverage->replicate();
					$newCoverage->parent_id        = $actionItem['coverage_id'];
					$newCoverage->payment_monthly  = $actionItem['new_monthly'];
					$newCoverage->payment_annually = $actionItem['new_annually'];
					$newCoverage->payment_without_loading = $actionItem['without_loading'];
					if($actionItem['payment_term']=='monthly'){
						$newCoverage->full_premium =$actionItem['new_monthly'];
					}else{
						$newCoverage->full_premium =$actionItem['new_annually'];
					}

					if($actionItem['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
						$newCoverage->deductible = $actionItem['new_coverage'];
					    $newCoverage->status = 'active';
						
					}else{
						$newCoverage->coverage = $actionItem['new_coverage'];
					}

					$newCoverage->save();

					$testd = CoverageOrder::where("coverage_id",$coverage->id)->first();

					$order = $testd->order_id;
	   
					$corder = new CoverageOrder;
					$corder->coverage_id = $newCoverage->id;
					$corder->order_id = $order;
	   
					$corder->save();

					array_push($newCoverageIds,$newCoverage->id);
				}
				continue;
			}
		};

		return $newCoverageIds;
	}

	public function refund($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;

		foreach ($actions as $actionItem) {
			$amt = CoverageOrder::where('coverage_id',$actionItem['coverage_id'])->first();
			$thank_id_charity = Credit::where('order_id',$amt->order_id)->where('user_id','=',null)->first()->type_item_id ?? null;
			$thank_id = Credit::where('order_id',$amt->order_id)->where('user_id','!=',null)->first()->type_item_id ?? null;
            $thanks = Thanksgiving::where('id', $thank_id)->where('type', 'self')->first()->percentage ?? 0;
			$thanks_charity = Thanksgiving::where('id', $thank_id_charity)->where('type', 'charity')->first()->percentage ?? 0;
			// dd($thanks);
			if(isset($actionItem['pro_rate'])){
				$startDate = Carbon::parse($actionItem['changed_at']);
				$endDate   = Carbon::parse($actionItem['next_payment_on'])->format('Y-m-d');
				$firstDate = Carbon::parse($actionItem['first_payment_on'])->format('Y-m-d');
				$difffDays = $startDate->startOfDay()->diffInDays($endDate);
				$diff_days =date_diff(date_create($firstDate), date_create($endDate));
                            if($diff_days->format("%y") < 1){
                                $te = $diff_days->format("%a");
                            }else{
                                $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                            }

				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
						$diffPrice    = $actionItem['old_monthly'] / $te;
						$totalMonthly += $diffPrice * $difffDays;
				}else{
						$diffPrice    = $actionItem['old_annually'] / $te;
						$totalAnnually += $diffPrice * $difffDays;
				}
			}else{
				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
						$totalMonthly += $actionItem['old_monthly'];
			
				}else{
						$totalAnnually += $actionItem['old_annually'];
				}
			}
		}

		// dd($totalAnnually);
		$total = $totalAnnually + $totalMonthly;
		// dd($total);

		if ($thanks != 0){
		$total = round($total - ($total * $thanks/1000),2);
		}
		// dd($total);

		if ($thanks_charity != 0){
			$total = round($total - ($total * $thanks_charity/1000),2);
		}
		// dd($total);

		// less thanksgiving 10%
		return $total;
		// dd(round($total - ($total * 0.1),2));
	}

	public function partialRefund($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;

		foreach ($actions as $actionItem) {
			$amt = CoverageOrder::where('coverage_id',$actionItem['coverage_id'])->first();
			$thank_id_charity = Credit::where('order_id',$amt->order_id)->where('user_id','=',null)->first()->type_item_id ?? null;
			$thank_id = Credit::where('order_id',$amt->order_id)->where('user_id','!=',null)->first()->type_item_id ?? null;
            $thanks = Thanksgiving::where('id', $thank_id)->where('type', 'self')->first()->percentage ?? 0;
			$thanks_charity = Thanksgiving::where('id', $thank_id_charity)->where('type', 'charity')->first()->percentage ?? 0;


			if(isset($actionItem['pro_rate'])){

				$startDate = Carbon::parse($actionItem['changed_at']);
				$endDate   = Carbon::parse($actionItem['next_payment_on'])->format('Y-m-d');
				$firstDate = Carbon::parse($actionItem['first_payment_on'])->format('Y-m-d');
				$difffDays = $startDate->startOfDay()->diffInDays($endDate);
				$diff_days =date_diff(date_create($firstDate), date_create($endDate));
                            if($diff_days->format("%y") < 1){
                                $te = $diff_days->format("%a");
                            }else{
                                $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                            }
				// $startDate = Carbon::parse($actionItem['changed_at']);
				// $endDate   = Carbon::parse($actionItem['next_payment_on']);
				// $difffDays = $startDate->diffInDays($endDate);

				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['old_monthly'] > $actionItem['new_monthly']){
						$diffPrice    = ($actionItem['old_monthly'] - $actionItem['new_monthly']) / $te;
						$totalMonthly += $diffPrice * $difffDays;
					}
				}else{
					if($actionItem['old_annually'] > $actionItem['new_annually']){
						$diffPrice    = ($actionItem['old_annually'] - $actionItem['new_annually']) / $te;
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
		$firstDate = Carbon::parse($actionItem['first_payment_on']);

			$tr = Carbon::parse($firstDate);
			$ts = Carbon::now();
			$tu = $tr->diffInDays($ts);
		}

		$total = $totalAnnually + $totalMonthly;

		if($tu > 15){

		if ($thanks != 0){
			$total = round($total - ($total * $thanks/1000),2);
			}
		if ($thanks_charity != 0){
				$total = round($total - ($total * $thanks_charity/1000),2);
			}
		}else{
			if ($thanks != 0){
				$total = round($total - ($total * $thanks/1000),2);
				}
		}

		// less thanksgiving 10%
		return $total;
		//return round($total - ($total * 0.1),2);
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

	public function changePaymentTermCoverage($action,$actions)
	{
		foreach ($actions as $actionItem) {
			if($actionItem['new_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){ // annually to monthly (simple)
				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_FULFILLED,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'           => Enum::COVERAGE_STATE_ACTIVE,
					'status'          => Enum::COVERAGE_STATUS_ACTIVE,
					'parent_id'       => $actionItem['coverage_id'],
					'payment_term'    => $actionItem['new_payment_term'],
					'next_payment_on' => Carbon::parse($actionItem['next_payment_on'])->addMonth()->toDateTimeString(),
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();
			}elseif($actionItem['new_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_ANNUALLY){ // monthly to annually(pro rate)
				$to           = Carbon::parse($actionItem['first_payment_on']);
				$from         = Carbon::parse($actionItem['changed_at']);
				$diffInMonths = ceil($to->floatDiffInMonths($from));

				$oldValues = [
					'state'      => Enum::COVERAGE_STATE_INACTIVE,
					'status'     => Enum::COVERAGE_STATUS_FULFILLED,
					'updated_at' => Carbon::now()
				];

				$newValues = [
					'state'           => Enum::COVERAGE_STATE_ACTIVE,
					'status'          => Enum::COVERAGE_STATUS_ACTIVE,
					'parent_id'       => $actionItem['coverage_id'],
					'payment_term'    => $actionItem['new_payment_term'],
					'next_payment_on' => Carbon::parse($actionItem['next_payment_on'])->addYear()->toDateTimeString(),
				];

				$coverage = Coverage::find($actionItem['coverage_id']);
				$coverage->update($oldValues);
				$coverage->replicate()->fill($newValues)->save();

				return round(($actionItem['current_annually'] * (12 - $diffInMonths)) / 12,2);
			}
		}
	}

	public function additionalPremium($action,$actions)
	{
		$totalAnnually = 0;
		$totalMonthly  = 0;

		foreach ($actions as $actionItem) {

			$amt = CoverageOrder::where('coverage_id',$actionItem['coverage_id'])->first();
			$thank_id_charity = Credit::where('order_id',$amt->order_id)->where('user_id','=',null)->first()->type_item_id ?? null;
			$thank_id = Credit::where('order_id',$amt->order_id)->where('user_id','!=',null)->first()->type_item_id ?? null;
            $thanks = Thanksgiving::where('id', $thank_id)->where('type', 'self')->latest()->first()->percentage ?? 0;
			$thanks_charity = Thanksgiving::where('id', $thank_id_charity)->where('type', 'charity')->first()->percentage ?? 0;

			if(isset($actionItem['pro_rate'])){
				// $startDate = Carbon::parse($actionItem['changed_at']);
				// $endDate   = Carbon::parse($actionItem['next_payment_on']);
				// $difffDays = $startDate->diffInDays($endDate);
				$now = Carbon::now();
				//$days = $now->startOfDay()->diffInDays(Carbon::parse($actionItem['next_payment_on']));
				
				$startDate = Carbon::parse($actionItem['changed_at']);
				$endDate   = Carbon::parse($actionItem['next_payment_on'])->format('Y-m-d');
				$firstDate = Carbon::parse($actionItem['first_payment_on'])->format('Y-m-d');
				$difffDays = $now->startOfDay()->diffInDays(Carbon::parse($actionItem['next_payment_on']));
				$diff_days =date_diff(date_create($firstDate), date_create($endDate));
                            if($diff_days->format("%y") < 1){
                                $te = $diff_days->format("%a");
                            }else{
                                $te  = round($diff_days->format("%a")/$diff_days->format("%y"));
                            }
               // dd($difffDays);
			  
				if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
					if($actionItem['new_monthly'] > $actionItem['old_monthly']){
						$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $te;
						$totalMonthly += $diffPrice * $difffDays;
						//$totalMonthly += $actionItem['new_monthly'] - $actionItem['old_monthly'];
					}
					
				}else{
					if($actionItem['new_annually'] > $actionItem['old_annually']){
						$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / $te;
						$totalAnnually += $diffPrice * $difffDays;
					//$totalAnnually += $actionItem['new_annually'] - $actionItem['old_annually'];
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

	
	
		$total = $totalAnnually + $totalMonthly;
		//dd($total);
		$true_amount = round($total,2);
		//dd($total);
		if ($thanks != 0){
			$total = round($total - ($total * $thanks/1000),2);
			}else{
			$total =round($total,2);
			}
        //dd($total);
		return [$total,$true_amount];
	}

	public function autodebit_addPremium($action,$coverages,$premium,$true_amount){
	
		$monthly_pro = 0;
		$annually_pro = 0;
		$without_load = 0;
		$monthly_new = 0;
		$annually_new = 0;
		$actions        = $action->actions;
		$newCoverageIds = [];
		$coverageIds =[];
		foreach ($coverages as $coverage) {

			array_push($coverageIds,$coverage->id);
			foreach ($actions as $actionItem) {
				if (isset($actionItem['coverage_id']) && $coverage->id == $actionItem['coverage_id']) {
					$newCoverage                   = $coverage->replicate();

					$newCoverage->parent_id        = $actionItem['coverage_id'];
					if(isset($actionItem['pro_rate'])){
						$now = Carbon::now();
						$startDate = Carbon::parse($actionItem['changed_at']);
						$endDate   = Carbon::parse($actionItem['next_payment_on'])->format('Y-m-d');
                        $firstDate = Carbon::parse($actionItem['first_payment_on'])->format('Y-m-d');
                        $firstDates = Carbon::parse($actionItem['first_payment_on']);
                        $renewalDate = Carbon::parse($actionItem['renewal_date'])->format('Y-m-d');
						$difffDays = $now->startOfDay()->diffInDays(Carbon::parse($actionItem['next_payment_on']));
						$diffDayRen = $now->startOfDay()->diffInDays(Carbon::parse($actionItem['renewal_date']));
						$monn = Carbon::parse($actionItem['first_payment_on'])->daysInMonth;
						$mon_nex = $firstDates->addDays($monn);
						$diff_days =date_diff(date_create($firstDate), date_create($endDate));
						$diff_day_ren = date_diff(date_create($firstDate), date_create($renewalDate));
						$diff_day_mon = date_diff(date_create($firstDate), date_create($mon_nex));
						$dayy = $now->startOfDay()->diffInDays($mon_nex);
									
								if($diff_days->format("%y") < 1){
										$te = $diff_days->format("%a");
									}else{
										$te  = round($diff_days->format("%a")/$diff_days->format("%y"));
									}

									if($diff_day_ren->format("%y") < 1){
										$tes = $diff_day_ren->format("%a");
									}else{
										$tes  = round($diff_day_ren->format("%a")/$diff_day_ren->format("%y"));
									}

									if($diff_day_mon->format("%y") < 1){
										$tess = $diff_day_mon->format("%a");
									}else{
										$tess  = round($diff_day_mon->format("%a")/$diff_day_mon->format("%y"));
									}
					  
						if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
							if($actionItem['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
							if($actionItem['new_monthly'] > $actionItem['old_monthly']){
								$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $te;
								$monthly_med = $diffPrice * $difffDays;
								$monthly_pro = $actionItem['old_monthly'] + $monthly_med;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;

								$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / $te;
								$annually_med = $diffPrice * $difffDays;
								$annually_pro = $actionItem['old_annually'] + $annually_med;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;

								$monthly_pro_new = $actionItem['old_monthly'] + $monthly_med;

								$diffPrice_new = ($actionItem['new_annually'] - $actionItem['old_annually']) / $tes;
								$annually_med_new = $diffPrice_new * $diffDayRen;
								$annually_pro_new = $actionItem['old_annually'] + $annually_med_new;


							}
						}else{
							if($actionItem['new_monthly'] > $actionItem['old_monthly']){
								$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $te;
								$monthly_pri = $diffPrice * $difffDays;
								$monthly_pro = $actionItem['old_monthly'] + $monthly_pri;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;

								$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / $tess;
								$annually_pri = $diffPrice * $difffDays;
								$annually_pro = $actionItem['old_annually'] + $annually_pri;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;

								$monthly_pro_new = $actionItem['old_monthly'] + $monthly_pri;

								$diffPrice_new = ($actionItem['new_annually'] - $actionItem['old_annually']) / $tes;
								$annually_pri_new = $diffPrice_new * $diffDayRen;
								$annually_pro_new = $actionItem['old_annually'] + $annually_pri_new;
							}
						}
						}else{
							if($actionItem['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
							if($actionItem['new_annually'] > $actionItem['old_annually']){
								$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / $te;
								$annually_med = $diffPrice * $difffDays;
								$annually_pro = $actionItem['old_annually'] + $annually_med;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;

								$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $te;
								$monthly_med = $diffPrice * $difffDays;
								$monthly_pro = $actionItem['old_monthly'] + $monthly_med;
								$without_diff = $actionItem['without_loading'] / $te;
								$without_load = $without_diff * $difffDays;
								
								$diffPrice_new = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $tes;
								$monthly_med_new = $diffPrice_new * $dayy;
								$monthly_pro_new = $actionItem['old_monthly'] + $monthly_med_new;

								$annually_pro_new = $actionItem['old_annually'] + $annually_med;
							}
						}
							else{
								if($actionItem['new_annually'] > $actionItem['old_annually']){
									$diffPrice     = ($actionItem['new_annually'] - $actionItem['old_annually']) / $te;
									$annually_pri = $diffPrice * $difffDays;
									$annually_pro = $actionItem['old_annually'] + $annually_pri;
									$without_diff = $actionItem['without_loading'] / $te;
									$without_load = $without_diff * $difffDays;

									$diffPrice    = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $te;
									$monthly_pri = $diffPrice * $difffDays;
									$monthly_pro = $actionItem['old_monthly'] + $monthly_pri;
									$without_diff = $actionItem['without_loading'] / $te;
									$without_load = $without_diff * $difffDays;

									$diffPrice_new = ($actionItem['new_monthly'] - $actionItem['old_monthly']) / $tes;
									$monthly_pri_new = $diffPrice_new * $dayy;
									$monthly_pro_new = $actionItem['old_monthly'] + $monthly_pri_new;

									$annually_pro_new = $actionItem['old_annually'] + $annually_pri;
								}
							}
						}
					}else{
						if($actionItem['payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
							if($actionItem['new_monthly'] > $actionItem['old_monthly']){
								$monthly_pri = $actionItem['new_monthly'] - $actionItem['old_monthly'];
								$monthly_pro = $actionItem['old_monthly'] + $monthly_pri;
								$without_load = $actionItem['without_loading'];

								$annually_pri = $actionItem['new_annually'] - $actionItem['old_annually'];
								$annually_pro = $actionItem['old_annually'] + $annually_pri;
								$without_load = $actionItem['without_loading'];

								$monthly_pro_new = $actionItem['old_monthly'] + $monthly_pri;

								$annually_pro_new = $actionItem['old_annually'] + $annually_pri;

							}
						}else{
							if($actionItem['new_annually'] > $actionItem['old_annually']){
								$annually_pri = $actionItem['new_annually'] - $actionItem['old_annually'];
								$annually_pro = $actionItem['old_annually'] + $annually_pri;
								$without_load = $actionItem['without_loading'];

								$monthly_pri = $actionItem['new_monthly'] - $actionItem['old_monthly'];
								$monthly_pro = $actionItem['old_monthly'] + $monthly_pri;
								$without_load = $actionItem['without_loading'];

								$monthly_pro_new = $actionItem['old_monthly'] + $monthly_pri;

								$annually_pro_new = $actionItem['old_annually'] + $annually_pri;
							}
						}
					}
					$newCoverage->payment_monthly  = round($monthly_pro,2);

					$newCoverage->payment_annually = round($annually_pro,2);

					// $newCoverage->payment_monthly_new  = round($monthly_pro_new,2);

					// $newCoverage->payment_annually_new = round($annually_pro_new,2);

					$newCoverage->payment_without_loading  = round($without_load,2);
					if($actionItem['payment_term']=='monthly'){
						$newCoverage->full_premium =$actionItem['new_monthly'];
					}else{
						$newCoverage->full_premium =$actionItem['new_annually'];
					}
		
					if($coverage->status == Enum::COVERAGE_STATUS_ACTIVE_INCREASED){
						$newCoverage->status = Enum::COVERAGE_STATUS_ADDPREM_INCREASE_UNPAID;
					}else{
						$newCoverage->status = Enum::COVERAGE_STATUS_ADDPREM_UNPAID;
					}

					if($actionItem['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
						$newCoverage->deductible = $actionItem['new_coverage'];
					    $newCoverage->status = 'active';
						
					}else{
						$newCoverage->coverage = $actionItem['new_coverage'];
					}

					$newCoverage->save();

					array_push($newCoverageIds,$newCoverage->id);
				}
				continue;
			}
		};

		//dd('test');
		// renew order
				
						$newOrder = new Order();
                        $newOrder->amount = $premium;
                        $newOrder->true_amount = $true_amount;
                        //$newOrder->status = Enum::ORDER_SUCCESSFUL;
                        $newOrder->due_date = now();
						$newOrder->payer_id = $this->profile->user_id;
                        $newOrder->retries = 1;
                        $newOrder->type = Enum::ORDER_TYPE_NEW;
                        $newOrder->grace_period = 30;
                        $newOrder->last_try_on = now();
                        $newOrder->next_try_on = Carbon::today()->addDays(7);
                        $newOrder->save();
                        
						foreach($newCoverageIds as $newCoverageId){
							$coverage_order = new CoverageOrder();
							$coverage_order->coverage_id = $newCoverageId;
							$coverage_order->order_id =  $newOrder->id;
							$coverage_order->save();
						}
						

                    $coverage_thanksgiving =CoverageThanksgiving::whereIn('coverage_id',$coverageIds)->get()->pluck('thanksgiving_id')->toArray();
					$thanksgiving=Thanksgiving::whereIn('id',$coverage_thanksgiving)->get();
                    $discount = Helpers::calcThanksgivingDiscount($thanksgiving,$newOrder->true_amount);

					if($discount > 0){
												$discount = $true_amount-$discount;
											}
				//	dd($discount);
                    $owner_user_id = Individual::where('id',$newOrder->coverages()->first()->owner_id)->first()->user_id;
                    $owner_name =Individual::where('id',$newOrder->coverages()->first()->owner_id)->first()->name;
                    $owner =User::where('id',$owner_user_id)->first();
                    if($discount > 0){
                        Credit::createDepositSelf($owner_user_id,$newOrder,$discount);
                    }
					$transaction_success = false;
					$from_ops = true;
					//dd($from_ops);
					$payment = ProcessPayment::dispatchNow($newOrder->id,$from_ops);
					$transaction_check = Transaction::where('order_id',$newOrder->id)->latest()->first();
					if($transaction_check){
					if($transaction_check->success == 1){
						$transaction_success = true;
					}
				}


				if($transaction_success == true){

					$coveragesOwner =Coverage::whereIn('id',$coverageIds)->get();
					$method = Enum::ACTION_METHOD_TERMINATE;
					$this->$method($coveragesOwner);
				}else{
					$coveragesOwner =Coverage::whereIn('id',$newCoverageIds)->get();
					$method = Enum::ACTION_METHOD_TERMINATE;
					$this->$method($coveragesOwner);
				}
						// unsuccefull old order
						// $order->update([
						// 				   'status' => Enum::ORDER_UNSUCCESSFUL
						// 			   ]);
	
					
					return $transaction_success;
					
					}


	private function changeDob($action): void
	{
		$actionParams = collect($action->actions)->first();

		// add particular
		$groupId     = $this->getGroupId();
		$columnName  = 'dob';
		$columnAlias = 'date of birth';
		$oldValue    = Carbon::parse($actionParams['old_dob'])->format('d/m/y');
		$newValue    = Carbon::parse($actionParams['new_dob'])->format('d/m/y');
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName,$columnAlias);

		// update profile
		$this->updateProfile($actionParams['new_dob'],$columnName);

		// update action
		$action->update(['execute_on' => Carbon::now()]);
	}

	private function changeGender($action): void
	{
		$actionParams = collect($action->actions)->first();

		$groupId    = $this->getGroupId();
		$columnName = 'gender';
		$oldValue   = $actionParams['old_gender'];
		$newValue   = $actionParams['new_gender'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);
		$this->updateProfile($newValue,$columnName);
	}

	private function getFirstActiveCoverage()
	{
		return Coverage::query()
					   ->where('covered_id',$this->profile->id)
					   ->where('state',Enum::COVERAGE_STATE_ACTIVE)
					   ->where('status',Enum::COVERAGE_STATUS_ACTIVE)
					   ->whereNotNull('last_payment_on')
					   ->first();
	}

}