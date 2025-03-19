<?php     

namespace App\Http\Livewire\Tables;

use App\Helpers\Enum;
use App\User;
use App\Individual;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class CorporatesTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return User::where('users.type',Enum::USER_TYPE_CORPORATE)
				->orWhere(function($query) {
					$query->where('users.type',Enum::USER_TYPE_INDIVIDUAL)
					->whereRaw("COALESCE(users.corporate_type, '') = 'payorcorporate'");
				});
	}

	public function columns()
	{
		return [

			Column::name('ref_no')
				  ->label(__('web/messages.ref_no'))
				  ->sortBy('users.ref_no')
				  ->searchable(),

			/*Column::name('type')
				  ->label('Type'),*/

			Column::callback('id','CorpName')
				  ->label(__('web/messages.name'))
				  ->sortBy('users.id')
				  ->searchable(),

			Column::name('email')
				  ->label(__('web/messages.email'))
				  ->sortBy('users.email')
				  ->searchable(),

			/*Column::name('individual.nric')
				  ->label(__('web/messages.nric'))
				  ->searchable(),*/

			/*Column::callback(['id'],function ($id) {
				return (User::find($id)->profile->selfieMatch->percent ?? 0) . ' %';
			})
				  ->label(__('web/messages.selfie_match'))
				  ->searchable(),*/

			DateColumn::callback(['users.created_at'],function ($created_at) {
				return Carbon::parse($created_at)->format(config('static.datetime_format'));
			})
					  ->label(__('web/messages.registered_at'))
					  //->defaultSort('desc')
					  ->sortBy('users.created_at')
					  ->searchable(),

			/*Column::callback(['uuid'],function ($uuid) {
				return view('livewire.table-actions.customers-action',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))*/
		];
	}

	public function CorpName($id)
	{
		$user = User::findOrFail($id);
		return $user->profile->name ?? '';
	}
}
