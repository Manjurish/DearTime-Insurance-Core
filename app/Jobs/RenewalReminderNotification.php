<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\Email;
use Illuminate\Support\Facades\App;
use App\Coverage;
use Carbon\Carbon;
use App\Helpers;
use App\Helpers\Enum;



class RenewalReminderNotification implements ShouldQueue
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
        $coverages = Coverage::where('state',Enum::COVERAGE_STATE_ACTIVE)
        ->whereRaw('DATEDIFF(DATE(next_payment_on),CURDATE()) = 2')
        ->groupBy('payer_id')
        ->get();

        //dd($coverages);
        
       if($coverages->isNotEmpty()){
        foreach ($coverages ?? [] as $coverage){
            $payer =$coverage->payer ??'';
            $owner =$coverage->owner ??'';
            $locale = $owner->user->locale;
           
            App::setLocale($locale ?? 'en');

        
    
            if($payer->profile->id == $coverage->owner_id){

            $usercoverages =Coverage::where('owner_id',$coverage->owner_id)->where('payer_id',$coverage->payer_id)->where('state','active')->whereRaw('DATEDIFF(DATE(next_payment_on),CURDATE()) = 2')->get();               
            $premimum_amount=0;
            $occ_loading =0;
            $newAge = Carbon::parse($coverage->owner->dob)->age;

            
            foreach ($usercoverages as $usercoverage){
                if($usercoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                    $price =$usercoverage->product->getPrice($usercoverage->owner,$usercoverage->real_coverage,$occ_loading,$newAge,$usercoverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $usercoverage->deductible : null,$usercoverage->owner->gender)[0];
                    
                }else{
                    $price =$usercoverage->product->getPrice($usercoverage->owner,$usercoverage->coverage)[0];
                   
                }
               
               //$price = $usercoverage->product->getPrice($payer->profile,$usercoverage->coverage);
               $annually       =   Helpers::round_up($price, 2);
               $monthly        =   Helpers::round_up($price * 0.085, 2);
               if($usercoverage->payment_term_new == 'monthly'){
                $premimum_amount += Helpers::round_up($monthly, 2);
               }else{
                $premimum_amount += Helpers::round_up($annually, 2);
               }
               
               //dd($usercoverage->product_name);
            }
                
                $thanksgiving= $owner->thanksgiving()->get();
                $premium = Helpers::calcThanksgivingDiscount($thanksgiving,$premimum_amount);
                $premium =round($premium,2);
                if($premium ==0){
                    $premium = round($premimum_amount,2);
                }
                
                $emailText =__('mobile.renewal_reminder_notif',['owner_name'=>$owner->name,'next_payment_on'=>Carbon::parse($coverage->next_payment_on)->format('d M Y'),'premium_amount'=> $premium]);
                
                $owner->user->notify(new Email($emailText, ['subject' => __('mobile.coverage_renewal')],['title' => __('mobile.coverage_renewal')]));
                
                $owner->user->sendNotification('mobile.coverage_renewal', 'mobile.renewal_reminder_inapp', [
				'translate_data' => ['owner_name'=>$owner->name,'next_payment_on'=>Carbon::parse($coverage->next_payment_on)->format('d M Y'),'premium_amount'=>$premium ],
				'buttons'   => ['ok'],
				//'auto_read' => FALSE
            ]);

            }


        }
    }

      



    }
}
