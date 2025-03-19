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


class ReferralPaymentMonth implements ShouldQueue
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

        $referrals = Referral::whereIn('payment_status',['PENDING','ON HOLD'])->get();
        if($referrals->isNotEmpty()){
    foreach($referrals as $referral){
          if($referral->month == 'January'){
             $referral->month_bm ='Januari';
             $referral->month_ch ='一月';
             $referral->save();
            }

          if($referral->month == 'February'){
             $referral->month_bm ='Februari';
             $referral->month_ch ='二月';
             $referral->save();
            }

          if($referral->month == 'March'){
             $referral->month_bm ='Mac';
             $referral->month_ch ='三月';
             $referral->save();
            }

          if($referral->month == 'April'){
             $referral->month_bm ='April';
             $referral->month_ch ='四月';
             $referral->save();
            }

          if($referral->month == 'May'){
             $referral->month_bm ='Mei';
             $referral->month_ch ='五月 ';
             $referral->save();
            }

          if($referral->month == 'June'){
             $referral->month_bm ='Jun';
             $referral->month_ch ='六月';
             $referral->save();
          }

          if($referral->month == 'July'){
             $referral->month_bm ='Julai';
             $referral->month_ch ='七月';
             $referral->save();
          }

          if($referral->month == 'August'){
             $referral->month_bm ='Ogos';
             $referral->month_ch ='八月';
             $referral->save();
            }

          if($referral->month == 'September'){
             $referral->month_bm ='September';
             $referral->month_ch ='九月'; 
             $referral->save();
          } 

          if($referral->month == 'October'){
             $referral->month_bm ='Oktober';
             $referral->month_ch ='十月 ';
             $referral->save();
            }

          if($referral->month == 'November'){
             $referral->month_bm ='November';
             $referral->month_ch ='十一月 ';
             $referral->save();
          }

          if($referral->month == 'December'){
             $referral->month_bm ='Disember';
             $referral->month_ch ='十二月';            
             $referral->save();
            }
          }
      }
    }
}
