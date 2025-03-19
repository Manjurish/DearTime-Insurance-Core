<?php     

namespace App\Jobs;


use App\Notifications\Email;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Jobs\ProcessPayment;
use App\Config;
use App\Coverage;
use App\CoverageOrder;
use App\Credit;
use App\Helpers;
use App\Helpers\Enum;
use App\Transaction;
use App\Individual;
use App\Underwriting;
use App\User;
use App\Order;
use App\Product;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\TestTime\TestTime;

class RenewCoverage implements ShouldQueue
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
        //check for active coverages
        $coverages = Coverage::whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])->whereNull('sponsored')                
                   ->get()->filter(function ($item){
                    if(now()->startOfDay()->diffInDays($item->next_payment_on) == 0 && $item->next_payment_on!=NULL  ){
                        $decrease_check =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
                    if($decrease_check && $decrease_check->next_payment_on!=NULL){
                        $decrease_activecov = Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                    }else{
                        $decrease_activecov =[];
                    }
                    $med_cov =[];
                    if($item->product_id==5){
                        $med_increase =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                        if($med_increase){
                            $med_cov =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                            $med_cov =array_diff($med_cov,[$med_increase->id]);
                        }else{
                            $med_cov =[];
                        }
                    }
                  
                    if(!in_array($item->id,$med_cov)){
                     if(!in_array($item->id,$decrease_activecov)){
                        return $item;
                    }
                    }
                    
                    }
                })->pluck('id')->toArray();
        
              
        $coverages_orders = CoverageOrder::whereIn("coverage_id",$coverages)->get()->pluck('order_id')->toArray();
        $orders = Order::whereIn("id",$coverages_orders)->get()->filter(function ($item){
            foreach($item->coverages as $cov){
             if($cov->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                 if($item->status == Enum::ORDER_PENDING){
                     return $item;
                 }
             }else{
                 if($item->status == Enum::ORDER_SUCCESSFUL){
                     return $item;
                 }
             }
            }
         });

        

        foreach ($orders as $order){
          try{
            if($order->coverages()->first()->owner_id == $order->payer->profile->id){
            $payer_card = $order->payer->profile->bankCards;
            $auto_debit = false;
            $recent_order =Order::where('parent_id',$order->id)->latest()->first();
            if($recent_order){
                    $covorder =CoverageOrder::where("order_id",$recent_order->id)->get()->pluck('coverage_id')->toArray();
            }else{
                    $covorder=[];
                }
            if(($payer_card)->isNotEmpty()){
         
                $auto_debit = $payer_card->first()->auto_debit == Enum::CARD_AUTO_DEBIT_ACTIVE;
            }
            
                // $graceCoverages = Coverage::where(function ($q){
                //     $q->where('status',Enum::COVERAGE_STATUS_GRACE_UNPAID)
                //         ->orWhere('status',Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID);
                // })
                //     ->orderBy('id','asc')
                //     ->where('payer_id',$order->payer_id);
                $graceCoverages = Coverage::whereIn('id',$covorder)->whereIn('status',[Enum::COVERAGE_STATUS_GRACE_UNPAID,Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID])->orderBy('id','asc')->where('payer_id',$order->payer_id)->get();


                if($graceCoverages->count()==0){

                    $new_order = new Order();
                    $new_order->amount = $order->amount;
                    $new_order->true_amount = $order->true_amount;
                    $new_order->status = Enum::ORDER_PENDING;
                    $new_order->payer_id = $order->payer_id;
                    $new_order->type = Enum::ORDER_TYPE_RENEW;
                    // todo need check and change (30 or 90)
                    if($order->coverages()->first()->payment_term_new == 'monthly'){
                        $new_order->grace_period = 30;
                    }else{
                        $new_order->grace_period = (now()->startOfDay()->diffInMonths($order->coverages()->first()->first_payment_on) >= 23 )?  90: 30;

                    }                    
                    $new_order->due_date = $order->coverages()->first()->next_payment_on;
                    $new_order->last_try_on = now();
                    $new_order->next_try_on = Carbon::today()->addDays(7);
                    //$new_order->retries = (now()->startOfDay()->diffInMonths($order->coverages()->first()->first_payment_on) >= 23 )?  11: 6;
                   $retries_check=(now()->startOfDay()->diffInMonths($order->coverages()->first()->first_payment_on));
                   $retries_term=$order->coverages()->first()->payment_term_new;
                   if( $retries_check>23 && $retries_term=='monthly'){
                       $new_order->retries=6;
                   }elseif($retries_check>23 && $retries_term=='annually'){
                       $new_order->retries=10;
                   }else{
                       $new_order->retries=6;
                   }
                    $new_order->parent_id = $order->id;
                    $new_order->save();
                    $payableOrder = $new_order;
                    $total = 0;
                    $occ_loading = 0;

                    // when some coverage recently paid and don't need calculate at own-order (for example deacrease coverage)
                    $allCoveragesWithoutReduced = $order->coverages()->whereIn('status',[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])->get()->filter(function ($item){
                        if(now()->startOfDay()->diffInDays($item->next_payment_on) == 0 && $item->next_payment_on!=NULL ){
                            $decrease_check =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
                        if($decrease_check && $decrease_check->next_payment_on!=NULL){
                            $decrease_activecov = Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                        }else{
                            $decrease_activecov =[];
                        }
                        $med_cov =[];
                        if($item->product_id==5){
                            $med_increase =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                            if($med_increase){
                                $med_cov =Coverage::where('owner_id',$item->owner_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                                $med_cov =array_diff($med_cov,[$med_increase->id]);
                            }else{
                                $med_cov =[];
                            }
                        }
                      
                        if(!in_array($item->id,$med_cov)){
                         if(!in_array($item->id,$decrease_activecov)){
                            return $item;
                        }
                        }
                        
                        }
                    });
                    foreach ($allCoveragesWithoutReduced as $coverage){
                     
                        $new_coverage = $coverage->replicate();
                       
                        $newAge = Carbon::parse($coverage->owner->dob)->age;
                        if($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                            $occ_loading =$coverage->owner->occupationJob->Medical;
                        }elseif($coverage->product_name == Enum::PRODUCT_NAME_DEATH){
                            $occ_loading =$coverage->owner->occupationJob->death;
                        }elseif($coverage->product_name == Enum::PRODUCT_NAME_DISABILITY){
                            $occ_loading =$coverage->owner->occupationJob->TPD;
                        }elseif($coverage->product_name == Enum::PRODUCT_NAME_ACCIDENT){
                            $occ_loading =$coverage->owner->occupationJob->Accident;

                        }



                        if($new_coverage->renewal_date ==null || $new_coverage->renewal_date==''){
                         
                            $today = Carbon::now();
                            $diff =date_diff(date_create($coverage->first_payment_on), date_create($today));
                            if($diff->format('%m') ==0 ){
                              $renewal_date  =Carbon::parse($coverage->first_payment_on)->addYear($diff->format('%y'));
                              $new_coverage->renewal_date =$renewal_date;
                            }else{
                              $renewal_date =Carbon::parse($coverage->first_payment_on)->addYear($diff->format('%y')+1);
                              $new_coverage->renewal_date =$renewal_date;

                            }

                           
                        }else{
                          $renewal_date =Carbon::parse($new_coverage->renewal_date);
                          $new_coverage->renewal_date =$renewal_date;

                        }
                        
                        
                        $diff = date_diff(date_create(now()),date_create(Carbon::parse($renewal_date)));
                        $diff_days =  $diff->invert ?  - $diff->format('%a') : $diff->format('%a');
                        if($coverage->payment_term_new =='monthly' && $diff_days  > 0){
                          
                            $last_renew = Carbon::parse($coverage->renewal_date)->subYear();
                            $last_renew_format = date('Y-m-d', strtotime($last_renew));

                            $newAge =Carbon::parse($coverage->owner->dob)->diffInYears($last_renew_format);
                        }

                        // calculate increased medical coverages premium
                        if ($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                            $latestuw = Coverage::where('owner_id',$coverage->owner_id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
                            $underwriting=Underwriting::where('id',$latestuw)->first();
                            $newIncreasePrem = $coverage->product->getPrice($coverage->owner,$coverage->real_coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting,$coverage->owner->gender)[0];

                            // because first create grace-unpaid then grace-increase-unpaid
                            $oldGrace = Coverage::where('owner_id',$coverage->owner_id)
                                ->where('payer_id',$coverage->payer_id)
                                ->where('covered_id',$coverage->covered_id)
                                ->where('product_name',$coverage->product_name)->where(function ($q){
                                    $q->where('status',Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID)
                                        ->orWhere('status',Enum::COVERAGE_STATUS_GRACE_UNPAID)
                                        ->orWhere('status',Enum::COVERAGE_STATUS_ACTIVE)
                                        ->orWhere('status',Enum::COVERAGE_STATUS_ACTIVE_INCREASED);
                                });

                            $payment_annually = $newIncreasePrem;
                           

                            $new_coverage->payment_annually = round($payment_annually,2);
                            $new_coverage->full_premium = $coverage->payment_term_new =='monthly'?round($coverage->product->covertAnnuallyToMonthly($payment_annually),2):round($payment_annually,2);
                            $without_loading =$coverage->product->getPrice($coverage->owner,$coverage->real_coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting,$coverage->owner->gender)[3];
                            if($coverage->payment_term_new =='monthly'){
                                $new_coverage->payment_without_loading = round($coverage->product->covertAnnuallyToMonthly($without_loading),2);
                                $new_coverage->full_premium = round($coverage->product->covertAnnuallyToMonthly($payment_annually),2);

                            }else{
                                $new_coverage->payment_without_loading = round($without_loading,2);
                                $new_coverage->full_premium = round($payment_annually,2);
                            }

                            if($coverage->payment_term != $coverage->payment_term_new ){
                                if($coverage->payment_term_new == 'annually'){
                                    $today = Carbon::now();
                                        // $diff_month =date_diff(date_create($today), date_create($coverage->first_payment_on));
                                        // $balance_month = 12 - $diff_month->format('%m');
                                        // //dd($diff_month->format('%m'));
                                        // $new_coverage->payment_annually = round($payment_annually*($balance_month/12),2);

                                        $daysinyear =date_diff(date_create($coverage->first_payment_on), date_create($renewal_date));
                                        if($daysinyear->format("%y") < 1){
                                            $daysinyear =$daysinyear->format("%a");
                                        }else{
                                            $daysinyear = round($daysinyear->format("%a")/$daysinyear->format("%y"));
                                        }
                                        $balance_days =date_diff(date_create($today), date_create($renewal_date))->format('%R%a');
                                        $new_coverage->payment_annually = round($payment_annually*($balance_days/$daysinyear),2);
                                        $new_coverage->payment_without_loading =round($without_loading*($balance_days/$daysinyear),2);
                                        $new_coverage->full_premium = round($payment_annually*($balance_days/$daysinyear),2);

                                }

                            }
                            $new_coverage->payment_monthly = round($coverage->product->covertAnnuallyToMonthly($payment_annually),2);
                            // $new_coverage->full_premium = round($coverage->product->covertAnnuallyToMonthly($payment_annually),2);

                            
                        }
                        else{
                            $latestuw = Coverage::where('owner_id',$coverage->owner_id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
                            $underwriting=Underwriting::where('id',$latestuw)->first();
                            $newPrice = $coverage->product->getPrice($coverage->owner,$coverage->coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting)[0];
                            
                            $new_coverage->payment_annually = round($newPrice,2);
                            $without_loading =$coverage->product->getPrice($coverage->owner,$coverage->coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting)[3];
                            if($coverage->payment_term_new =='monthly'){
                                $new_coverage->payment_without_loading = round($coverage->product->covertAnnuallyToMonthly($without_loading),2);
                                $new_coverage->full_premium = round($coverage->product->covertAnnuallyToMonthly($newPrice),2);

                            }else{
                                $new_coverage->payment_without_loading = round($without_loading,2);
                                $new_coverage->full_premium = round($newPrice,2);
                            }
                              
                            if($coverage->payment_term != $coverage->payment_term_new ){
                                if($coverage->payment_term_new == 'annually'){
                                    $today = Carbon::now();
                                    //     $diff_month =date_diff(date_create($today), date_create($coverage->first_payment_on));
                                    //     $balance_month = 12 - $diff_month->format('%m');
                                    //    // dd($diff_month->format('%m'));
                                    //    $new_coverage->payment_annually = round($newPrice*($balance_month/12),2);

                                    $daysinyear =date_diff(date_create($coverage->first_payment_on), date_create($renewal_date));

                                    if($daysinyear->format("%y") < 1){
                                        $daysinyear =$daysinyear->format("%a");
                                    }else{
                                        $daysinyear = round($daysinyear->format("%a")/$daysinyear->format("%y"));
                                    }
                                    $balance_days =date_diff(date_create($today), date_create($renewal_date))->format('%R%a');
                                    $new_coverage->payment_annually = round($newPrice*($balance_days/$daysinyear),2);
                                    $new_coverage->payment_without_loading =round($without_loading*($balance_days/$daysinyear),2);
                                    $new_coverage->full_premium = round($newPrice*($balance_days/$daysinyear),2);


                                }

                            }
                            $new_coverage->payment_monthly = round($coverage->product->covertAnnuallyToMonthly($newPrice),2);
                            // $new_coverage->full_premium = round($coverage->product->covertAnnuallyToMonthly($newPrice),2);

                        }

                        $new_coverage->next_payment_on = null;
                        $new_coverage->last_payment_on = null;
                        $new_coverage->payment_term = $coverage->payment_term_new;
                       
                        if($coverage->payment_term == $coverage->payment_term_new ){
                            
                          if($new_coverage->payment_term_new == 'annually' || (now()->startOfDay()->diffInDays($renewal_date) == 0 && $new_coverage->payment_term_new == 'monthly')){
                             
                              $new_coverage->renewal_date =Carbon::parse($renewal_date)->addYear();
                              $new_coverage->renewal_last_payment_on =now();
  
                          } 
                         }else{
                          if($new_coverage->payment_term_new == 'monthly' || (now()->startOfDay()->diffInDays($renewal_date) == 0 && $new_coverage->payment_term_new == 'annually')){
                            
                              $new_coverage->renewal_date =Carbon::parse($renewal_date)->addYear();
                              $new_coverage->renewal_last_payment_on =now();
  
                          }
                         }
                        //$new_coverage->state = Enum::COVERAGE_STATE_INACTIVE;
                        if( $coverage->product_id ==5){
                            $new_coverage->status =Enum::COVERAGE_STATUS_GRACE_UNPAID;
                        }else{
                            $new_coverage->status = $coverage->status == Enum::COVERAGE_STATUS_ACTIVE_INCREASED? Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID:Enum::COVERAGE_STATUS_GRACE_UNPAID;

                        }
                        
                        $new_coverage->parent_id = $coverage->id;
                        $new_coverage->save();
                    
                       
                        $new_coverage->state = Enum::COVERAGE_STATE_INACTIVE;
                        $new_coverage->save();

                        $total += ($new_coverage->payment_term == 'annually') ? $new_coverage->payment_annually : $new_coverage->payment_monthly;

                        $coverage_order = new CoverageOrder();
                        $coverage_order->coverage_id = $new_coverage->id;
                        $coverage_order->order_id = $new_order->id;
                        $coverage_order->save();
                    }

                    $transaction_fee = $total * config('static.transaction_fee');
                    $new_order->update([
                        'amount'=>$total + $transaction_fee,
                        'true_amount'=>$total + $transaction_fee
                    ]);


                    $thanksgiving=$new_order->coverages()->first()->owner()->first()->thanksgiving()->get();
                    $discount = Helpers::calcThanksgivingDiscount($thanksgiving,$new_order->true_amount);
                    //  if($discount > 0){
                    //             $new_order->amount =$discount;
                    //             $new_order->save();
                    //         }
                            
                    $owner_user_id = Individual::where('id',$new_order->coverages()->first()->owner_id)->first()->user_id;
                    $owner_name =Individual::where('id',$new_order->coverages()->first()->owner_id)->first()->name;
                    $owner =User::where('id',$owner_user_id)->first();
                    if($discount > 0){
                        $discount = $new_order->true_amount -$discount;
                        Credit::createDepositSelf($owner_user_id,$new_order,$discount);
                    }

                    $locale = $owner->locale;
                    App::setLocale($locale ?? 'en');


                    if(($payer_card)->isNotEmpty() && $auto_debit){
                        try{
                           $payment = ProcessPayment::dispatchNow($payableOrder->id);
                            }catch (\Throwable $e) {
                                $transaction_check = Transaction::where('order_id',$payableOrder->id)->latest()->first();
                                if($transaction_check){
                                if($transaction_check->success == 0){
                                    $gracecov =$new_order->coverages()->get();
                                    foreach( $gracecov as $cov){
                                        $cov->state =Enum::COVERAGE_STATE_INACTIVE;
                                        $cov->save();
                                    }
    
                                    $owner_id =$new_order->coverages()->first()->owner->id;
                                    $payer_indv_id =$new_order->coverages()->first()->payer->profile->id;
                                    if($owner_id == $payer_indv_id){
                                         
                                        $premium =round($new_order->true_amount - $discount,2);
                                        $new_order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                            'translate_data' => ['premimum_amount'=>$premium,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')],
                                            'buttons'   => ['ok'],
                                            //'auto_read' => FALSE
                                        ]);
                        
                                        $data['subject'] = __('mobile.coverage_renewal');
                        
                                        $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $new_order->coverages()->first()->payer->profile->name,'premimum_amount'=>$premium,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')]);
                                
                                        Notification::route('mail',$new_order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                    }
            
                                 }
                            }
                            }
    
                            $transaction_check = Transaction::where('order_id',$payableOrder->id)->latest()->first();
                            if($transaction_check){
                            if($transaction_check->success == 0 && Carbon::parse($transaction_check->created_at) == Carbon::today()){
                                $gracecov =$new_order->coverages()->get();
                                foreach( $gracecov as $cov){
                                    $cov->state =Enum::COVERAGE_STATE_INACTIVE;
                                    $cov->save();
                                }
                                $owner_id =$new_order->coverages()->first()->owner->id;
                                $payer_indv_id =$new_order->coverages()->first()->payer->profile->id;
                                if($owner_id == $payer_indv_id){
                                             
                                    $premium =round($new_order->true_amount - $discount,2);
                                    $new_order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                        'translate_data' => ['premimum_amount'=>$premium,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')],
                                        'buttons'   => ['ok'],
                                        //'auto_read' => FALSE
                                    ]);
                    
                                    $data['subject'] = __('mobile.coverage_renewal');
                    
                                    $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $new_order->coverages()->first()->payer->profile->name,'premimum_amount'=>$premium,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')]);
                            
                                    Notification::route('mail',$new_order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                }
        
                             }else{
                                  GenerateDocument::dispatch($new_order->coverages()->get(),$owner_user_id,__('mobile.payment_success_desc', ['name' => ucwords(strtolower($owner_name))]),__('mobile.payment_success_subject'));
    
                            }
                        }
                        }else{
    
    
                           
                                    $new_order->last_try_on = now();
                                    $new_order->next_try_on = Carbon::today()->addDays(7);
                                    $new_order->retries -= 1;
                                    $new_order->save();
                                    if($new_order->grace_period == 30){
                                        $new_order->next_try_on = ($new_order->retries ==1)?Carbon::today()->addDays(2):Carbon::today()->addDays(7);
                                        $new_order->save();
                                    }
                                    if($order->grace_period == 90){
                                        $new_order->next_try_on = ($new_order->retries <= 6)?Carbon::today()->addDays(14):Carbon::today()->addDays(7);
                                        $new_order->save();
                                    }
                                   
                                     $owner_id =$new_order->coverages()->first()->owner->id;
                                     $payer_indv_id =$new_order->coverages()->first()->payer->profile->id;
                                     if($owner_id == $payer_indv_id){
                                        $new_order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                             'translate_data' => ['premimum_amount'=>$new_order->amount,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')],
                                             'buttons'   => ['ok'],
                                             //'auto_read' => FALSE
                                         ]);
                         
                                         $data['subject'] = __('mobile.coverage_renewal');
                         
                                         $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $new_order->coverages()->first()->payer->profile->name,'premimum_amount'=>$new_order->amount,'next_payment_date'=> Carbon::parse($new_order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($new_order->next_try_on)->format('d M Y')]);
                                 
                                         Notification::route('mail',$new_order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                     }
                        }

                }

            

        }
    }catch (\Throwable $e) {
        ($e->getMessage());
    }
}

//        TestTime::addDays((Config::getValue('system_extra_day') ?? 0*(-1)))->addHours((Config::getValue('system_extra_hour') ?? 0*(-1)));
    }

}