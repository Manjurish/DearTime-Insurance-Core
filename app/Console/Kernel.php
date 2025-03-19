<?php

namespace App\Console;

use App\Config;
use App\Jobs\Age;
use App\Jobs\CancelPendingCoverage;
use App\Jobs\DecreaseCoverage;
use App\Jobs\FulfilledCoverage;
use App\Jobs\NotificationSchedule;
use App\Jobs\PromoterRefund;
use App\Jobs\RecalculateCoverage;
use App\Jobs\RenewCoverage;
use App\Jobs\RetryFailedCoverage;
use App\Jobs\RetryPendingOrder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\TestTime\TestTime;
use App\Jobs\PayerOwnerAgreementNotifications;
use App\Jobs\PayerOwnerAgreementNoResponseNotifications;
use App\Jobs\MedicalsurveyExpiry;
use App\Jobs\FundAllocation;
use App\Jobs\ReferralPaymentStatus;
use App\Jobs\ReferralThanksgivingStatus;
use App\Jobs\ReferralPaymentMonth;
use App\Jobs\RenewalReminderNotification;
use App\Jobs\RenewalReminderAfterNdd;
use App\Jobs\RetryRenewalPendingOrder;
use App\Jobs\ArecaAutoUpload;
use App\Jobs\SarawakAutoUpload;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        TestTime::addDays(Config::getValue('system_extra_day') ?? 0)->addHours(Config::getValue('system_extra_hour') ?? 0);
        //         $schedule->command('clinic:update')     ->weeklyOn(0);
        //         $schedule->command('db:backup')         ->weekly();
        //         $schedule->job(DecreaseCoverage::class)      ->dailyAt("12:00");
        //         $schedule->job(RenewCoverage::class)         ->dailyAt("12:00");
        //         $schedule->job(RetryPendingOrder::class)     ->dailyAt("12:00");
        // //        $schedule->job(RetryFailedCoverage::class)   ->dailyAt("12:00");
        //         $schedule->job(Age::class)                   ->dailyAt("12:00");
        //         $schedule->job(NotificationSchedule::class)  ->dailyAt("12:00");
        //         $schedule->job(CancelPendingCoverage::class) ->hourly();
        //         $schedule->job(FulfilledCoverage::class)     ->dailyAt("12:00");
        //         $schedule->job(PromoterRefund::class)        ->monthlyOn();
        //         $schedule->job(RecalculateCoverage::class)    ->dailyAt("12:00");


        $schedule->job(new RenewCoverage)->everyFiveMinutes();
        $schedule->job(new RenewalReminderAfterNdd)->dailyAt('15:00');
        $schedule->job(new RenewalReminderNotification)->dailyAt('10:30');
        $schedule->job(new RetryRenewalPendingOrder)->everyFiveMinutes();
        $schedule->job(new PayerOwnerAgreementNotifications)->dailyAt('03:15');
        //$schedule->job(new PayerOwnerAgreementNoResponseNotifications)->dailyAt('03:30');
        $schedule->job(new FundAllocation)->hourly();
        $schedule->job(new MedicalsurveyExpiry)->dailyAt('12:30');
        $schedule->job(new ReferralPaymentStatus)->dailyAt('06:30');
        $schedule->job(new ReferralThanksgivingStatus)->dailyAt('07:00');
        $schedule->job(new ReferralPaymentMonth)->dailyAt('07:30');
        $schedule->job(new ArecaAutoUpload)->hourly();
        $schedule->job(new SarawakAutoUpload)->hourly();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
