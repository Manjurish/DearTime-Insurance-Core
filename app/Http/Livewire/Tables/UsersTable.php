<?php     

namespace App\Http\Livewire\Tables;

use App\InternalUser;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class UsersTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return InternalUser::query();
	}

	public function columns()
	{
		return [

			Column::name('name')
				  ->label(__('web/messages.name'))
				  ->sortBy('name')
				  ->searchable(),

			Column::name('email')
				  ->label(__('web/messages.email'))
				  ->sortBy('name')
				  ->searchable(),

			Column::name('position')
				  ->label(__('web/messages.position'))
				  ->sortBy('position')
				  ->searchable(),

			Column::callback(['active'],function ($active) {
				if($active == 0){
					return __('web/messages.disable');
				}
				return __('web/messages.active');
			})
				  ->label(__('web/messages.status'))
				  ->sortBy('active')
				  ->searchable(),

			DateColumn::name('created_at')
					  ->label(__('web/messages.created_at'))
					   ->sortBy('created_at')
					  ->searchable()
					  

			/*Column::callback(['uuid'],function ($uuid) {
				return view('livewire.table-actions.users-action',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))*/
		];
	}
}
