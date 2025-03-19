<?php     

namespace App\Http\Livewire\Tables;

use App\Helpers\Enum;
use App\Transaction;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class TransactionsTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return Transaction::query();
	}

	public function columns()
	{
		return [

			Column::name('transaction_ref')
				  ->label(__('web/messages.transactions_ref'))
				  ->sortBy('transactions.transaction_ref')
				  ->searchable(),

			Column::name('transaction_id')
				  ->label(__('web/messages.transactions_id'))
				  ->sortBy('transactions.transaction_id')
				  ->searchable(),

			Column::callback(['id'],function ($id) {
				$transaction = Transaction::find($id);
				//return 'RM' . number_format($transaction->amount,2);
				return 'RM' . ($transaction->amount);
			})
				  ->label(__('web/messages.amount'))
				  ->sortBy('transactions.amount')
				  ->searchable(),

			Column::name('card_type')
				  ->label(__('web/messages.card_type'))
				  ->sortBy('transactions.card_type')
				  ->searchable(),

			Column::name('card_no')
				  ->label(__('web/messages.card_no'))
				  ->sortBy('transactions.card_no')
				  ->searchable(),

			Column::name('order.ref_no')
				  ->label(__('web/messages.order_ref'))
				  ->sortBy('orders.ref_no')
				  ->searchable(),

			/*DateColumn::name('date')
					  ->label(__('web/messages.payment_at'))
					  ->searchable(),*/
			DateColumn::name('date')
			->label(__('web/messages.payment_at'))
			->sortBy('transactions.date')
			->searchable(),

			Column::callback('id',function ($id) {
				$tr = Transaction::find($id);
				return $tr->success ? Enum::TRANSACTION_STATUS_SUCCESSFUL : Enum::TRANSACTION_STATUS_UNSUCCESSFUL;
			})
				  ->label(__('web/messages.status'))
				  ->sortBy('transactions.success')
				  ->searchable(),

			/*DateColumn::name('created_at')
					  ->label(__('web/messages.created_at'))
					  ->searchable(),*/
			DateColumn::name('created_at')
			->label(__('web/messages.created_at'))
			->sortBy('transactions.created_at')
			->searchable(),

			Column::callback('order.id',function ($orderId) {
				return view('livewire.table-actions.transactions',['orderId' => $orderId]);
			})->label(__('web/messages.operation'))

		];
	}
}
