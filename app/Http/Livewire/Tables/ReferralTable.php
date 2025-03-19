<?php     

namespace App\Http\Livewire\Tables;

use App\Referral;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ReferralTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $rowNumber  = 1;

	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		return Referral::query();
		// ->where('payment_status','!=','ONHOLD');
	}

	public function columns()
	{
		return [

			Column::name('from_referral_name')
				  ->label(('Referrer Name'))
				  ->searchable()
				  ->sortBy('from_referral_name'),

			Column::name('to_referee_name')
				  ->label(('Referee Name'))
				  ->searchable()
				  ->sortBy('to_referee_name'),
			
			Column::name('amount')
				  ->label(('Amount'))			  
				  ->searchable()
				  ->sortBy('amount'),

			Column::name('transaction_ref')
				  ->label(('Transaction Ref'))			  
				  ->searchable()
				  ->sortBy('transaction_ref'),
				  
            Column::name('thanksgiving_percentage')
				  ->label(('Thanksgiving Percentage'))  
				  ->searchable()
				  ->sortBy('thanksgiving_percentage'),

            Column::name('payment_status')
				  ->label(('Payment Status'))  
				  ->searchable()
				  ->sortBy('payment_status'),
				  
			Column::callback('id','refAmount')
					->label(__('Referral Amount Time'))
					->sortBy('id')
					->searchable(),

		    Column::callback(['uuid'],function ($uuid) {
				return view('livewire.table-actions.referral-details',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))

			
			
		];
	}

	public function refAmount($id)
	{
      $q = Referral::find($id);

	//   $user=auth()->user();
	   $created = $q['created_at'];
	   $now = Carbon::now();

	   $day = (date_diff(date_create(($now)), date_create(($created)))->format('%a'));

	  return $day." days";

	// return $q['created_at'];

	}
}
