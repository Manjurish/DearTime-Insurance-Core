<?php     

namespace App\Http\Livewire\Coverage;

use App\Action;
use App\Helpers\Enum;
use App\Helpers\Modal;
use App\Product;
use App\Coverage;
use App\Referral;
use App\BankAccount;
use App\Thanksgiving;
use App\Individual;
use App\Order;
use App\CoverageOrder;
use App\Credit;
use App\SpoCharityFunds;
use App\Refund;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;

class Cancell extends Component
{
	public    $profile;
	public    $data;
	public    $action;
	public    $status;
	protected $listeners = ['recalculateAction','executeAction','cancelAction'];

	public function render()
	{
		$this->data =[];
		//dd($this->$data);
		$products = Product::all();

		$coveragesOwner = $this->profile->coverages_owner->where('state',Enum::COVERAGE_STATE_ACTIVE);
		
       
      foreach($coveragesOwner->groupby('payer_id') as $covowner){
		
		foreach ($products as $product) {
			$coverage = $coveragesOwner->where('product_id',$product->id)->where('payer_id',$covowner[0]->payer_id);
			
			 //$cancelreqdate =Carbon::parse($coverage->pluck('cancel_request_date')->implode(' '))->format('Y-m-d');
			 if($coverage->isNotEmpty()){
				$cancelreqdate =Carbon::parse($coverage->first()->cancel_request_date)->format('Y-m-d');

			}else{
				$cancelreqdate = null;
			}
			 

			if(!($coverage->isEmpty())){
			if($coverage->first()->cancel_request_date == null){
				$cancelduration = 0;

			}else{
				$initial_purchase =Carbon::parse($coverage->first()->first_payment_on)->format('Y-m-d');
				$cancelreqdate = Carbon::parse($coverage->first()->cancel_request_date)->format('Y-m-d');
				$cancelduration = (date_diff(date_create(($cancelreqdate)), date_create(($initial_purchase)))->format('%a'));
			}
		}
			if($coverage->count() == 0){ 
				continue;
			}

			if($product->name == Enum::PRODUCT_NAME_MEDICAL){
				$activeCoverage = $coveragesOwner->where('product_id',$product->id)->where('payer_id',$covowner[0]->payer_id)->sortByDesc('created_at')->first()->deductible;
			}else{
				$activeCoverage = $coveragesOwner->where('product_id',$product->id)->where('payer_id',$covowner[0]->payer_id)->sum('coverage');
			}

			$this->data[] = [
				'product-id'       => $product->id,
				'product-name'     => $product->name,
				'payer-id'         => $covowner[0]->payer_id,
				'active-coverage'  => $activeCoverage,
				'freelook' => Carbon::parse($coverage->first()->first_payment_on)->diffInDays(Carbon::now()),
				'cancel_request_date' =>$coverage->first()->cancel_request_date ?? null,
				'cov_status'      =>$coverage->first()->status ?? null,
				'duration'         =>$cancelduration,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
			];
		}
	}
 //dd($this->data);
		return view('livewire.coverage.cancell');
	}

	public function addCancellAction($productName,$activeCoverage,$payer_id)
	{

		
		$coveragesOwner = $this->profile->coverages_owner->where('payer_id',$payer_id)->where('state',Enum::COVERAGE_STATE_ACTIVE);
		$coverage       = $coveragesOwner->where('product_name',$productName);

		if($productName == Enum::PRODUCT_NAME_MEDICAL){
			$checkActiveCoverage = $activeCoverage == $coveragesOwner->where('product_name',$productName)->sortByDesc('created_at')->first()->deductible;
		}else{
			$checkActiveCoverage = $activeCoverage == $coveragesOwner->where('product_name',$productName)->sum('coverage');
		}

		if(!$checkActiveCoverage){
			Modal::success($this,__('web/messages.active_coverage_changed'));
			return;
		}

		// check is in free-look(+15days) | yes: refund + terminate | no: terminate
		$freeLook = $this->profile->freeLook();
		$actions  = [];

		if($freeLook){
			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_FULL_REFUND,Enum::ACTION_METHOD_TERMINATE],
				'product_name'     => $productName,
				'payer-id'         => $payer_id,
				'active_coverage'  => $activeCoverage,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
				'free_look'        => 1,
			]);
		}else{
			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_TERMINATE],
				'product_name'     => $productName,
				'payer-id'         => $payer_id,
				'active_coverage'  => $activeCoverage,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
				'free_look'        => 0,
			]);
		}

		$action = auth('internal_users')->user()->actions()->create([
																		'user_id' => $this->profile->user->id,
																		'type'    => Enum::ACTION_TYPE_TERMINATE,
																		'event'   => Enum::ACTION_EVENT_CANCELL_COVERAGE,
																		'actions' => $actions,
																		'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
																	]);

		$coverageIds = $coveragesOwner->where('product_name',$productName)->pluck('id');
		$action->coverages()->attach($coverageIds);

		$this->emit('tableRefresh');
		Modal::success($this,__('web/messages.succecfully_added'));
	}

	public function adddeactivateAction($productName,$activeCoverage,$payer_id)
	{

		
		$coveragesOwner = $this->profile->coverages_owner->where('payer_id',$payer_id)->where('state',Enum::COVERAGE_STATE_ACTIVE);
		$coverage       = $coveragesOwner->where('product_name',$productName);
		$initial_purchase =Carbon::parse($coverage->first()->first_payment_on)->format('Y-m-d');
		$cancelreqdate = Carbon::parse($coverage->first()->cancel_request_date)->format('Y-m-d');
		$cancelduration = (date_diff(date_create(($cancelreqdate)), date_create(($initial_purchase)))->format('%a'));
       
		$coveragenew =Coverage::where('covered_id',$this->profile->id)->where('payer_id',$payer_id)->where('product_name',$productName)->where('state',Enum::COVERAGE_STATE_ACTIVE)->first();
		$coveragenew->reason_for_cancel = $this->status;
		$coveragenew->save();
		

		// check is in free-look(+15days) | yes: refund + terminate | no: terminate
		$freeLook = $this->profile->freeLook();
		$actions  = [];

	
			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_DEACTIVATE],
				'product_name'     => $productName,
				'payer-id'         => $payer_id,
				'active_coverage'  => $activeCoverage,
				'cancel_request_date'=>$coverage->first()->cancel_request_date,
				'duration'          =>$cancelduration,
				'reason_for_cancel'  =>$coveragenew->reason_for_cancel,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
				'free_look'        => 0,
			]);
		

		$action = auth('internal_users')->user()->actions()->create([
																		'user_id' => $this->profile->user->id,
																		'type'    => Enum::ACTION_TYPE_DEACTIVATE,
																		'event'   => Enum::ACTION_EVENT_DEACTIVATE ,
																		'actions' => $actions,
																		'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
																	]);

		$coverageIds = $coveragesOwner->where('product_name',$productName)->pluck('id');
		$action->coverages()->attach($coverageIds);

		$this->emit('tableRefresh');
		Modal::success($this,__('web/messages.succecfully_added'));
	}



	public function recalculateAction($uuid)
	{
		$this->action = Action::whereUuid($uuid)->first();

		$actions     = collect($this->action->actions)->first();
		$event =$this->action->event;
		$productName = $actions['product_name'];
		$payer_id =$actions['payer-id'];
	

		$firstPaymentOn = $this->profile->coverages_owner()->select('first_payment_on')->orderBy('first_payment_on')->first()->first_payment_on;
		
		$coveragesOwner = $this->profile->coverages_owner->where('payer_id',$payer_id)->where('state',Enum::COVERAGE_STATE_ACTIVE);
		$coverage       = $coveragesOwner->where('product_name',$productName);

		if($productName == Enum::PRODUCT_NAME_MEDICAL){
			$activeCoverage = $coveragesOwner->where('product_name',$productName)->sortByDesc('created_at')->first()->deductible;
		}else{
			$activeCoverage = $coveragesOwner->where('product_name',$productName)->sum('coverage');
		}

		// check is in free-look(+15days) | yes: refund + terminate | no: terminate
		$freeLook = $this->profile->freeLook();
		$actions  = [];

		if($freeLook){
			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_FULL_REFUND,Enum::ACTION_METHOD_TERMINATE],
				'product_name'     => $productName,
				'payer-id'         => $payer_id,
				'active_coverage'  => $activeCoverage,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
				'free_look'        => 1,
			]);
		}else{
			if($event =='deactivate'){
				array_push($actions,[
					'methods'          => [Enum::ACTION_METHOD_DEACTIVATE],
					'product_name'     => $productName,
					'payer-id'         => $payer_id,
					'active_coverage'  => $activeCoverage,
					'cancel_request_date'=>$coverage->first()->cancel_request_date,
					'reason_for_cancel'  =>$coverage->first()->reason_for_cancel,
					'payment-term'     => $coverage->first()->payment_term,
					'payment-monthly'  => $coverage->sum('payment_monthly'),
					'payment-annually' => $coverage->sum('payment_annually'),
					'free_look'        => 0,
				]);
			}else{
			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_TERMINATE],
				'product_name'     => $productName,
				'payer-id'         => $payer_id,
				'active_coverage'  => $activeCoverage,
				'payment-term'     => $coverage->first()->payment_term,
				'payment-monthly'  => $coverage->sum('payment_monthly'),
				'payment-annually' => $coverage->sum('payment_annually'),
				'free_look'        => 0,
			]);
		}
		}

		$this->action->update([
								  'actions'    => $actions,
								  'updated_at' => Carbon::now()
							  ]);

		$this->emit('tableRefresh');
		Modal::success($this,__('web/messages.succecfully_recalculated'));
	}

	public function executeAction($uuid)
	{
		$this->action = Action::whereUuid($uuid)->first();
		$actions      = collect($this->action->actions)->first();
		$event =$this->action->event;
		if($event =='cancellCoverage'){
			
			$coverages=$this->profile->coverages_owner()
			->where('payer_id',$actions['payer-id'])
			->where('product_name',$actions['product_name'])
			->where('state',Enum::COVERAGE_STATE_ACTIVE)->get();
			
			foreach($coverages as $coverage){
				$Coverage_order = CoverageOrder::where('coverage_id',$coverage->id)->first()->order_id;
				//dd($Coverage_order);
				$order =Order::where('id',$Coverage_order)->first();
			    $selectedcov =Coverage::where('id',$coverage->id)->first();
			    $spo_fund = SpoCharityFunds::where('order_id',$Coverage_order)->where('status','ON HOLD')->first();
				$ref_amount = Referral::where('order_id',$Coverage_order)->where('payment_status','ON HOLD')->first();
				$thanksgiving = Thanksgiving::where('individual_id',$this->profile->id)->where('type','charity')->first();
				$self_thanksgiving = Thanksgiving::where('individual_id',$this->profile->id)->where('type','self')->first();
				if($selectedcov->payment_term == 'monthly'){
                    $amount =$selectedcov->payment_monthly;
				}else{
					$amount =$selectedcov->payment_annually;
				}
                if($thanksgiving){
					Credit::create([
						'order_id'=>$Coverage_order,
						'from_id'=>$this->profile->user_id,
						'amount'=>-1*($amount * ($spo_fund->percentage / config('static.thanksgiving_percent'))),
						'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
						'type_item_id'=>$thanksgiving->id
					]);
				}
				
			    if($spo_fund){
					$spo_fund->charity_fund =round($spo_fund->charity_fund - round(($amount*($spo_fund->percentage/1000)),2),2);
                    $reverse_credit =Credit::where('order_id',$Coverage_order)->latest()->first();
					//dd($spo_fund->flcancel_credit);
				  if($spo_fund->flcancel_credit==''||$spo_fund->flcancel_credit==null){
					$credit_id[]=$reverse_credit->id;
					$spo_fund->flcancel_credit =$credit_id;
					//dd($credit_id);

				  }else{
					$credit_id =[] ;
					$credit_id = $spo_fund->flcancel_credit;
					array_push($credit_id,$reverse_credit->id);
					$spo_fund->flcancel_credit =$credit_id;

				  }
			      $spo_fund->save();
			      if($spo_fund->charity_fund<=0){
					$spo_fund->status ='CANCEL-FL';
					$spo_fund->save();
			      }
			  }
			  
			  if($ref_amount){
				$ref_amount->amount =round($ref_amount->amount - round(($selectedcov->payment_annually*($ref_amount->thanksgiving_percentage/1000)),2),2);
				$ref_amount->save();
				if($ref_amount->amount<=0){
				  $ref_amount->payment_status ='CLOSED';
				  $ref_amount->save();
				}
			}
			  
			}
           if($this->profile->is_charity()){
			$sop_fund= new SpoCharityFunds;
            $sop_fund->user_id =$this->profile->user_id; 
            $sop_fund->charity_fund=$actions['payment-annually'];       
            $sop_fund->status ='ADDED';
			$sop_fund->save();
		   }
		}
		

		foreach ($actions['methods'] as $method) {
			$this->$method($actions);
		}


		

		// update action
		$this->action->update([
							'status'     => Enum::ACTION_STATUS_EXECUTED,
							'execute_on' => Carbon::now()
						]);

		$this->emit('tableRefresh');
	}

	public function cancelAction($uuid)

	{
		$action = Action::whereUuid($uuid)->first();

		$action->update([
			'status'     => Enum::ACTION_STATUS_CANCEL,
			'execute_on' => Carbon::now()
		]);

		$this->emit('tableRefresh');
	}

	public function fullRefund($actions)
	{
		// if($actions['payment-term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
		// 	$amount = round($actions['payment-monthly'] - ($actions['payment-monthly'] * 0.1),2);
		// }else{
		// 	$amount = round($actions['payment-annually'] - ($actions['payment-annually'] * 0.1),2);
		// }

		if($actions['payment-term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY){
			$amount = $actions['payment-monthly'];
		}else{
			$amount = $actions['payment-annually'];
		}

		$self_thanksgiving =Thanksgiving::where('individual_id',$this->profile->id)->where('type','self')->latest()->first();
		if($self_thanksgiving){
			$amount = round($amount - ($amount * $self_thanksgiving->percentage/1000),2);
		}
		if($this->profile->user_id ==$actions['payer-id']){
			$bankAccount = $this->action->user->profile->bankAccounts()->first()->account_no;
			$userid =$this->action->user_id;

		}else{
			$indv =Individual::where('user_id',$actions['payer-id'])->first();
			
			//$bankAccount =BankAccount::where('owner_id',$indv->id)->first()->account_no;
			$bankAccount = 9999999999;
        	//dd($bankAccount);
			$userid =$actions['payer-id'];
		}

		Refund::create([
						   'action_id'       => $this->action->id,
						   'payer'           => Enum::REFUND_PAYER_DEARTIME,
						   'user_id'         => $userid,
						   'bank_account_id' => $bankAccount,
						   'amount'          => $amount,
						   'status'          => Enum::REFUND_STATUS_PENDING,
					   ]);

	}

	public function terminate($actions)
	{
		$this->profile->coverages_owner()
			->where('product_name',$actions['product_name'])
			->where('payer_id',$actions['payer-id'])
			->where('state',Enum::COVERAGE_STATE_ACTIVE)
			->update([
						 'state'  => Enum::COVERAGE_STATE_INACTIVE,
						 'status' => Enum::COVERAGE_STATUS_TERMINATE,
					 ]);
	}

	public function deactivate($actions)
	{
		$this->profile->coverages_owner()
			->where('product_name',$actions['product_name'])
			->where('payer_id',$actions['payer-id'])
			->where('state',Enum::COVERAGE_STATE_ACTIVE)
			->update([
						 'state'  => Enum::COVERAGE_STATE_ACTIVE,
						 'status' => Enum::COVERAGE_STATUS_DEACTIVATE,
					 ]);
	}
}
