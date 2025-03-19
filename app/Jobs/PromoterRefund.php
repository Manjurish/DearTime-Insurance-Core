<?php     

namespace App\Jobs;

use App\Action;
use App\Config;
use App\Credit;
use App\Helpers\Enum;
use App\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class PromoterRefund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        TestTime::addDays(Config::getValue('system_extra_day') ?? 0)->addHours(Config::getValue('system_extra_hour') ?? 0);

        $credits = Credit::whereHas('thanksgiving',function ($q){
            $q->where('type',Enum::THANKSGIVING_TYPE_PROMOTER)->withTrashed();
        })
            ->whereBetween('created_at',[now()->startOfDay()->addDays(-45),now()->startOfDay()->addDays(-15)])
            ->get()
            ->groupBy('user_id');

        foreach ($credits as $key=>$userCredits){
            $actions= [];
            $refund=Refund::create([
                'payer'=>Enum::REFUND_PAYER_DEARTIME,
                'user_id'=>$key,
                'bank_account_id'=>$userCredits->first()->toUser->profile->bankAccounts()->latest()->first()->id,
                'amount'=>$userCredits->sum('amount'),
                'status'=>Enum::REFUND_STATUS_PENDING,
            ]);

            $actions['refund_id'] = $refund->id;
            $actions['credits'] =[];
            foreach ($userCredits as $userCredit){
                array_push($actions['credits'],[
                    'credit_id'=>$userCredit->id,
                    'from_id'=>$userCredit->from_id,
                    'amount'=>$userCredit->amount,
                ]);
            }

            Action::create([
                'user_id'=>$key,
                'type'=>Enum::ACTION_TYPE_PROMOTER_REFUND,
                'event'=>Enum::ACTION_EVENT_CREATE_REFUND,
                'actions'=>$actions,
                'execute_on'=>now(),
                'createdbyable_type'=>null,
                'createdbyable_id'=>null,
            ]);

        }

//        TestTime::addDays((Config::getValue('system_extra_day') ?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
