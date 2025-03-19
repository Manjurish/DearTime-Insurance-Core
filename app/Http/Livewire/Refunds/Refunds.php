<?php     

namespace App\Http\Livewire\Refunds;

use App\Credit;
use App\Helpers\Enum;
use App\Refund;
use Livewire\Component;

class Refunds extends Component
{
	public    $refund;
	public    $status      = [];
	public    $allowStatus = [];
	public    $confirming;
	public    $effective_date;
	public    $effective_time;
	public    $pay_ref_no;
	protected $listeners   = ['edit','revert'];

	public function render()
	{
		$this->dispatchBrowserEvent('timepicker');
		return view('livewire.refunds.refunds');
	}

	public function edit($uuid)
	{
		$this->allowStatus    = NULL;
		$this->effective_date = NULL;
		$this->effective_time = NULL;
		$this->pay_ref_no     = NULL;
		$this->status         = NULL;
		$this->confirming     = NULL;

		$this->refund = Refund::where('uuid',$uuid)->first();

		if($this->refund->status == Enum::REFUND_STATUS_PENDING){
			$this->allowStatus = [
				Enum::REFUND_STATUS_APPROVE,
				Enum::REFUND_STATUS_REJECT,
			];
			$this->status      = Enum::REFUND_STATUS_APPROVE;
		}elseif($this->refund->status == Enum::REFUND_STATUS_APPROVE){
			$this->allowStatus = [
				Enum::REFUND_STATUS_COMPLETED,
			];
			$this->status      = Enum::REFUND_STATUS_COMPLETED;
		}elseif($this->refund->status == Enum::REFUND_STATUS_COMPLETED){
			$this->allowStatus = NULL;
			//$this->status      = Enum::REFUND_STATUS_COMPLETED;
		}
		$this->dispatchBrowserEvent('timepicker');

	}

	public function update()
	{
		$validatedDate = $this->validate(
			[
				'status'         => 'required',
				'effective_date' => 'required_if:status,' . Enum::REFUND_STATUS_APPROVE,
				'effective_time' => 'required_if:status,' . Enum::REFUND_STATUS_APPROVE,
				'pay_ref_no'     => 'required_if:status,' . Enum::REFUND_STATUS_COMPLETED,
			]);


		if($validatedDate['status'] == Enum::REFUND_STATUS_REJECT){
			$this->refund->update(['status' => $validatedDate['status']]);
		}

		if($validatedDate['status'] == Enum::REFUND_STATUS_APPROVE){
			$this->refund->update([
									  'status'         => $validatedDate['status'],
									  'effective_date' => $validatedDate['effective_date'] . ' ' . $validatedDate['effective_time']
								  ]);
		}

		if($validatedDate['status'] == Enum::REFUND_STATUS_COMPLETED){
			$this->refund->update([
									  'status'     => $validatedDate['status'],
									  'pay_ref_no' => $validatedDate['pay_ref_no']
								  ]);
			// add credit +
			Credit::create([
							   'user_id'      => $this->refund->user_id,
							   'amount'       => $this->refund->amount,
							   //'type'         => 'App\Action',
							   //'type_item_id' => $this->refund->action->id,
						   ]);

		}

		$this->dispatchBrowserEvent('editModalHide');
		$this->emit('tableRefresh');
	}

	public function revert($uuid)
	{
		$this->refund = Refund::where('uuid',$uuid)->first();

		$this->refund->update(['status' => Enum::REFUND_STATUS_PENDING]);

		$this->emit('tableRefresh');
	}

	public function confirm($uuid)
	{
		$this->confirming = $uuid;
	}

}
