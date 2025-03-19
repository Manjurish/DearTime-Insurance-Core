<?php     

namespace App\Http\Livewire\Tables;

use App\Action;
use App\Helpers\Enum;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ActionsTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $type;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		if($this->type == Enum::ACTION_TABLE_TYPE_BASIC_INFO){
			return Action::query()
						 ->whereIn('event',[
							 Enum::ACTION_EVENT_CHANGE_NAME,
							 Enum::ACTION_EVENT_CHANGE_NATIONALITY,
							 Enum::ACTION_EVENT_CHANGE_ADDRESS,
							 Enum::ACTION_EVENT_CHANGE_DOB,
							 Enum::ACTION_EVENT_CHANGE_GENDER,
							 Enum::ACTION_EVENT_CHANGE_OCCUPATION,
						 ]);
		}elseif($this->type == Enum::ACTION_TABLE_TYPE_PAYMENT_TERM){
			return Action::query()
						 ->whereIn('event',[
							 Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM
						 ]);
		}elseif($this->type == Enum::ACTION_TABLE_TYPE_CANCELL_COVERAGE){
			return Action::query()
						 ->whereIn('event',[
							 Enum::ACTION_EVENT_CANCELL_COVERAGE,Enum::ACTION_EVENT_DEACTIVATE
						 ]);
		}
	}

	public function columns()
	{
		return [
			Column::name('user.email')
				->label(__('web/messages.user'))
				->sortBy('actions.id')
				->searchable(),

			Column::name('type')
				->label(__('web/messages.type'))
				->sortBy('actions.type')
				->searchable(),

			Column::name('event')
				->label(__('web/messages.event'))
				->sortBy('actions.type')
				->searchable(),

			Column::callback('actions','getActions')
				->label(__('web/messages.actions'))
				->sortBy('actions.id')
				->searchable(),

			Column::name('status')
				->label(__('web/messages.status'))
				->sortBy('actions.status')
				->searchable(),

			Column::callback('id','getCreatedBy')
				->label(__('web/messages.created_by'))
				->sortBy('actions.id')
				->searchable(),

			DateColumn::callback(['created_at'],function ($created_at) {
				return Carbon::parse($created_at)->format(config('static.datetime_format'));
			})
				->label(__('web/messages.created_at'))
				->sortBy('actions.created_at')
				->searchable(),

			DateColumn::callback(['updated_at'],function ($updated_at) {
				return Carbon::parse($updated_at)->format(config('static.datetime_format'));
			})
				->label(__('web/messages.updated_at'))
				//->defaultSort('desc')
				->sortBy('actions.updated_at')
				->searchable(),

			DateColumn::callback(['execute_on'],function ($execute_on) {
				if(!empty($execute_on)){
					return Carbon::parse($execute_on)->format(config('static.datetime_format'));
				}else{
					return null;
				}

			})
				->label(__('web/messages.execute_on'))
				//->defaultSort('desc')
				->sortBy('actions.execute_on')
				->searchable(),

			Column::callback(['uuid','status','event'],function ($uuid,$status,$event) {
				return view('livewire.table-actions.actions-table-actions',['uuid' => $uuid,'status' => $status,'event' => $event,'type' => $this->type]);
			})->label(__('web/messages.operation'))
		];
	}

	public function getActions($actions)
	{
		$actions = json_decode($actions,TRUE);
		$result  = '';
		foreach ($actions as $action) {
			foreach ($action as $key => $item) {
				if(is_array($item)){
					$result .= $key . ': ';
					foreach ($item as $val) {
						$result .= $val . ' | ';
					}
					$result .= '
					';
				}else{
					$result .= $key . ': ' . $item . '
					';
				}
			}
			$result .= '
			';
		}

		return nl2br(htmlspecialchars($result));
	}

	public function getCreatedBy($id)
	{
		$action = Action::find($id);
		return $action->createdbyable->name;
	}
}
