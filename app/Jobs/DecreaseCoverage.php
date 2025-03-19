<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\Helpers\Enum;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class DecreaseCoverage implements ShouldQueue
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

        $coverages = Coverage::where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->whereDate('next_payment_on',Carbon::today()->startOfDay())->orderBy('created_at','desc')->get();
        foreach ($coverages as $coverage) {
            $active_coverage = Coverage::
                  where("owner_id",$coverage->owner_id)
                ->where("payer_id",$coverage->payer_id)
                ->where("covered_id",$coverage->covered_id)
                ->where("product_id",$coverage->product_id)
                ->where("state",Enum::COVERAGE_STATE_ACTIVE)->get();

            Coverage::changeCoveragesToInactive($active_coverage);
            $order = $coverage->orders()->first();
            ProcessPayment::dispatch($order->id);

//            if(empty($active_coverage)){
//                $coverage->status = Enum::COVERAGE_STATUS_CANCELLED;
//                $coverage->save();
//            }else{
//                $next_payment_on = Carbon::parse($coverage->next_payment_on);
//                if($next_payment_on->lessThanOrEqualTo(now())){
//
//                    $active_coverage->status = Enum::COVERAGE_STATUS_CANCELLED;
//                    $coverage->status = Enum::COVERAGE_STATUS_ACTIVE;
//
//                    $active_coverage->save();
//                    $coverage->save();
//
//                }
//            }
        }
//        TestTime::addDays((Config::getValue('system_extra_day')?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
