<?php     

namespace App\Jobs;

use App\Config;
use App\Helpers\Enum;
use App\Notifications\Email;
use App\Notifications\Sms;
use App\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class RetryFailedCoverage implements ShouldQueue
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
     * Deprecated
     * @return void
     */
    public function handle()
    {
        TestTime::addDays(Config::getValue('system_extra_day') ?? 0)->addHours(Config::getValue('system_extra_hour') ?? 0);

        //check for failed orders
        $orders = Order::where("status",'!=',"Paid")->where("retries","<","5")->get();
        foreach ($orders as $order) {
            //check if coverage not active
            if(($order->coverages()->first()->status ?? null) != 'Unpaid')
                continue;
            $day = abs($order->created_at->diffInDays(Carbon::now()));
            if(($order->retries == '1' && $day == '7') || ($order->retries == '2' && $day == '14') || ($order->retries == '3' && $day == '21') || ($order->retries == '4' && $day == '28')){
                ProcessPayment::dispatch($order->id);
            }
            if(($order->retries == '1' && $day == '5') || ($order->retries == '2' && $day == '12') || ($order->retries == '3' && $day == '19') || ($order->retries == '4' && $day == '26')){
                //send notification
                $payer = $order->payer;
                if(!empty($payer)) {
                    app()->setLocale($payer->locale ?? 'en');
                    $text = __('web/profile.retry_inform_msg');
                    $payer->sendNotification(__('notification.payment_information.title'), $text, ['command' => 'next_page', 'data' => 'payment_details']);
                    $payer->notify(new Sms($text));
                    $payer->notify(new Email($text));
                }
            }

        }

        TestTime::addDays((Config::getValue('system_extra_day')*(-1)) ?? 0)->addHours((Config::getValue('system_extra_hour')*(-1)) ?? 0);
    }
}
