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
use App\Jobs\GenerateDocument;
use App\Notifications\Sms;
use Illuminate\Support\Facades\Notification;
use App\User;
use App\Order;
use App\Transaction;
use App\Thanksgiving;
use App\Underwriting;
use App\Credit;
use App\CoverageOrder;
use App\Coverage;
use App\SpoCharityFunds;
use App\IndustryJob;
use App\Individual;
use App\SpoCharityFundApplication;
use App\Helpers;
use App\Helpers\Enum;
use Carbon\Carbon;


class FundAllocation implements ShouldQueue
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
     
        //$user = User::where('email','preethimohan43@gmail.com')->get();
        $applications =SpoCharityFundApplication::where('status','QUEUE')->orderby('submitted_on','asc')->get();
       // $charityfundsum=0;
       // $sop=SpoCharityFunds::where('status','ADDED')->get();

       // $amounts=$sop->pluck('charity_fund');
        
       // foreach($amounts as $key=>$value){
           // if(isset($value))
           // $charityfundsum += $value;
         // }

        //   $approvedfund =Transaction::where('gateway','sponsored_insurance')->sum('amount')?? 0;
        //   $charityfundsum =$charityfundsum - $approvedfund;
        //   if($charityfundsum < 0){
        //     $charityfundsum =0;
        //   }
         // $first_applicant =SpoCharityFundApplication::where('status','QUEUE')->orderby('created_at','asc')->first();

        
       
       
        foreach($applications as $application){
        
        
              $charityfundsum=0;
              $sop=SpoCharityFunds::where('status','ADDED')->get();
  
              $amounts=$sop->pluck('charity_fund');
        
            foreach($amounts as $key=>$value){
               if(isset($value))
                $charityfundsum += $value;
               }
            $approvedfund =Transaction::where('gateway','sponsored_insurance')->sum('amount')?? 0;
            $charityfundsum =$charityfundsum - $approvedfund;
            if($charityfundsum < 0){
            $charityfundsum =0;
              }
            $first_applicant =SpoCharityFundApplication::where('status','QUEUE')->orderby('submitted_on','asc')->first();
        if($first_applicant){
           $user =Individual::where('user_id',$application->user_id)->first();
           $coverages =Coverage::where('owner_id',$user->id)->where('payer_id',$first_applicant->user_id)->where('status','unpaid')->get();

           foreach($coverages as $coverage){
               $this->agecheck($user,$coverage->product_id,$coverage);
           }
          //  dd($coverages->sum('payment_annually'));
          //  dd($user->age());
           if(!$coverages->isNotEmpty()){
               $first_applicant->status ='REJECTED';
               $first_applicant->save();
               $applicantuser=User::where('id',$application->user_id)->first();
               $applicantuser->sendNotification('Attention', 'mobile.spo_age_reject', [
                   'translate_data' => ['name' =>$applicantuser->profile->name],
                   'buttons' => [
                       ['title' => 'ok'],
                   ],
   
               ]);
   
               return;
   
   
           }
        }
        if($first_applicant){
        $first_applicant_id=Individual::where('user_id',$first_applicant->user_id)->first()->id;
        $first_applicant_coverages =Coverage::where('owner_id',$first_applicant_id)->where('payer_id',$first_applicant->user_id)->where('status','unpaid')->sum('payment_annually');
        $user =Individual::where('user_id',$application->user_id)->first();
        $user_data=User::where('id',$application->user_id)->first();
        
        $coverages =Coverage::where('owner_id',$user->id)->where('payer_id',$application->user_id)->where('status','unpaid')->get();
       // $coverages =Coverage::where('owner_id',$user->id)->where('payer_id',$first_applicant->user_id)->where('status','unpaid')->get();
        //dd($user->age());
        foreach($coverages as $coverage){
            $this->agecheck($user,$coverage->product_id,$coverage);
        }
       //  dd($coverages->sum('payment_annually'));
       //  dd($user->age());
        if(!$coverages->isNotEmpty()){
            $first_applicant->status ='REJECTED';
            $first_applicant->save();
            $applicantuser=User::where('id',$application->user_id)->first();
            $applicantuser->sendNotification('Attention', 'mobile.spo_age_reject', [
                'translate_data' => ['name' =>$applicantuser->profile->name],
                'buttons' => [
                    ['title' => 'ok'],
                ],

            ]);

            return;


        }

        if( $first_applicant_coverages < $charityfundsum){
            
        $order = new Order();
        $order->amount = $coverages->sum('payment_annually');
        $order->true_amount = $coverages->sum('payment_annually');
        $order->status = Enum::ORDER_SUCCESSFUL;
        $order->due_date = now();
        $order->payer_id = $application->user_id;
        $order->retries = 1;
        $order->type = Enum::ORDER_TYPE_NEW;
        $order->grace_period = 30;
        $order->last_try_on = now();
        $order->next_try_on = Carbon::today()->addDays(7);
        $order->save();


        $actions = [];
        $coverageIds = [];
        $newMember = FALSE;
        $arrPlanChangeCoverages =   [];

        foreach($coverages as $coverage){


            //add member action
            $coverage = Coverage::whereUuid($coverage->uuid)->first();
            if (empty($coverage))
                continue;

            $old_coverage = Coverage::where('owner_id', $coverage->owner_id)
                ->where('product_id', $coverage->product_id)
                ->where('covered_id', $coverage->covered_id)
                ->where(function ($q) {
                    $q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
                        ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
                })
                ->orderBy('first_payment_on', 'desc')->first();

            $first_payment_on = $old_coverage->first_payment_on ?? NULL;

            // add action for Member Addition
            if($coverage->product->name == 'Medical'){
                $actions['product_name'] = $coverage->product->name;
                $actions['new_payment_term'] = $coverage->payment_term;
                $actions['new_medical'] = $coverage->deductible;

                if(!empty($old_coverage)){
                    $actions['old_payment_term'] = $old_coverage->payment_term;
                    $actions['old_medical']      = $old_coverage->deductible;
                    $actions['changed_at']       = Carbon::now();
                    $actions['first_payment_on'] = $old_coverage->first_payment_on;
                    $actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
                }
            }
             elseif($coverage->product->name == 'Disability'){
                $actions['product_name'] = $coverage->product->name;
                $actions['new_payment_term'] = $coverage->payment_term;
                $actions['new_disability'] = $coverage->coverage;
               

                if(!empty($old_coverage)){
                    $actions['old_payment_term'] = $old_coverage->payment_term;
                    $actions['old_disability']      = $old_coverage->coverage;
                    $actions['changed_at']       = Carbon::now();
                    $actions['first_payment_on'] = $old_coverage->first_payment_on;
                    $actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
                }
            }
            elseif($coverage->product->name == 'Critical Illness'){
                $actions['product_name'] = $coverage->product->name;
                $actions['new_payment_term'] = $coverage->payment_term;
                $actions['new_criticalillness'] = $coverage->coverage;
               

                if(!empty($old_coverage)){
                    $actions['old_payment_term'] = $old_coverage->payment_term;
                    $actions['old_criticalillness']      = $old_coverage->coverage;
                    $actions['changed_at']       = Carbon::now();
                    $actions['first_payment_on'] = $old_coverage->first_payment_on;
                    $actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
                }
            }
            elseif($coverage->product->name == 'Accident'){
                $actions['product_name'] = $coverage->product->name;
                $actions['new_payment_term'] = $coverage->payment_term;
                $actions['new_accident'] = $coverage->coverage;
               

                if(!empty($old_coverage)){
                    $actions['old_payment_term'] = $old_coverage->payment_term;
                    $actions['old_accident']      = $old_coverage->coverage;
                    $actions['changed_at']       = Carbon::now();
                    $actions['first_payment_on'] = $old_coverage->first_payment_on;
                    $actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
                }
            }
            elseif($coverage->product->name == 'Death'){
                $actions['product_name'] = $coverage->product->name;
                $actions['new_payment_term'] = $coverage->payment_term;
                $actions['new_death'] = $coverage->coverage;
               

                if(!empty($old_coverage)){
                    $actions['old_payment_term'] = $old_coverage->payment_term;
                    $actions['old_death']      = $old_coverage->coverage;
                    $actions['changed_at']       = Carbon::now();
                    $actions['first_payment_on'] = $old_coverage->first_payment_on;
                    $actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
                }
            }

            array_push($coverageIds, $coverage->id);

            $newMemberCount = $user_data->actions()->where('event', Enum::ACTION_EVENT_NEW_MEMBER)->count();
            $terminateCount = $user_data->actions()->where('event', Enum::ACTION_EVENT_TERMINATE)->count();

            if (empty($first_payment_on) || ($terminateCount >= $newMemberCount)) {
                $newMember = true;
            }





            $c_o = new CoverageOrder();
                    $c_o->coverage_id = $coverage->id;
                    $c_o->order_id = $order->id;
                    $c_o->save();
    
            
            $coverage->state ='active';
            $coverage->status='active';
            $coverage->uw_id =Underwriting::where("individual_id", $coverage->owner_id)->latest()->first()->id ?? NULL;
            $coverage->next_payment_on = ($coverage->payment_term == 'monthly') ? now()->addMonth() : now()->addYear();
            $coverage->first_payment_on = $coverage->first_payment_on ?? now();
            $coverage->last_payment_on = now();
            $coverage->save();

            }

        if(!empty($actions)){
            if($newMember){
                $this->memberAdditionAction($user_data,$actions,$coverageIds,$arrPlanChangeCoverages);
            }else{
                $this->planChangeAction($user_data,$actions,$coverageIds);
            }
            }
       
        $transaction = new Transaction();
        $transaction->order_id = $order->id;
        $transaction->gateway = 'sponsored_insurance';
        $transaction->transaction_ref = 'TRX'.time();
        $transaction->amount = $order->amount;
        $transaction->success =1;
        $transaction->card_no = '12345678';
        $transaction->save();
        $application->status ='ACTIVE';
        $application->save();

   
        //$user_id=Individual::where('id',$payer->profile->id)->first()->user_id;
        $thanksgiving=Thanksgiving::where('individual_id',$first_applicant_id)->where('type','charity')->latest()->first()->percentage;
        
        if($thanksgiving){
            $thanksgiving_id = Thanksgiving::where('individual_id',$first_applicant_id)->where('type','charity')->latest()->first()->id;
            Credit::create([
                'order_id'=>$order->id,
                'from_id'=>$application->user_id,
                'amount'=>$order->true_amount * ($thanksgiving / config('static.thanksgiving_percent')),
                'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
                'type_item_id'=> $thanksgiving_id
            ]);

            $sop_fund= new SpoCharityFunds;
            $sop_fund->user_id =$first_applicant->user_id;
            $sop_fund->order_id=$order->id;
           
            $sop_fund->transaction_id= $order->transactions()->latest()->first()->id;
           
            $sop_fund->transactions_no=$order->transactions()->latest()->first()->transactions_ref;
          
            $sop_fund->amount =$order->amount;
           
            $sop_fund->percentage =$thanksgiving;
         
            $sop_fund->charity_fund=($order->amount*($thanksgiving/1000));
           
            
            if( $user->freelook()){
            $sop_fund->status ='ON HOLD';
            }else{
            $sop_fund->status ='ADDED';
            }
            $sop_fund->save();
            }
          

           
        
        }

           GenerateDocument::dispatch($coverages,$application->user_id,__('mobile.spo_success_desc', ['name' => ucwords(strtolower($user->name))]),__('mobile.payment_success_subject'));

            

        }
    }
       
    $charity_funds =SpoCharityFunds::where('status','ON HOLD')->get();
        if($charity_funds->isNotEmpty()){
        foreach($charity_funds as $charity_fund){
            if(now()->startOfDay()->diffInDays($charity_fund->created_at) >= 18){
                $charity_fund->status ='ADDED';
                $charity_fund->save();
            }

        }
    }
        

    
         
        
    }

    private function memberAdditionAction($user,array $actions,array $coverageIds, $arrPlanChangeCoverages = []): void
    {
        $action = $user
            ->actions()
            ->create([
                         'user_id'    => $user->id,
                         'type'       => Enum::ACTION_TYPE_MEMEBR_ADDITION,
                         'event'      => Enum::ACTION_EVENT_NEW_MEMBER,
                         'actions'    => $actions,
                         'execute_on' => Carbon::now(),
                         'status'     => Enum::ACTION_STATUS_EXECUTED,
                         'plan_change_coverage_ids' => implode(',', $arrPlanChangeCoverages)
                     ]);
        $action->coverages()->attach($coverageIds);
    }

    private function planChangeAction($user,array $actions,array $coverageIds): void
    {
        $action = $user
            ->actions()
            ->create([
                         'user_id'    => $user->id,
                         'type'       => Enum::ACTION_TYPE_PLAN_CHANGE,
                         'event'      => Enum::ACTION_EVENT_PLAN_CHANGE,
                         'actions'    => $actions,
                         'execute_on' => Carbon::now(),
                         'status'     => Enum::ACTION_STATUS_EXECUTED
                     ]);
        $action->coverages()->attach($coverageIds);

        //$actions = collect($action->actions)->first();

        if($actions['old_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY && $actions['new_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_ANNUALLY){
            $totalCredit = 0;
            //foreach ($actions as $actionItem){
                $to = Carbon::parse($actions['first_payment_on']);
                $from = Carbon::parse($actions['changed_at']);
                $diffInMonths = ceil($to->floatDiffInMonths($from));
                $totalCredit += round(($actions['current_annually'] * (12 - $diffInMonths)) / 12,2);
            //}
            if($totalCredit > 0){
                Credit::create([
                                   'from_id'      => $action->user_id,
                                   'amount'       => -$totalCredit,
                                   'type'         => Enum::CREDIT_TYPE_ACTION,
                                   'type_item_id' => $action->id,
                               ]);
            }
        }
    }

    private function agecheck($profile,$product,$coverage){
       if($product == 1){    
           if($profile->age() > 65){
               $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
               $coverage->save();
           }
       }elseif($product == 2){
           if($profile->age() > 65){
               $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
               $coverage->save();
           }
           
       }elseif($product == 3){
           if($profile->age() > 65){
               $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
               $coverage->save();
           }

       }elseif($product == 4){
           if($profile->age() > 60){
               $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
               $coverage->save();
           }
       }else{
          
           if($profile->age() > 55){
               $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
               $coverage->save();
           }
       
       }
       Helpers::updatePremiumOnOccupation($profile);
   }
}
