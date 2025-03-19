<?php

namespace App\Http\Livewire\Tables;

use App\Coverage;
use App\CoverageOrder;
use App\Helpers;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class CoverageordersTable extends LivewireDatatable
{
	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		$orderId = $this->params;
		$query		=	CoverageOrder::query()
						->join('coverages', 'coverages.id', 'coverage_orders.coverage_id')
						->where('coverage_orders.order_id', $orderId);
		return $query;
	}

	public function columns()
	{
		return [
			Column::name('coverages.ref_no')
				->label(__('RefNo'))
				->sortBy('coverages.ref_no')
				->defaultSort('coverages.ref_no')
				->searchable(),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->owner->name ?? NULL;
			})->label(__('Owner'))->sortBy('coverages.owner_id'),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->payer->profile->name ?? NULL;
			}, [], 'payerfunc')->label(__('Payer'))->sortBy('coverages.payer_id'),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->covered->name ?? NULL;
			}, [], 'Covered')->label(__('Covered'))->sortBy('coverages.covered_id'),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->product_name ?? NULL;
			}, [], 'Product')->label(__('Product'))->sortBy('coverages.product_name'),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->status ?? NULL;
			}, [], 'Status')->label(__('Status'))->sortBy('coverages.status'),

			Column::callback(['coverage_orders.coverage_id'], function ($id) {
				$coverage = Coverage::find($id);
				return $coverage->payment_term ?? NULL;
			}, [], 'Payment Term')->label(__('Payment Term'))->sortBy('coverages.payment_term'),

			Column::callback(['coverage_orders.created_at'], function ($created_at) {
				return Carbon::parse($created_at)->format('d/m/Y H:i A');
			})->label(__('Created'))->sortBy('coverage_orders.created_at'),
		];
	}
}