<?php     

namespace App\Http\Livewire\Tables;

use App\Helpers\Enum;
use App\Underwriting;
use App\User;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class UnderwritingTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return Underwriting::query();
	}

	public function columns()
	{
		return [

			Column::name('ref_no')
				  ->label(__('web/messages.ref_no'))
				  ->sortBy('ref_no')
				  ->searchable(),

			Column::name('individual.name')
				->label(__('web/messages.name'))
				->sortBy('individuals.name')
				->searchable(),
			Column::name('individual.nric')
				->label(__('web/messages.nric'))
				->sortBy('individuals.nric')
				->searchable(),

			Column::callback('id', function ($id) {
				$uw = Underwriting::find($id);
				return $uw->creator->profile->name ?? '';
			})
			->label(__('web/messages.created_by'))
			->sortBy('underwritings.id')
			->searchable(),

			/*Column::callback('uuid', function ($uuid) {

				return '<button data-toggle="modal" data-target="#editModal" wire:click="$emitTo(\'underwritings.underwritings\',\'show\', \'' . $uuid . '\')" class="btn btn-primary">Show</button>';

				// $uw = Underwriting::whereUuid($uuid)->first();
				// $answers = $uw->answers; 
				// $out = '';
				// $out .= "Smoke :" . ($answers['smoke'] ?? '-') . "</br>";
				// $out .= "Height :" . ($answers['height'] ?? '-') . "</br>";
				// $out .=  "Weight :" . ($answers['weight'] ?? '-');
				// return $out;
			})
				->label(__('Underwriting Answers'))
				->searchable(),*/
			Column::callback(['uuid'], function ($uuid) {
				return view('livewire.table-actions.underwriting-action', ['uuid' => $uuid]);
			})->label(__('Underwriting Answers')),

			DateColumn::name('created_at')
				->label(__('web/messages.created_at'))
				->sortBy('created_at')
				->searchable(),
		];
	}
}