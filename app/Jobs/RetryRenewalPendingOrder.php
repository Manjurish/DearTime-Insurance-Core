<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Coverage;
use App\Order;
use App\Helpers;
use App\Individual;
use App\User;
use App\Helpers\Enum;
use App\Jobs\ProcessPayment;
use App\Credit;
use App\Transaction;
use App\Notifications\Email;
use App\Jobs\GenerateDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class RetryRenewalPendingOrder implements ShouldQueue
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
        //

        $pending_orders =Order::where('status',Enum::ORDER_PENDING)
                        ->whereRaw('DATEDIFF(DATE(next_try_on),CURDATE()) = 0')
                        ->where('type',Enum::ORDER_TYPE_RENEW)
                        ->get();

        
        foreach ($pending_orders as $order){
            
               $auto_debit = false;

               $payer_card = $order->payer->profile->bankCards;
            
               if(($payer_card)->isNotEmpty()){
                $auto_debit = $payer_card->first()->auto_debit == Enum::CARD_AUTO_DEBIT_ACTIVE;
            }
                $graceCoverages = $order->coverages()->where(function ($q){
                         $q->where('status',Enum::COVERAGE_STATUS_GRACE_UNPAID)
                         ->orWhere('status',Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID);
                    });
   
            
             if($graceCoverages->count() != 0){
               
                     if($order->retries > 0 ){
                        if(($payer_card)->isNotEmpty() && $auto_debit){
                            

                        try{
                            
                           
                            $payment = ProcessPayment::dispatchNow($order->id);

                            $thanksgiving= $order->coverages()->first()->owner()->first()->thanksgiving()->get();
                            $discount = Helpers::calcThanksgivingDiscount($thanksgiving,$order->true_amount);
                            $owner_user_id = Individual::where('id',$order->coverages()->first()->owner_id)->first()->user_id;
                            $owner_name =Individual::where('id',$order->coverages()->first()->owner_id)->first()->name;
                            $owner =User::where('id',$owner_user_id)->first();
                            if($discount > 0){
                                $discount = $order->true_amount -$discount;
                                Credit::createDepositSelf($owner_user_id,$order,$discount);
                            }
                       
                             }catch (\Throwable $e) {
                                $transaction_check = Transaction::where('order_id',$order->id)->latest()->first();
                                if($transaction_check){
                               
                                if($transaction_check->success == 0){
                                    $order->last_try_on = now();
                                    $order->next_try_on = Carbon::today()->addDays(7);
                                    $order->retries -= 1;
                                    $order->save();
                                    if($order->grace_period == 30){
                                        $order->next_try_on = ($order->retries ==1)?Carbon::today()->addDays(2):Carbon::today()->addDays(7);
                                        $order->save();
                                    }
                                    if($order->grace_period == 90){
                                        $order->next_try_on = ($order->retries <= 6)?(($order->retries ==1)?Carbon::today()->addDays(6):Carbon::today()->addDays(14)):Carbon::today()->addDays(7);
                                        $order->save();
                                    }
                                   

                                    if($order->retries ==0){
                                        $this->deactivateCoverage($order->coverages);
                                        if(!empty($order->parent)){
                                            $this->deactivateCoverage($order->parent->coverages);
                                        }
                                    }
                                    $owner_id =$order->coverages()->first()->owner->id;
                                    $payer_indv_id =$order->coverages()->first()->payer->profile->id;
                                    if($owner_id == $payer_indv_id){
                                       $order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                            'translate_data' => ['premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')],
                                            'buttons'   => ['ok'],
                                            //'auto_read' => FALSE
                                        ]);
                        
                                        $data['subject'] = __('mobile.coverage_renewal');
                        
                                        $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $order->coverages()->first()->payer->profile->name,'premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')]);
                                
                                        Notification::route('mail',$order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                    }
               
               
                                 }

                                }
                             }

                             
                             $owner_user_id =$order->coverages()->first()->payer->profile->user_id;
                             $owner_name =Individual::where('id',$order->coverages()->first()->owner_id)->first()->name;

                             $transaction_check = Transaction::where('order_id',$order->id)->latest()->first();
                             //dd($transaction_check);
                             if($transaction_check){
                            
                             if(($transaction_check->success == 0)&& Carbon::parse($transaction_check->created_at)->format('Y-m-d') == Carbon::today()->format('Y-m-d')){
                                $order->last_try_on = now();
                                $order->next_try_on = Carbon::today()->addDays(7);
                                $order->retries -= 1;
                                $order->save();
                                if($order->grace_period == 30){
                                    $order->next_try_on = ($order->retries ==1)?Carbon::today()->addDays(2):Carbon::today()->addDays(7);
                                    $order->save();
                                }
                                if($order->grace_period == 90){
                                    $order->next_try_on = ($order->retries <= 6)?(($order->retries ==1)?Carbon::today()->addDays(6):Carbon::today()->addDays(14)):Carbon::today()->addDays(7);
                                    $order->save();
                                }
                               
                                 $owner_id =$order->coverages()->first()->owner->id;
                                 $payer_indv_id =$order->coverages()->first()->payer->profile->id;
                                 if($owner_id == $payer_indv_id){
                                    $order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                         'translate_data' => ['premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')],
                                         'buttons'   => ['ok'],
                                         //'auto_read' => FALSE
                                     ]);
                     
                                     $data['subject'] = __('mobile.coverage_renewal');
                     
                                     $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $order->coverages()->first()->payer->profile->name,'premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')]);
                             
                                     Notification::route('mail',$order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                 }
            
                                 //dd("payment Failed");
            
                              }else{
                                   GenerateDocument::dispatch($order->coverages()->get(),$owner_user_id,__('mobile.payment_success_desc', ['name' => ucwords(strtolower($owner_name))]),__('mobile.payment_success_subject'));
                            
                             }


                         }

                        }else{

                            
                          
                                    $order->last_try_on = now();
                                    $order->next_try_on = Carbon::today()->addDays(7);
                                    $order->retries -= 1;
                                    $order->save();
                                    if($order->grace_period == 30){
                                        $order->next_try_on = ($order->retries ==1)?Carbon::today()->addDays(2):Carbon::today()->addDays(7);
                                        $order->save();
                                    }
                                    if($order->grace_period == 90){
                                        $order->next_try_on = ($order->retries <= 6)?(($order->retries ==1)?Carbon::today()->addDays(6):Carbon::today()->addDays(14)):Carbon::today()->addDays(7);
                                        $order->save();
                                    }
                                   
                                     $owner_id =$order->coverages()->first()->owner->id;
                                     $payer_indv_id =$order->coverages()->first()->payer->profile->id;
                                     if($owner_id == $payer_indv_id){
                                        $order->coverages()->first()->payer->sendNotification('mobile.coverage_renewal', 'mobile.renewal_payment_failureinapp', [
                                             'translate_data' => ['premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')],
                                             'buttons'   => ['ok'],
                                             //'auto_read' => FALSE
                                         ]);
                         
                                         $data['subject'] = __('mobile.coverage_renewal');
                         
                                         $textEmail  = __('mobile.renewal_payment_failure', ['owner_name'=> $order->coverages()->first()->payer->profile->name,'premimum_amount'=>$order->amount,'next_payment_date'=> Carbon::parse($order->coverages()->first()->next_payment_on)->format('d M Y'),'next_try_on'=> Carbon::parse($order->next_try_on)->format('d M Y')]);
                                 
                                         Notification::route('mail',$order->coverages()->first()->payer->email)->notify(new Email($textEmail, $data));
                                     }
                        }

                     }

                     if($order->retries ==0){

                        $owner_user_id =$order->coverages()->first()->payer->profile->user_id;


                        $discount =$order->true_amount - $order->amount;
                        if($discount > 0){
    
                            Credit::createWithdrawSelf($owner_user_id,$order);

                        }
                        $this->deactivateCoverage($order->coverages);
                        if(!empty($order->parent)){
                            $this->deactivateCoverage($order->parent->coverages);
                        }

                        foreach($order->coverages as $coverage){
                            $active_cov =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('payer_id',$coverage->payer_id)->where('state','active')->get();
                            if($active_cov->isNotEmpty()){
                                foreach($active_cov as $cov){
                                    if(now()->startOfDay()->diffInDays($cov->next_payment_on) <= 0){
                                        $cov->status =Enum::COVERAGE_STATUS_FULFILLED_DEACTIVATE;
                                        $cov->state ='inactive';
                                        $cov->save();
                                    }
                                   
                                }
                            }

                        }



                        foreach($order->coverages as $coverage){
                            $grace_cov =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('payer_id',$coverage->payer_id)->whereIn('status',['grace-unpaid','grace-increase-unpaid'])->get();
                            if($grace_cov->isNotEmpty()){
                                foreach($grace_cov as $gcov){
                                    if(now()->startOfDay()->diffInDays($gcov->next_payment_on) <= 0){
                                        $gcov->status =Enum::COVERAGE_STATUS_GRACE_DEACTIVATE;
                                        $gcov->state ='inactive';
                                        $gcov->save();
                                    }
                                   
                                }
                            }

                        }
                    }
                    
                    }

                        }
       // $pending_orders =Order::where('status',)
    }

    public function deactivateCoverage($coverages){
        foreach ($coverages as $coverage){
            $coverage->state = Enum::COVERAGE_STATE_INACTIVE;
            switch ($coverage->status){
                case Enum::COVERAGE_STATUS_DECREASE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_FULFILLED_DEACTIVATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID:
                case Enum::COVERAGE_STATUS_GRACE_UNPAID:
                    $coverage->status = Enum::COVERAGE_STATUS_GRACE_DEACTIVATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_ACTIVE_INCREASED:
                    $coverage->status = Enum::COVERAGE_STATUS_FULFILLED_DEACTIVATE;
                    $coverage->save();
                    break;
                case Enum::COVERAGE_STATUS_ACTIVE:
                    $coverage->status = Enum::COVERAGE_STATUS_FULFILLED_DEACTIVATE;
                    $coverage->save();
                    break;
            }
        }

    }
}
