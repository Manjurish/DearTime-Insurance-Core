<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\CoverageOrder;
use App\Events\ChangedCoveragesStatusEvent;
use App\Helpers\Enum;
use App\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class FulfilledCoverage implements ShouldQueue
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
        $coverages = Coverage::where('state',Enum::COVERAGE_STATE_ACTIVE)
            ->whereDate('next_payment_on','=',Carbon::now()->startOfDay());

        Coverage::changeCoveragesToInactive($coverages->get());

        $orders = CoverageOrder::whereIn('coverage_id',$coverages->pluck('id')->toArray())->pluck('order_id')->toArray();
        $orders = array_unique($orders);
        $orders = Order::whereIn('id',$orders)->get();
        foreach ($orders as $order){
            event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_FULFILLED));
        }

//        TestTime::addDays((Config::getValue('system_extra_day')?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
