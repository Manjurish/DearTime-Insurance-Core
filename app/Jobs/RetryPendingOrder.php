<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\Helpers\Enum;
use App\Notifications\Email;
use App\Notifications\Sms;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class RetryPendingOrder implements ShouldQueue
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
            ->get()->filter(function ($item){
                if(now()->startOfDay()->diffInDays($item->next_payment_on) == 2){
                    return $item;
                }
            })->groupBy('payer_id');

        foreach ($coverages as $key=>$coverage){
            $payer = User::whereId($key)->first();
            if(!empty($payer)) {
                app()->setLocale($payer->locale ?? 'en');
                $text = __('web/profile.retry_inform_msg');
                $payer->sendNotification('notification.payment_information.title', 'web/profile.retry_inform_msg', ['command' => 'next_page', 'data' => 'payment_details']);
                $payer->notify(new Sms($text));
                $payer->notify(new Email($text));
            }
        }

        $orders = Order::where("status",Enum::ORDER_PENDING)->get();
        foreach ($orders as $order){
            $graceCoverages = $order->coverages()->where(function ($q){
                $q->where('status',Enum::COVERAGE_STATUS_GRACE_UNPAID)
                    ->orWhere('status',Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID);
            });

            if($graceCoverages->count() != 0){
                ProcessPayment::dispatch($order->id);
            }
        }
//        TestTime::addDays((Config::getValue('system_extra_day') ?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
