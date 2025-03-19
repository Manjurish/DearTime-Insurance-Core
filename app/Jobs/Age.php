<?php     

namespace App\Jobs;

use App\Config;
use App\Coverage;
use App\Individual;
use App\Notifications\Email;
use App\Notifications\Sms;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class Age implements ShouldQueue
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

        $new_restricted_ages = Individual::whereDate("dob","<=",now()->startOfDay()->subYears(65))->where("in_restricted_age","0")->get();
        foreach ($new_restricted_ages as $new_restricted_age) {
            $user = $new_restricted_age->user;
            if(empty($u))
                return;
            app()->setLocale($user->locale ?? 'en');
            $text = __('web/profile.age_above_msg');

            $user->sendNotification('notification.new_message.title', 'web/profile.age_above_msg', ['command' => 'next_page', 'data' => 'policies_page']);
            $user->notify(new Sms($text));
            $user->notify(new Email($text));

            $new_restricted_age->in_restricted_age = 1;
            $new_restricted_age->save();
        }
        $new_released_ages = Individual::whereDate("dob","<=",now()->startOfDay()->subYears(16))->where("in_restricted_age","1")->get();
        foreach ($new_released_ages as $new_released_age) {
            $user = $new_released_age->user;
            if(empty($u))
                return;
            app()->setLocale($user->locale ?? 'en');
            $text = __('web/profile.age_below_msg');

            $user->sendNotification('notification.new_message.title','web/profile.age_below_msg', ['command' => 'next_page', 'data' => 'policies_page']);
            $user->notify(new Sms($text));
            $user->notify(new Email($text));

            $new_released_age->in_restricted_age = 0;
            $new_released_age->save();
        }
//        TestTime::addDays((Config::getValue('system_extra_day')?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }
}
