<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\Events\ChangedCoveragesStatusEvent;
use App\Helpers\Enum;
use App\Notifications\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class CancelPendingCoverage implements ShouldQueue
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
        //check for active coverages
        $coverages = Coverage::where(function ($q){
            $q->where("status",Enum::COVERAGE_STATUS_UNPAID)->orWhere('status',Enum::COVERAGE_STATUS_INCREASE_UNPAID);
        })->where("created_at","<",now()->startOfDay()->subDays(7))->get();
        //to notify
        $coverages = $coverages->groupBy('payer_id')->map(function ($q) {
            return $q->take(1);
        });

        if(!empty($coverages)){
            foreach ($coverages as $groupCoverages){
                event(new ChangedCoveragesStatusEvent($groupCoverages,null,Enum::COVERAGE_STATUS_PENDING));
            }
        }


        $coverages = Coverage::where(function ($q){
            $q->where("status",Enum::COVERAGE_STATUS_UNPAID)->orWhere('status',Enum::COVERAGE_STATUS_INCREASE_UNPAID);
        })->where("created_at",">",now()->startOfDay()->subDays(8))->where("created_at","<=",now()->startOfDay()->subDays(7))->get();
        //to cancel
        if(!empty($coverages)){
            foreach ($coverages as $coverage) {
                $coverage->status = Enum::COVERAGE_STATUS_EXPIRED;
                $coverage->save();
            }
            event(new ChangedCoveragesStatusEvent($coverages,null,Enum::COVERAGE_STATUS_EXPIRED));
        }


//        TestTime::addDays((Config::getValue('system_extra_day')?? 0*(-1)))->addHours((Config::getValue('system_extra_hour')?? 0*(-1)));
    }
}
