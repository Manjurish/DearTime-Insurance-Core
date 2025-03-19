<?php     

namespace App\Http\Livewire\Tables;

use App\Helpers\Enum;
use App\ParticularChange;
use Carbon\Carbon;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ParticularChangeTable extends LivewireDatatable
{
    public $exportable = true;
    public $type;
    public $payload_value=false;

    protected $listeners = [
        'tableRefresh' => '$refresh',
    ];

    public function builder()
    {
        return ParticularChange::query();
        if($this->type == Enum::ACTION_TABLE_TYPE_BASIC_INFO){
            return ParticularChange::query()
                ->whereIn('column_name',[
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_NAME,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_COUNTRY,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_NRIC,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_PASSPORT_EXPIRY_DATE,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_ADDRESS,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_STATE,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_CITY,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_POSTCODE,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_DOB,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_GENDER,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_INDUSTRY,
                    Enum::PARTICULAR_CHANGE_COLUMN_NAME_JOB,
                ]);
        }elseif($this->type == Enum::ACTION_TABLE_TYPE_PAYMENT_TERM){
            return ParticularChange::query()
                ->whereIn('event',[
                    Enum::ACTION_EVENT_CHANGE_PAYMENT_TERM
                ]);
        }
    }

    public function columns()
    {
        return [
            Column::name('group_id')
                ->label(__('web/messages.group'))
                ->sortBy('particular_changes.group_id')
                ->searchable(),

            Column::name('column_name')
                ->label(__('web/messages.column_name'))
                ->sortBy('particular_changes.column_name')
                ->searchable(),

            Column::name('old_value')
                ->label(__('web/messages.old_value'))
                ->sortBy('particular_changes.old_value')
                ->searchable(),

            Column::name('new_value')
                ->label(__('web/messages.new_value'))
                ->sortBy('particular_changes.new_value')
                ->searchable(),

            Column::name('createdBy.name')
                ->label(__('web/messages.created_by'))
                ->sortBy('particular_changes.created_by')
                ->searchable(),

			DateColumn::callback(['created_at'],function ($created_at) {
				return Carbon::parse($created_at)->format(config('static.datetime_format'));
			})
				->label(__('web/messages.created_at'))
                ->sortBy('particular_changes.created_at')
				//->defaultSort('desc')
				->searchable(),
        ];
    }
}
