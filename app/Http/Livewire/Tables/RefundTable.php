<?php     

namespace App\Http\Livewire\Tables;

use App\Refund;
use App\User;
use Illuminate\Support\Facades\Crypt;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class RefundTable extends LivewireDatatable
{
	public $exportable = TRUE;
	public $payload_value=false;

	protected $listeners = ['tableRefresh' => '$refresh'];

	public function builder()
	{	Schema::dropIfExists('bank_temps');
		Schema::create('bank_temps', function (Blueprint $table) {
         
            $table->id();
            $table->integer('refund_id');
            $table->uuid('uuid');
            $table->integer('owner_id'); // for both individual & group
            $table->string('owner_type');
            $table->text('account_no');
            $table->text('bank_name');
            $table->timestamps();
            $table->temporary();
        });


		
            $result=array();
            $refund_details = Refund::whereNotNull('id')->get();
    
            if($refund_details)
            {	
				foreach($refund_details as $refund_value)
                {
					if(!$refund_value->receiver->isCorporatePayer())
                {
                    foreach($refund_value->receiver->profile->bankAccounts as $re_key=>$re_value)
                    {
                        $account_no = $refund_value->corporate_type =='payorcorporate'?'':$re_value->account_no;
						$bank_name =$refund_value->corporate_type =='payorcorporate'?'':$refund_value->receiver->profile->bankAccounts[0]->bank_name;

                        $result[] = ['refund_id'=>$refund_value->id,'account_no'=>$account_no,'bank_name'=>$bank_name ,
                                    'owner_id'=>$re_value->owner_id,'owner_type'=>$re_value->owner_type,
                                    'uuid'=>$re_value->uuid
                                    ];
                    }
				}
				else 
				{
					$result[] = ['refund_id'=>$refund_value->id,'account_no'=>'','bank_name'=>'' ,
                                    'owner_id'=>$re_value->owner_id,'owner_type'=>$re_value->owner_type,
                                    'uuid'=>$re_value->uuid
                                    ];
				}
                }
                DB::table('bank_temps')->insert($result);
			}
		
		//$query = Refund::query();
		$query = Refund::query()
				->Join('users AS A', 'A.id', 'refunds.user_id')
				->Join('individuals As Ind1', 'Ind1.user_id', 'A.id')
				->Join('bank_temps', 'bank_temps.refund_id', 'refunds.id')
				->leftJoin('actions', 'actions.id', 'refunds.action_id')
				->leftJoin('internal_users', 'internal_users.id', 'refunds.authorized_by')
				->whereNull('A.deleted_at')
				->whereNotNull('A.password')
				->whereNotNull('Ind1.user_id')
				->where('Ind1.type', 'owner');
				//->where('bank_temps.owner_type', 'App\Individual');
		
		return $query;
	}

	public function columns()
	{
		return [
			Column::name('refunds.payer')
				  ->label(__('web/messages.refund.payer'))
				  ->sortBy('refunds.payer')
				  ->searchable(),
		
				 Column::name('bank_temps.account_no')
                  ->label(__('web/bank.account_no'))
                  ->sortBy('bank_temps.account_no')
                  ->searchable(),
				  
				  Column::name('bank_temps.bank_name')
				  ->label(__('web/bank.bank_name'))
                  ->sortBy('bank_temps.bank_name')
                  ->searchable(),
				 
				  Column::name('Ind1.nric')
				  ->label(__('web/messages.nric'))
                  ->sortBy('Ind1.nric')
                  ->searchable(),
				 

				  Column::callback(['Ind1.name','A.uuid'],function ($name,$uuid){

                    $this->get_payload();
                    if($this->payload_value == true)
        
                    {
                        return $name ?? '';    
                    }
                    $user =User::where('uuid',$uuid)->first();
					if($user->profile->is_charity()){
						return 'Deartime Charity Fund';
					}else{
						return '<a style="color:#1000ff" href="User/'.$uuid.'">'.$name.'</a>' ?? '';

					}

					
                   // return 'RM'.number_format($amount,2);
                })->label(__('web/messages.refund.receiver'))
                  ->sortBy('Ind1.name')
                  ->searchable(),

				  Column::callback(['refunds.amount'],function ($amount){
                    return 'RM'.number_format($amount,2);
                })->label(__('web/messages.amount'))
                    ->sortBy('refunds.amount')
                ->searchable(),
				

				/*Column::callback(['actions.id','actions.type'],function ($action_id,$action_type){

                    $this->get_payload();
		
					if (!empty($action_id)){
					if($this->payload_value == true)
					{
					return $action_id.','.$action_type;
					}
					return 'ID: ' . $action_id . '</br>' .  'Type: ' . $action_type;
					}
					else{
					if($this->payload_value == true)
					{
					return '';
					}
					return 'ID: --</br> Type: --';
					}
                  
                })->label(__('web/messages.action'))
                  ->sortBy('refunds.action_id')
                  ->searchable(),
				
				Column::name('internal_users.name')
                ->label(__('web/messages.refund.authorized_by'))
                ->sortBy('internal_users.name')
                ->searchable(),*/
		  

		  DateColumn::raw('authorized_at')
					->label(__('web/messages.refund.authorized_at'))
					->format(config('static.datetime_format'))
					->sortBy(DB::raw('DATE_FORMAT(refunds.authorized_at, "%m%d%Y")')),
					//->sortBy(DB::raw('DATE_FORMAT(if(refunds.authorized_at !=NUll,refunds.authorized_at,now()), "%m%d%Y")')),

		  DateColumn::raw('refunds.created_at')
					->label(__('web/messages.refund.created_at'))
					->format(config('static.datetime_format'))
					->sortBy(DB::raw('DATE_FORMAT(refunds.created_at, "%m%d%Y")')),

		  DateColumn::raw('effective_date')
					->label(__('web/messages.refund.effective_date'))
					->format(config('static.datetime_format'))
					->sortBy(DB::raw('DATE_FORMAT(refunds.effective_date, "%m%d%Y")')),

		  Column::name('pay_ref_no')
				->label(__('web/messages.refund.pay_ref_no'))
				->sortBy('refunds.pay_ref_no')
				->searchable(),

		  Column::name('refunds.status')
				->label(__('web/messages.refund.status'))
				->filterable(['pending','approve','complete','reject']),

		  Column::callback(['uuid','status'],function ($uuid,$status) {
			  return view('livewire.table-actions.refunds-action',['uuid' => $uuid,'status' => $status]);
		  })->label(__('web/messages.operation'))
			
			/*Column::name('payer')
				  ->label(__('web/messages.refund.payer'))
				  ->sortBy('refunds.payer')
				  ->searchable(),

				 

				  Column::callback('id','account_number')
				  ->label(__('web/bank.account_no'))
				  ->sortBy('refunds.user_id')
				  ->searchable(),

				  Column::callback('id','bank_name')
				  ->label(__('web/bank.bank_name'))
				  ->sortBy('refunds.user_id')
				  ->searchable(),
				 
				  Column::callback('id','ic_number')
				  ->label(__('web/messages.nric'))
				  ->sortBy('refunds.user_id')
				  ->searchable(),

			Column::callback('id','receiver')
				  ->label(__('web/messages.refund.receiver'))
				  ->sortBy('refunds.user_id')
				  ->searchable(),
				  //->hideable(),
			

			Column::callback('amount','amount')
				  ->label(__('web/messages.refund.amount'))
				  ->sortBy('refunds.amount')
				  ->searchable(),
			
			

			Column::callback('id','getAction')
				  ->label(__('web/messages.action'))
				  ->sortBy('refunds.id')
				  ->searchable(),

			Column::callback('id','authorizedBy')
				  ->label(__('web/messages.refund.authorized_by'))
				  ->sortBy('refunds.id')
				  ->searchable(),

			DateColumn::raw('authorized_at')
					  ->label(__('web/messages.refund.authorized_at'))
					  ->format(config('static.datetime_format'))
					  ->sortBy(DB::raw('DATE_FORMAT(refunds.authorized_at, "%m%d%Y")')),
					  //->sortBy(DB::raw('DATE_FORMAT(if(refunds.authorized_at !=NUll,refunds.authorized_at,now()), "%m%d%Y")')),

			DateColumn::raw('created_at')
					  ->label(__('web/messages.refund.created_at'))
					  ->format(config('static.datetime_format'))
					  ->sortBy(DB::raw('DATE_FORMAT(refunds.created_at, "%m%d%Y")')),

			DateColumn::raw('effective_date')
					  ->label(__('web/messages.refund.effective_date'))
					  ->format(config('static.datetime_format'))
					  ->sortBy(DB::raw('DATE_FORMAT(refunds.effective_date, "%m%d%Y")')),

			Column::name('pay_ref_no')
				  ->label(__('web/messages.refund.pay_ref_no'))
				  ->sortBy('refunds.pay_ref_no')
				  ->searchable(),

			Column::name('status')
				  ->label(__('web/messages.refund.status'))
				  ->filterable(['pending','approve','complete','reject']),

			Column::callback(['uuid','status'],function ($uuid,$status) {
				return view('livewire.table-actions.refunds-action',['uuid' => $uuid,'status' => $status]);
			})->label(__('web/messages.operation'))*/
		];
	}

	public function authorizedBy($id)
	{
		$refund = Refund::findOrFail($id);
		return $refund->authorizedBy->name ?? '';
	}

	public function receiver($id)
	{
		$refund = Refund::findOrFail($id);
		$this->get_payload();
		if($this->payload_value == true)
		{
			return $refund->receiver->profile->name ?? '';    		
		}
		return '<a style="color:#1000ff" href="User/' . $refund->receiver->uuid . '">' . $refund->receiver->profile->name . '</a>' ?? '';
	}

	public function getAction($id)
	{
		$refund = Refund::findOrFail($id);
		$action = $refund->action;
		$this->get_payload();
		
		if (!empty($action)){
			if($this->payload_value == true)
			{
				return $action->id.','.$action->type;
			}
			return 'ID: ' . $action->id . '</br>' .  'Type: ' . $action->type;
        }
		else{
			if($this->payload_value == true)
			{
				return '';
			}
			return 'ID: --</br> Type: --';
        }

	}

	public function amount($amount)
	{
		return 'RM' . number_format($amount,2);
	}
	public function bank_name($id)
	{
		$bank = Refund::findOrFail($id);
		
		if(!empty($bank))
		{
			if($bank->receiver->profile->bankAccounts)
			{
				$bank_name =$bank->receiver->isCorporatePayer()?'':$bank->receiver->profile->bankAccounts[0]->account_no;
				return $bank_name;
				//return $bank->receiver->profile->bankAccounts[0]->bank_name??'';
				
				
			}
		}
		 
	}
	public function ic_number($id)
	{
		$bank = Refund::findOrFail($id);
		
		if(!empty($bank))
		{
			if($bank->receiver->profile->nric)
			{
				return $bank->receiver->profile->nric??'';
			}
		}
		 
	}
	public function account_number($id)
	{
		$bank = Refund::findOrFail($id);
		
		if(!empty($bank))
		{	

			if($bank->receiver->profile->bankAccounts)
			{
				$account_no = $bank->receiver->isCorporatePayer()?'':$bank->receiver->profile->bankAccounts[0]->account_no;

				return $account_no ;

				//return $bank->receiver->profile->bankAccounts[0]->account_no??'';
				
				
			}
		}
		 //return '';
	}
	public function get_payload()
    {   
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body,true);
       // return $data['updates'][0]['payload']['method']??'';
        $data_val = $data['updates'][0]['payload']['method']??'';
        if(!empty($data_val) && $data_val == 'export')
        {
            $this->payload_value=true;
        }
        

    }

}
