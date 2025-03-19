<?php     

namespace App\Http\Livewire;

use App\Action;
use App\Coverage;
use App\Credit;
use App\Helpers\Enum;
use App\Helpers\Modal;
use App\ParticularChange;
use Carbon\Carbon;
use Livewire\Component;

class ChangePaymentTerm extends Component
{
	public $profile;
	public $action;
	public $paymentTerm;
	public $firstActiveCoverage;
	public $reCalculateStatus = FALSE;

	protected $listeners = [
		'refresh' => '$refresh','executeAction','recalculateAction'
	];

	public function mount()
	{
		// peyment term
		$this->firstActiveCoverage = $this->getFirstActiveCoverage();
		$this->paymentTerm         = $this->firstActiveCoverage->payment_term;
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

	///////////// Change Payment Term /////////////

	public function render()
	{
		return view('livewire.change-payment-term');
	}

	public function changePaymentTerm($action)
	{
		$actionParams = collect($action->actions)->first();

		$groupId = $this->getGroupId();

		$columnName = 'payment term';
		$oldValue   = $actionParams['old_payment_term'];
		$newValue   = $actionParams['new_payment_term'];
		$this->saveParticularChange($groupId,$action,$oldValue,$newValue,$columnName);
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

	public function executeAction($uuid)
	{
		$action                           = Action::whereUuid($uuid)->first();
		$changePaymentTermCoverageActions = [];
		$otherActions                     = [];

		foreach ($action->actions as $actionItem) {
			if(!empty($actionItem['methods'])){
				foreach ($actionItem['methods'] as $method) {
					if($method == Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE){
						array_push($changePaymentTermCoverageActions,$actionItem);
					}else{
						array_push($otherActions,$actionItem);
					}
				}
			}
		}

		$totalAdditionalPremium = 0;

		if(!empty($changePaymentTermCoverageActions)){
			$method                 = Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE;
			$totalAdditionalPremium += $this->$method($changePaymentTermCoverageActions);
		}

		if(!empty($totalAdditionalPremium)){
			Credit::create([
							   'from_id'      => $action->user_id,
							   'amount'       => -$totalAdditionalPremium,
							   'type'         => Enum::CREDIT_TYPE_ACTION,
							   'type_item_id' => $action->id,
						   ]);
		}

		if(!empty($otherActions)){
			foreach ($otherActions as $otherAction) {
				foreach ($otherAction['methods'] as $method) {
					if($method != Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE){
						$this->$method($action);
						break;
					}
				}
				break;
			}
		}

		// excute method = event
		//$methodName = $action->event;
		//$this->$methodName($action);

		// update action
		$action->update(['status' => Enum::ACTION_STATUS_EXECUTED]);

		$this->emit('tableRefresh');
	}

	public function recalculateAction($uuid)
	{
		$this->action = Action::whereUuid($uuid)->first();

		if($this->action->event == Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM){
			$this->paymentTerm       = collect($this->action->actions)->first()['new_payment_term'];
			$this->reCalculateStatus = TRUE;
			$this->addPaymentTermAction();
		}
	}

	public function addPaymentTermAction()
	{
		$messages = [
			'not_in' => __('web/messages.should_be_different'),
		];

		$this->validate([
							'paymentTerm' => 'required|in:monthly,annually|not_in:' . $this->firstActiveCoverage->payment_term,
						],$messages);

		$actions     = [];
		$coverageIds = [];

		$coverages = Coverage::query()
							 ->where('covered_id',$this->profile->id)
							 ->where('state',Enum::COVERAGE_STATE_ACTIVE)
							 ->where('status',Enum::COVERAGE_STATUS_ACTIVE)
							 ->whereNotNull('last_payment_on')
							 ->orderBy('last_payment_on')
							 ->get();

		$changedAt = Carbon::now()->toDateTimeString();

		foreach ($coverages as $coverage) {
			$currentMonthly  = $coverage->payment_monthly;
			$currentAnnually = $coverage->payment_annually;
			$firstPaymentOn  = Carbon::parse($coverage->first_payment_on)->toDateTimeString();
			$nextPaymentOn   = Carbon::parse($coverage->next_payment_on)->toDateTimeString();
			$lastPaymentOn   = Carbon::parse($coverage->last_payment_on)->toDateTimeString();

			array_push($actions,[
				'methods'          => [Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE,Enum::ACTION_METHOD_CHANGE_PAYMENT_TERM],
				'coverage_id'      => $coverage->id,
				'product_name'     => $coverage->product->name,
				'old_payment_term' => $coverage->payment_term,
				'new_payment_term' => $this->paymentTerm,
				'changed_at'       => $changedAt,
				'first_payment_on' => $firstPaymentOn,
				'next_payment_on'  => $nextPaymentOn,
				'last_payment_on'  => $lastPaymentOn,
				'current_monthly'  => $currentMonthly,
				'current_annually' => $currentAnnually,
			]);

			array_push($coverageIds,$coverage->id);
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
							 'event'   => Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM,
							 'actions' => $actions,
							 'status'  => Enum::ACTION_STATUS_PENDING_REVIEW
						 ]);
			$action->coverages()->attach($coverageIds);
			Modal::success($this,__('web/messages.succecfully_added'));
		}

		$this->emit('tableRefresh');
	}

	public function changePaymentTermCoverage($actions)
	{
		$totalCredit = 0;
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

				$totalCredit += round(($actionItem['current_annually'] * (12 - $diffInMonths)) / 12,2);
			}
		}

		return $totalCredit;
	}
}

