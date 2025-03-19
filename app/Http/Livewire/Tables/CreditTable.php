<?php     

namespace App\Http\Livewire\Tables;

use App\Credit;
use App\Helpers\Enum;
use App\Thanksgiving;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Request;

class CreditTable extends LivewireDatatable
{
    public $exportable = true;

    public $user;
    public $promote=false;
    public $payload_value=false;
    public $currentUrl;
    
    protected $listeners = [
        'tableRefresh' => '$refresh'
    ];
    
    public function builder()
    {
        //echo Request::url();die;
        if(Request::is('internal/credits/', '*'))
        {
            $creditQuery = Credit::query()
            ->Join('orders', 'orders.id', 'credits.order_id')
            ->Join('users AS A', 'A.id', 'credits.from_id')
            //->leftJoin('users AS B', 'B.id', 'credits.user_id')
            // ->leftJoin('users AS B', 'A.id', 'credits.from_id')
            ->Join('individuals As Ind1', 'Ind1.user_id', 'A.id')
            //->leftJoin('individuals As Ind2', 'Ind2.user_id', 'B.id')
            ->whereNull('A.deleted_at')
            ->whereNotNull('A.password')
            ->whereNotNull('Ind1.user_id')
            ->where('Ind1.type', 'owner')
            
            ->where('credits.type_item_id', '<>', 0)
            ->whereNotNull('credits.type_item_id');
    
    
        }else
        {
            $creditQuery = Credit::query()->where('type_item_id', '<>', 0)
                        ->whereNotNull('type_item_id');
        }
        
        
        if(!empty($this->user)) {
            if (!empty($this->promote)) {
                $creditQuery = $creditQuery
                    ->where('user_id', $this->user->id)
                    ->whereHasMorph('typeable', [Thanksgiving::class], function (Builder $query) {
                        $query->where('type', Enum::THANKSGIVING_TYPE_PROMOTER);
                    });
            } else {
                $creditQuery = $creditQuery->where('from_id', $this->user->id);

            }
        }
        return $creditQuery;
    }

    public function columns()
    {
        if(!empty($this->user)){
            if($this->promote){
                return[
                    Column::name('ref_no')
                        ->label(__('web/messages.credit.ref_no'))
                        ->sortBy('credits.ref_no')
                        ->searchable(),
                    Column::name('order.ref_no')
                        ->label(__('web/messages.order_ref'))
                        ->sortBy('orders.ref_no')
                        ->searchable(),
                    Column::callback('id','payBy')
                        ->label(__('web/messages.credit.from'))
                        ->sortBy('credits.from_id')
                        ->searchable(),
                    Column::callback(['amount'],function ($amount){
                        return 'RM'.number_format($amount,2);
                    })->label(__('web/messages.amount'))
                        ->sortBy('credits.amount')
                        ->searchable(),
                   /* DateColumn::callback(['created_at'],function ($created_at){
                        return Carbon::parse($created_at)->format(config('static.datetime_format'));
                    })->label(__('web/messages.credit.date'))*/
                    DateColumn::name('created_at')
                    ->label(__('web/messages.credit.date'))
                    ->sortBy('credits.created_at')
                    ->searchable(),
                ];
            }
            else{
                return[
                    Column::name('ref_no')
                    ->label(__('web/messages.credit.ref_no'))
                    ->sortBy('credits.ref_no')
                    ->searchable(),
                Column::name('order.ref_no')
                    ->label(__('web/messages.order_ref'))
                    ->sortBy('orders.ref_no')
                     ->searchable(),
                    Column::callback('id','typeable')
                        ->label(__('web/messages.type'))
                        ->sortBy('credits.type_item_id')
                        ->searchable(),
                    Column::callback(['amount'],function ($amount){
                        return 'RM'.number_format($amount,2);
                    })->label(__('web/messages.amount'))
                        ->sortBy('credits.amount')
                        ->searchable(),
                    /*DateColumn::callback(['created_at'],function ($created_at){
                        return Carbon::parse($created_at)->format(config('static.datetime_format'));
                    })->label(__('web/messages.credit.date'))*/
                    DateColumn::name('created_at')
                    ->label(__('web/messages.credit.date'))
                    ->sortBy('credits.created_at')
                    ->searchable(),
                ];
            }
        }
        else{
           
            return[
                
                Column::name('credits.ref_no')
                ->label(__('web/messages.credit.ref_no'))
                ->sortBy('credits.ref_no')
                ->searchable(),

                Column::name('orders.ref_no')
                ->label(__('web/messages.order_ref'))
                ->sortBy('orders.ref_no')
                ->searchable(),

                /*Column::name('Ind1.name')
                ->label(__('web/messages.credit.from'))
                ->sortBy('Ind1.name')
                ->searchable(),*/

                Column::callback(['Ind1.name','A.uuid'],function ($name,$uuid){

                    $this->get_payload();
                    if($this->payload_value == true)
        
                    {
                        return $name ?? '';    
                    }
                    return '<a style="color:#1000ff" href="User/'.$uuid.'">'.$name.'</a>' ?? '';
                   // return 'RM'.number_format($amount,2);
                })->label(__('web/messages.credit.from'))
                  ->sortBy('Ind1.name')
                  ->searchable(),

                /*Column::name('Ind2.name')
                ->label(__('web/messages.credit.to'))
                ->sortBy('Ind2.name')
                ->searchable(),*/

                Column::callback(['credits.amount'],function ($amount){
                    return 'RM'.number_format($amount,2);
                })->label(__('web/messages.amount'))
                    ->sortBy('credits.amount')
                ->searchable(),
                Column::callback('id','typeable')
                ->label(__('web/messages.type'))
                ->filterable(['self'=>'self','charity'=>'DearTime - Charity Fund','promoter'=>'promoter'],'filterType')
                ->sortBy('credits.type_item_id')
                ->searchable(),
                DateColumn::name('credits.created_at')
                ->label(__('web/messages.credit.date'))
                ->sortBy('credits.created_at')
                ->searchable(),
                /*Column::name('ref_no')
                    ->label(__('web/messages.credit.ref_no'))
                    ->sortBy('credits.ref_no')
                    ->searchable(),
                Column::name('order.ref_no')
                    ->label(__('web/messages.order_ref'))
                    ->sortBy('orders.ref_no')
                    ->searchable(),
                Column::callback('id','payBy')
                    ->label(__('web/messages.credit.from'))
                    ->sortBy('credits.from_id')
                    ->searchable(),
                Column::callback('id','getBy')
                    ->label(__('web/messages.credit.to'))
                    ->sortBy('credits.user_id')
                    ->searchable(),
                Column::callback(['amount'],function ($amount){
                    return 'RM'.number_format($amount,2);
                })->label(__('web/messages.amount'))
                    ->sortBy('credits.amount')
                    ->searchable(),

                Column::callback('id','typeable')
                    ->label(__('web/messages.type'))
                    ->filterable(['self'=>'self','charity'=>'Charity Insurance','promoter'=>'promoter'],'filterType')
                    ->sortBy('credits.type_item_id')
                    ->searchable(),

               /* DateColumn::callback(['created_at'],function ($created_at){
                    return Carbon::parse($created_at)->format(config('static.datetime_format'));
                })->label(__('web/messages.credit.date'))*/

                  /*  DateColumn::name('created_at')
                    ->label(__('web/messages.credit.date'))
                    ->sortBy('credits.created_at')
                    ->searchable(),*/
            ];
        }
    }

    public function typeable($id){
             
        $credit = Credit::findOrFail($id);
        return $credit->typeableWithTrashed->type ?? '';
    }

    public function payBy($id){
        $credit = Credit::findOrFail($id);
        if($credit->fromUser && $credit->fromUser->profile)
            $this->get_payload();
            if($this->payload_value == true)
            
            {
                return $credit->fromUser->profile->name ?? '';    
            }
            return '<a style="color:#1000ff" href="User/'.$credit->fromUser->uuid.'">'.$credit->fromUser->profile->name.'</a>' ?? '';
        
    }

    public function getBy($id){
        $credit = Credit::findOrFail($id);
        if($credit->toUser)
            return $credit->toUser->profile->name ?? '';
        return '';
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
        //return $data['updates'][0]['payload']['method']??'';
        //dd($data_val);
        //$payLoad = json_decode($request->getContent(), true);
        //return $payLoad['updates'][0]['payload']['method']??''
        //dd($payLoad['updates']['payload']);
       //echo "<pre>";print_r($payLoad['updates'][0]['payload']['method']);

    }
    
    
}
