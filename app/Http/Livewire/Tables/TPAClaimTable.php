<?php     

namespace App\Http\Livewire\Tables;

use App\TPAClaim;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class TPAClaimTable extends LivewireDatatable
{
    public $exportable = true;

    public function builder()
    {
        return TPAClaim::query();
    }

    public function columns()
    {
        return [

			Column::name('claim_type')
				  ->label(__('web/messages.claim_type'))
				  ->sortBy('claim_type')
				  ->searchable(),

			Column::name('claim_no')
				  ->label(__('web/messages.claim_no'))
				  ->sortBy('claim_no')
				  ->searchable(),

			Column::name('policy_no')
				  ->label(__('web/messages.policy_no'))
				  ->sortBy('policy_no')
				  ->searchable(),

			Column::name('id_no')
				  ->label(__('web/messages.id_no'))
				  ->sortBy('id_no')
				  ->searchable(),

			Column::name('date_of_visit')
				  ->label(__('web/messages.date_of_visit'))
				  ->sortBy('date_of_visit')
				  ->searchable(),

			Column::name('date_of_discharge')
				  ->label(__('web/messages.date_of_discharge'))
				  ->sortBy('date_of_discharge')
				  ->searchable(),

			Column::name('diagnosis_code_1')
				  ->label(__('web/messages.diagnosis_code_1'))
				  ->sortBy('diagnosis_code_1')
				  ->searchable(),

			Column::name('diagnosis_code_2')
				  ->label(__('web/messages.diagnosis_code_2'))
				  ->sortBy('diagnosis_code_2')
				  ->searchable(),

			Column::name('diagnosis_code_3')
				  ->label(__('web/messages.diagnosis_code_3'))
				  ->sortBy('diagnosis_code_3')
				  ->searchable(),

			Column::name('provider_code')
				  ->label(__('web/messages.provider_code'))
				  ->sortBy('provider_code')
				  ->searchable(),

			Column::name('provider_name')
				  ->label(__('web/messages.provider_name'))
				  ->sortBy('provider_name')
				  ->searchable(),

			Column::name('provider_invoice_no')
				  ->label(__('web/messages.provider_invoice_no'))
				  ->sortBy('provider_invoice_no')
				  ->searchable(),

			Column::name('date_claim_received')
				  ->label(__('web/messages.date_claim_received'))
				  ->sortBy('date_claim_received')
				  ->searchable(),

			Column::name('medical_leave_from')
				  ->label(__('web/messages.medical_leave_from'))
				  ->sortBy('medical_leave_from')
				  ->searchable(),

			Column::name('medical_leave_to')
				  ->label(__('web/messages.medical_leave_to'))
				  ->sortBy('medical_leave_to')
				  ->searchable(),

			Column::name('tpa_invoice_no')
				  ->label(__('web/messages.tpa_invoice_no'))
				  ->sortBy('tpa_invoice_no')
				  ->searchable(),

			Column::name('cliam_type')
				  ->label(__('web/messages.cliam_type'))
				  ->sortBy('cliam_type')
				  ->searchable(),

			Column::name('actual_invoice_amount')
				  ->label(__('web/messages.actual_invoice_amount'))
				  ->sortBy('actual_invoice_amount')
				  ->searchable(),

			Column::name('approved_amount')
				  ->label(__('web/messages.approved_amount'))
				  ->sortBy('actual_invoice_amount')
				  ->searchable(),

			Column::name('non_approved_amount')
				  ->label(__('web/messages.non_approved_amount'))
				  ->sortBy('non_approved_amount')
				  ->searchable(),

			DateColumn::name('created_at')
					  ->label(__('web/messages.created_at'))
					  ->searchable()
					  ->sortBy('created_at')
					  //->defaultSort(),
        ];
    }
}
