<?php     

namespace App\Http\Livewire\Tables;

use App\Claim;
use App\CustomerVerification;
use App\User;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ClaimTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return Claim::query();
	}

	public function columns()
	{
		return [

			Column::name('ref_no')
				  ->label(__('web/messages.ref_no'))
				  ->sortBy('claims.ref_no')
				  ->searchable(),

			Column::name('owner.name')
				  ->label(__('web/messages.owner'))
				  ->sortBy('individuals.name')
				  ->searchable(),

			Column::name('coverage.product_name')
				  ->label(__('mobile.policy'))
				  ->sortBy('coverages.product_name')
				  ->searchable(),

			Column::name('status')
				  ->label(__('web/messages.status'))
				  ->sortBy('claims.status')
				  ->searchable(),

			/*DateColumn::name('created_at')
					  ->label(__('web/messages.created_at'))
					  ->searchable(),*/
			DateColumn::name('created_at')
			->label(__('web/messages.created_at'))
			->sortBy('claims.created_at')
			->searchable(),

			Column::callback('claims.uuid',function ($uuid) {
				return view('livewire.table-actions.claim',['uuid' => $uuid]);
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
			return "By : " . ($last_action->creator->name ?? '-') . "<br>" .
				"Update : " . ($last_action->updated_at->format(config('static.date_format')) ?? '-') . "<br>" .
				"Status : " . ($last_action->status ?? '-');
		}
	}
}
