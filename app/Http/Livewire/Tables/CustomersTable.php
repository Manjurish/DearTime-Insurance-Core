<?php     

namespace App\Http\Livewire\Tables;

use App\Helpers\Enum;
use App\User;
use App\Beneficiary;
use App\Individual;
use App\Coverage;
use Carbon\Carbon;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class CustomersTable extends LivewireDatatable
{
	public $exportable = True;
	public $rowNumber  = 1;
	protected $listeners = [
		'tableRefresh' => '$refresh',
	];

	public function builder()
	{
		$query = User::where('users.type',Enum::USER_TYPE_INDIVIDUAL)->whereRaw("COALESCE(users.corporate_type, '') <> 'payorcorporate'")

		->Join('individuals As Ind1', 'Ind1.user_id', 'users.id');

		return $query;
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

			Column::name('Ind1.name')
				  ->label(__('web/messages.name'))
                  ->sortBy('Ind1.name')
                  ->searchable(),
				  
			// Column::callback('id','getTypeDetail')
			// 	  ->label(__('Type'))
			// 	  ->searchable(),
			// 	//   ->excludeFromExport(),

			Column::callback('id','getTypeDetails')
				  ->label(__('Type'))
				  ->searchable(),
				//   ->hide(),

			Column::name('email')
				  ->label(__('web/messages.email'))
				  ->sortBy('users.email')
				  ->searchable(),

			Column::name('Ind1.mobile')
				  ->label(__('web/messages.mobile'))
                  ->sortBy('Ind1.mobile')
                  ->searchable(),

			Column::name('Ind1.nric')
				  ->label(__('web/messages.nric'))
                  ->sortBy('Ind1.nric')
                  ->searchable(),

			/*Column::callback(['id'],function ($id) {
				return (User::find($id)->profile->selfieMatch->percent ?? 0) . ' %';
			})
				  ->label(__('web/messages.selfie_match'))
				  ->searchable(),*/

			DateColumn::name('created_at')
					  ->label(__('web/messages.registered_at'))
					  ->sortBy('users.created_at')
					  ->searchable(),
					  //->defaultSort('desc'),

			Column::callback(['users.uuid'],function ($uuid) {
				return view('livewire.table-actions.customers-action',['uuid' => $uuid]);
			})->label(__('web/messages.operation'))
			->excludeFromExport(),

			// Column::callback('id','getTypeDetails')	
			// ->label(__('Type'))
		];
	}
	
	// public function getTypeDetail($id)
	// {
	// 	$user = User::find($id);
	// 	$beneficiary =Beneficiary::whereEmail($user->email)->first()??NULL;
	// 	$individual_id =$user->profile->id??NULL;
	// 	$coverage =Coverage::where('covered_id',$individual_id)->where('state','active')->first()??NULL;
		
	// 	if(!empty($user) ){
	// 		if(!empty($beneficiary)&& !empty($coverage)){
	// 		        return '<mark style="background-color:black;color:white;">'."DT-User"."</mark>"." ".'<mark style="background-color:black;color:white;">'.'Beneficiary'."</mark>". " " .
	// 					   '<mark style="background-color:black;color:white;">'.'Owner'."</mark>". " " ;
	// 		}elseif(!empty($beneficiary)){
	// 			    return '<mark style="background-color:black;color:white;">'.'DT-User'."</mark>". " " .
					       
	// 				       '<mark style="background-color:black;color:white;">'.'Beneficiary'."</mark>";
						   
						   
	// 		}elseif(!empty($coverage)){
	// 			    return  '<mark style="background-color:black;color:white;">'."DT-User"."</mark>"." " .
	// 				        '<mark style="background-color:black;color:white;">'.'Owner'."</mark>". " " ;
	// 			}else{
	// 			return '<mark style="background-color:black;color:white;">'."DT-User"."</mark>". " " ;
	// 		}
	// 	}
		
	
	// }

	public function getTypeDetails($id)
	{
		$user = User::find($id);
		$beneficiary =Beneficiary::whereEmail($user->email)->first()??NULL;
		$individual_id =$user->profile->id??NULL;
		$coverage =Coverage::where('covered_id',$individual_id)->where('state','active')->first()??NULL;
		
		if(!empty($user) ){
			if(!empty($beneficiary)&& !empty($coverage)){
			        return 'Owner , Beneficiary , DT-User';
			}elseif(!empty($beneficiary)){
				    return 'Beneficiary , DT-User';
						   
			}elseif(!empty($coverage)){
				    return  'Owner , DT-User';
				}else{
				return 'DT-User';
			}
		}
		
	
	}

}
