<?php     

namespace App\Http\Livewire\Tables;

use App\CustomerVerification;
use App\User;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ekycTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;
	public $payload_value=false;
	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return CustomerVerification::query();
	}

	public function columns()
	{
		return [

			Column::name('individual.user.ref_no')
				  ->label(__('web/messages.ref_no'))
				  ->sortBy('customer_verifications.individual_id')
				  ->searchable(),

			Column::name('individual.name')
				  ->label(__('web/messages.name'))
				  ->sortBy('individuals.name')
				  ->searchable(),

			Column::callback(['id'], function ($id) {
				// dump(User::find($id)->profile);
				return (CustomerVerification::find($id)->individual->user->profile->selfieMatch->percent ?? 0) . ' %';
			})
				  ->label(__('web/messages.selfie_match'))
				  ->sortBy('customer_verifications.individual_id')
				  ->searchable(),

			Column::callback('id','getLastDetail')
				  ->label(__('web/messages.last_action'))
				  ->sortBy('customer_verifications.id')
				  ->searchable(),

			DateColumn::name('updated_at')
					  ->label(__('web/messages.registered_at'))
					 // ->defaultSort('desc')
					 ->sortBy('updated_at')
					  ->searchable(),

			Column::callback('id',function ($id) {
				return view('livewire.table-actions.ekyc-action',['id' => $id]);
			})->label(__('web/messages.operation'))
		];
	}

	public function getLastDetail($id)
	{
		$cv = CustomerVerification::find($id);
		$last_action = $cv->lastDetail;
		if(empty($last_action)){
			return "-";
		}else {
			$this->get_payload();
            if($this->payload_value == true)
            {
				return "By : " . ($last_action->creator->name ?? '-') . "," .
				"Update : " . ($last_action->updated_at->format(config('static.date_format')) ?? '-') . "," .
				"Status : " . ($last_action->status ?? '-');	
			}
			return "By : " . ($last_action->creator->name ?? '-') . "<br>" .
				"Update : " . ($last_action->updated_at->format(config('static.date_format')) ?? '-') . "<br>" .
				"Status : " . ($last_action->status ?? '-');
		}
	}

	public function get_payload()
    {   
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body,true);
       // return $data['updates'][0]['payload']['method']??'';
        $data_val = $data['updates'][0]['payload']['method']??'';
        if(!empty($data_val) && $data_val == 'export')
        {
            $this->payload_value=true;
        }
        

    }
}
