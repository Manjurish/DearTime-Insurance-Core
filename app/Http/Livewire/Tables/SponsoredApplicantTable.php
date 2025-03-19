<?php     

namespace App\Http\Livewire\Tables;

use App\SpoCharityFundApplication;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class SponsoredApplicantTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return SpoCharityFundApplication::query()
		->where('status','!=','pending');
	}

	public function columns()
	{
		return [

            Column::name('individual.name')
            ->label(('name'))
            ->searchable(),

			Column::name('ref_no')
				  ->label(('Application No'))
				  ->sortBy('ref_no')
				  ->searchable(),

			Column::name('submitted_on')
				  ->label(('Submitted On'))
				  ->sortBy('submitted_on')
				  ->searchable(),

			Column::name('renewed_at')
				  ->label(('Renewed At'))
				  ->sortBy('renewed_at')
				  ->searchable(),


			Column::name('form_expiry')
				  ->label(('Form Expiry Date'))
				  ->sortBy('form_expiry')
				  ->searchable(),
			

			Column::name('status')
				  ->label(('Status'))
				  
                  ->sortBy('status')
				  ->searchable(),

		    Column::callback(['uuid'],function ($uuid) {
				return view('livewire.table-actions.spo-applicant-details',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))

			
			
		];
	}
	
	public function sortBy($column)
    {
        parent::sortBy($column);
        $this->emit('tableRefresh');
    }
}
