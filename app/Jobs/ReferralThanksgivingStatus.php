<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Notifications\Email;
use App\Notifications\Sms;
use Illuminate\Support\Facades\Notification;
use App\Referral;


class ReferralThanksgivingStatus implements ShouldQueue
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

        $referrals = Referral::where('thanksgiving_percentage','0')->get();
        if($referrals->isNotEmpty()){
          foreach($referrals as $referral){
                  $referral->payment_status ='NO ACTION NEEDED';
                  $referral->save();
                  }
              }
          }
}
