<?php

namespace App\Http\Livewire\Tables;

use App\Coverage;
use App\Helpers;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use App\User;

class CoveragesTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return Coverage::query()
			->leftJoin('individuals as OwnerTab', 'coverages.owner_id', 'OwnerTab.id')
			->leftJoin('individuals as PayerTab', 'coverages.payer_id', 'PayerTab.user_id');
	}

	public function columns()
	{
		return [

			Column::name('ref_no')
				->label(__('RefNo'))
				->sortBy('coverages.ref_no')
				->searchable(),

			Column::name('OwnerTab.name')
				->label(__('Owner'))
				->sortBy('OwnerTab.name'),
			// Column::callback(['id'], function ($id) {
			// 	$coverage = Coverage::find($id);
			// 	return $coverage->owner->name ?? NULL;
			// })->label(__('Owner'))->sortBy('coverages.owner_id'),

			Column::name('PayerTab.name')
				->label(__('Payer'))
				->sortBy('PayerTab.name'),

			Column::name('product_name')
				->label(__('Product'))
				->sortBy('coverages.product_name')
				->searchable(),

			Column::name('status')
				->label(__('Status'))
				->sortBy('coverages.status')
				->searchable(),
			
			Column::name('payment_term')
				->label(__('Payment Term'))
				->sortBy('coverages.payment_term')
				->searchable(),

			Column::name('deductible')
				->hide()
				->label(__('deductible')),

			Column::name('coverage')
				->hide()
				->label(__('coverage')),

			Column::callback(['id', 'product_name'],'coverage')
			->label(__('Coverage')),
			/*DateColumn::name('created_at')
				->label(__('web/messages.created_at'))
				->searchable(),
				DateColumn::name('last_payment_on')
				->label(__('web/messages.payment_at'))
				->searchable(),
	
			*/
			DateColumn::name('created_at')
					->label(__('web/messages.created_at'))
					->sortBy('coverages.created_at')
					->searchable(),
			DateColumn::name('last_payment_on')
			->label(__('web/messages.payment_at'))
					->sortBy('coverages.last_payment_on')
					->searchable(),

			Column::name('payment_monthly')
				->hide()
				->label(__('payment_monthly')),

			Column::name('payment_annually')
				->hide()
				->label(__('payment_annually')),
			
			Column::callback(['id', 'payment_annually'],'premium')
			->label(__('Premium')),
			/*Column::callback(['id'], function ($id) {
				$q = Coverage::find($id);
				return $q->payment_term == 'monthly' ? 'RM' . number_format($q->payment_monthly, 2) : 'RM' . number_format($q->payment_annually, 2);
			}, 'premium')->label(__('Premium'))->searchable(),*/
			Column::callback(['coverages.uuid'], function ($uuid) {
				return view('livewire.table-actions.coverages-action', ['uuid' => $uuid]);
			})->label(__('web/messages.operation'))

			// Column::callback(['active'],function ($active) {
			// 	if($active == 0){
			// 		return __('web/messages.disable');
			// 	}
			// 	return __('web/messages.active');
			// })
			// 	  ->label(__('web/messages.status'))
			// 	  ->searchable(),


			
			/*Column::callback(['uuid'],function ($uuid) {
				return view('livewire.table-actions.users-action',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))*/
		];
	}
	public function premium($id, $q){
        //DB::enableQueryLog();
        
		 return $q->payment_term == 'monthly' ? 'RM' . number_format($q->payment_monthly, 2) : 'RM' . number_format($q->payment_annually, 2);
        // $quries = DB::getQueryLog();
        //dd($quries);
        
    }

 public function coverage($id, $model){
        //DB::enableQueryLog();
         //$q = Coverage::find($id);
		 return $model->product_name == 'medical' ? 'RM' . number_format($model->deductible, 2) : 'RM' . number_format($model->coverage, 2);
        // $quries = DB::getQueryLog();
        //dd($quries);
        
    }
}