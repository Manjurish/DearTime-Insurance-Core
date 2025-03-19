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
use App\CoverageOrder;
use App\Order;
use Carbon\Carbon;
use App\Helpers;
use App\Helpers\Enum;


class RenewalReminderAfterNdd implements ShouldQueue
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
        try {
            //DB::enableQueryLog();
            //NOTICE AFTER REACHING NDD
            //SECOND NOTICE ON 5TH DAY
            $firstCoverages   =   Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
                ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 5 DAY) = CURDATE()")
                ->groupBy('owner_id')
                ->orderBy('id')
                ->get();

                //dd($firstCoverages);

           // dd($firstCoverages);
            //THIRD NOTICE ON 12TH DAY
            $secondCoverages   =   Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
            ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 12 DAY) = CURDATE()")
            ->groupBy('owner_id')
            ->orderBy('id')
            ->get();
            //FOURTH NOTICE ON 19TH DAY
            $thirdCoverages   =   Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
            ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 19 DAY) = CURDATE()")
            ->groupBy('owner_id')
            ->orderBy('id')
            ->get();
           //FIFTH NOTICE ON 26TH DAY
            $fourthCoverages   =   Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
            ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 26 DAY) = CURDATE()")
            ->groupBy('owner_id')
            ->orderBy('id')
            ->get();

            //SIXTH NOTICE ON 29TH DAY(COVERAGES WITH GRACE PERIOD 30 DAYS)
            
            $fifthCoverages   =   Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
            ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 29 DAY) = CURDATE()")
            ->whereRaw('YEAR(CURDATE()) - YEAR(first_payment_on) - (DATE_FORMAT(CURDATE(), "%m%d") < DATE_FORMAT(first_payment_on, "%m%d")) < ?', [2])
            ->groupBy('owner_id')
            ->orderBy('id')
            ->get();

            //echo($fifthCoverages);

        //   //SIXTH NOTICE ON 40TH DAY(COVERAGES WITH GRACE PERIOD 90 DAYS)

        //     $sixthCoverages = Coverage::whereIn('status', [Enum::COVERAGE_STATUS_GRACE_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
        //     ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 39 DAY) = CURDATE()")
        //     ->whereRaw("TIMESTAMPDIFF(YEAR,CURDATE(),DATE(first_payment_on)) >= 2 ")
        //     ->groupBy('owner_id')
        //     ->orderBy('id')
        //     ->get();

        //     //SEVENTH NOTICE ON 54TH DAY(COVERAGES WITH GRACE PERIOD 90 DAYS)

        //     $seventhCoverages = Coverage::whereIn('status', [Enum::COVERAGE_STATUS_GRACE_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
        //     ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 53 DAY) = CURDATE()")
        //     ->whereRaw("TIMESTAMPDIFF(YEAR,CURDATE(),DATE(first_payment_on)) >= 2 ")
        //     ->groupBy('owner_id')
        //     ->orderBy('id')
        //     ->get();

        //     //EIGTH NOTICE ON 68TH DAY(COVERAGES WITH GRACE PERIOD 90 DAYS)

        //     $eigthCoverages = Coverage::whereIn('status', [Enum::COVERAGE_STATUS_GRACE_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
        //     ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 67 DAY) = CURDATE()")
        //     ->whereRaw("TIMESTAMPDIFF(YEAR,CURDATE(),DATE(first_payment_on)) >= 2 ")
        //     ->groupBy('owner_id')
        //     ->orderBy('id')
        //     ->get();
          
        //     //NINETH NOTICE ON 82TH DAY(COVERAGES WITH GRACE PERIOD 90 DAYS)
        //     $ninethCoverages = Coverage::whereIn('status', [Enum::COVERAGE_STATUS_GRACE_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
        //     ->whereRaw("ADDDATE(DATE(next_payment_on), INTERVAL 67 DAY) = CURDATE()")
        //     ->whereRaw("TIMESTAMPDIFF(YEAR,CURDATE(),DATE(first_payment_on)) >= 2 ")
        //     ->groupBy('owner_id')
        //     ->orderBy('id')
        //     ->get();
           
     

            foreach ($firstCoverages ?? [] as $coverage) {    
                $this->sendNotification($coverage,7);
        
            }
            
            foreach ($secondCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage,14);
            }
            foreach ($thirdCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage,21);
            }
            foreach ($fourthCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage,28);
            }
            foreach ($fifthCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage,30);
                
            }
            return ['status' => 'success', 'data' => ['info' => 'Email sent successfully!']];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function sendNotification($coverage,$next_try)
    {

       
        $payer  =   $coverage->payer ?? '';
        $owner  =   $coverage->owner ?? '';
        $locale = $payer->locale;
        App::setLocale($locale ?? 'en');

        if (!$payer) return false;
        if (!$owner) return false;

        if ($payer->profile->id == $owner->id) { 
          

            $usercoverages =Coverage::where('owner_id',$coverage->owner_id)->where('payer_id',$coverage->payer_id)->where('state','active')->where('next_payment_on','<',Carbon::now())->get();
               
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

          //  dd($premimum_amount);
            $order_id =CoverageOrder::where('coverage_id',$coverage->id)->latest()->first()->order_id;
            $order_next_try =Order::where('id',$order_id)->latest()->first()->next_try_on;

            $thanksgiving=$owner->thanksgiving()->get();
            $premium = Helpers::calcThanksgivingDiscount($thanksgiving,$premimum_amount);
            $premium =round($premium,2);
            if($premium ==0){
                    $premium = round($premimum_amount,2);
                }

            $emailText =__('mobile.renewal_afternnd_notif',['owner_name'=>$owner->name,'next_payment_on'=>Carbon::parse($coverage->next_payment_on)->format('d M Y'),'next_try_on'=>Carbon::parse($coverage->next_payment_on)->addDays($next_try)->format('d M Y'),'premium_amount'=>$premium]);

                    
            $payer->sendNotification('mobile.coverage_renewal', 'mobile. renewal_afternnd_inapp', [
                    'translate_data' => ['owner_name'=>$owner->name,'next_payment_on'=>Carbon::parse($coverage->next_payment_on)->format('d M Y'),'next_try_on'=>Carbon::parse($coverage->next_payment_on)->addDays($next_try)->format('d M Y'),'premium_amount'=> $premium],
                    'buttons'   => ['ok'],
                    //'auto_read' => FALSE
                ]);        

            $payer->notify(new \App\Notifications\Email($emailText,  ['subject' => __('mobile.coverage_renewal')],['title' => __('mobile.coverage_renewal')]));
    }
    }
}
