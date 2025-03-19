<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\Helpers\Enum;
use App\Product;
use Illuminate\Bus\Queueable;
use Spatie\TestTime\TestTime;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class RecalculateCoverage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($status=null)
    {
        $this->status = $status ?? [Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        TestTime::addDays(Config::getValue('system_extra_day') ?? 0)->addHours(Config::getValue('system_extra_hour') ?? 0);

        $coverages = Coverage::whereIn('status',$this->status)->get();

        foreach($coverages as $coverage){
            $deathCoverage = Coverage::where('payer_id',$coverage->payer_id)
                ->where('covered_id',$coverage->covered_id)
                ->where('owner_id',$coverage->owner_id)
                ->where('state',Enum::COVERAGE_STATE_ACTIVE)
                ->where('product_name',Enum::PRODUCT_NAME_DEATH)
                ->sum('coverage');

            $option = $coverage->product->quickQuoteFor($coverage->covered,$coverage->product_name == Enum::PRODUCT_NAME_MEDICAL?$coverage->real_coverage:$coverage->coverage,$deathCoverage,$coverage->payer,$coverage->deductible);

            if($option['allowed']){
                $coverage->update(
                    [
                        'payment_monthly' =>$option['monthly'],
                        'payment_annually'=>$option['annually']
                    ]
                );
            }
            else{
                $coverage->update(
                    [
                        'state' =>Enum::COVERAGE_STATE_INACTIVE,
                        'status'=>Enum::COVERAGE_STATUS_TERMINATE
                    ]
                );
            }

        }

//        TestTime::addDays((Config::getValue('system_extra_day') ?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
