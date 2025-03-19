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
use App\User;
use App\Order;
use App\Transaction;
use App\CoverageOrder;
use App\Coverage;
use App\SpoCharityFunds;
use App\Individual;
use App\SpoCharityFundApplication;
use App\Helpers\Enum;
use Carbon\Carbon;

class MedicalsurveyExpiry implements ShouldQueue
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
        $applications =SpoCharityFundApplication::whereIn('status',['SUBMITTED','QUEUE'])->orderby('submitted_on','asc')->get();
        foreach($applications as $application){
            if(now()->startOfDay()->diffInDays($application->form_expiry) == 0){
                //Carbon::today()->addDays(7)
                $application->status ='EXPIRED';
                $application->active =0;
                $application->save();
                $spo_coverages =Coverage::where('payer_id',$application->user_id)->where('sponsored',1)->where('status','unpaid')->get();
                if($spo_coverages->isNotEmpty()){
                    foreach($spo_coverages as $spo_coverage){
                        $spo_coverage->status ='Expired';
                        $spo_coverage->save();
                    }
                }
                $applicantuser =User::where('id',$application->user_id)->first();
                $applicantuser->sendNotification('Attention', 'mobile.medical_survey_expiry', [
                    'translate_data' => ['name' =>$applicantuser->profile->name],
                    'buttons' => [
                        ['title' => 'ok'],
                    ],

                ]);
                

            }

            if(now()->startOfDay()->diffInMonths($application->form_expiry) == 1){
                $applicantuser =User::where('id',$application->user_id)->first();
                if($application->reminder !=1){
                $applicantuser->sendNotification('Attention', 'mobile.spo_medicalsurvey_reminder', [
                    'translate_data' => ['expirydate' =>Carbon::parse($application->form_expiry)->format('d/m/Y')],
                    'buttons' => [
                        ['title' => 'ok'],
                    ],

                ]);
            }

                $application->reminder =1;
                $application->save();
            }


            //deactivating or terminating if medical survey not updated after grace period

            // $applications_expired =SpoCharityFundApplication::where('status','Expired')->get();
            // foreach($applications_expired as $application_expired){
            //     //$now=Carbon::parse($application_expired->form_expiry)->addDays(30);
            //     $grace_period=Carbon::parse($application_expired->form_expiry)->addDays(30);
            //     // $application_expired->renewed_at =$grace_period;
            //     // $application_expired->save();
            //     if(now()->diffInDays($grace_period) == 0){
            //         //$application_expired->status ='REJECTED';
            //         $application_expired->active=0;
            //         $application_expired->save();
                    

            //     }


            // }

           // $grace_period =Carbon::now()->addMonths(1);

     
        }

        $this->info('Successfully Checked.');
    }
}
