<?php     
namespace App\Jobs;
use App\Beneficiary;
use App\Coverage;
use App\Credit;
use App\Events\ChangedCoveragesStatusEvent;
use App\Helpers\Enum;
use App\Http\Controllers\User\PaymentGatewayController;
use App\Individual;
use App\paymentresponselogs_inout;
use App\Notifications\Email;
use App\Notifications\EmailVerification;
use App\Notifications\MobileVerification;
use App\Notifications\Sms;
use App\Order;
use App\Thanksgiving;
use App\Transaction;
use Carbon\Carbon;
//use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\PaymentResponseLogs;
use Illuminate\Http\Request;
use App\User;
class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    var $order_id;
    private $owner_id;
    private $owner_user_id;
    var $from_ops;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id,$from_ops =null)
    {
        //
        $this->order_id = $order_id;
        $this->from_ops = $from_ops ?? false;
        $this->owner_id = Order::where('id',$order_id)->first()->coverages()->first()->owner_id;
        $this->owner_user_id = Individual::where('id',$this->owner_id)->first()->user_id;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
     public function handle(Request $request)
    {

      
        $order = Order::findOrfail($this->order_id);
       
        $payer = $order->payer;
        if (empty($order) || empty($payer))
            return;

        if ($order->true_amount == 0) {
            $this->setOrderAsPaid($order, $payer);
            return $order;
        }

        // Commented for fulfilled status on payment failure on May/16/2023
        // // if order has been renew and ended retries
        // if($order->retries == 0){
        //     //todo if order was renew, need change older coverage status to fulfilled
        //     $order->update([
        //         'status'=>Enum::ORDER_UNSUCCESSFUL
        //     ]);

        //     Coverage::changeCoveragesToInactive($order->coverages);
        //     event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_CANCELLED));

        //     // set fulfilled status for old coverages
        //     if(!empty($order->parent)){
        //         Coverage::changeCoveragesToInactive($order->parent->coverages);
        //         event(new ChangedCoveragesStatusEvent($order->parent->coverages,$order->parent,Enum::COVERAGE_STATUS_FULFILLED));
        //     }


        //     return $order;
        // }
       
        if(!$this->from_ops){
        if($order->credit()->count() > 0){
            $discounts=$order->credit()->get();
            foreach ($discounts as $discount){
                $order->amount -= $discount->amount;
            }
            $order->save();
        }
    }

        $card = $payer->profile->bankCards()->latest()->first();
        if(!empty($request->input('covered_payer_id'))) {            
            $covered_payer_id   =   $request->input('covered_payer_id');
            $covered_payer      =   User::find($covered_payer_id);
            $card               =   $covered_payer->profile->bankCards()->latest()->first();
        }

       
        $trans_check = Transaction::where('order_id',$order->id)->where('success',1)->first();

        if(empty($trans_check)){
        $transaction = new Transaction();
        $transaction->order_id = $order->id;
        $transaction->gateway = 'senangpay_card';
        $transaction->transaction_ref = 'TRX'.time();
        $transaction->amount = $order->amount;
        $transaction->card_no = $card->masked_pan;
        $transaction->save();


        //if($order->type == Enum::ORDER_TYPE_NEW){
        $amount           =   (strval($order->amount) * 100);
        $merchant_id      =   config('payment.senangpay.merchant_id');
        $hashed_string    =   hash_hmac('SHA256', $merchant_id . $payer->profile->name . $payer->email . $payer->profile->mobile . $order->ref_no . $transaction->transaction_ref . $amount, config('payment.senangpay.secret_key'));
        $transaction->bank = $hashed_string;
        $transaction->save();
        $payment_payload = [
            'token' => $card->token,
            'name' => $payer->profile->name,
            'email' => $payer->email,
            'detail' => $order->ref_no,
            'phone' => $payer->profile->mobile,
            'order_id' => $transaction->transaction_ref,
            'amount' => $amount,
            'hash' => $hashed_string
        ];

        try {        
            $request_store= new paymentresponselogs_inout();
            $request_store->transaction_ref=$transaction->transaction_ref;
            $request_store->request_senangpay= json_encode($payment_payload);
            $request_store->save();
            $payment_response =   Http::asForm()->withBasicAuth($merchant_id, '')
                                ->post(config('payment.senangpay.base_url') . 'pay_cc', $payment_payload);
            $bodyResponse     =   $payment_response->body();
            $paymentResponseLogs    =   new PaymentResponseLogs;
            $paymentResponseLogs->response_json     =   $bodyResponse;
            $paymentResponseLogs->save();


            $json     = json_decode($bodyResponse);
            $findrequest=paymentresponselogs_inout::where('transaction_ref',$transaction->transaction_ref)->first();
            if(!empty($findrequest)){
            $findrequest->response_senangpay= $bodyResponse;
            $findrequest->save();       
            }

            if(!isset($json->transaction_id))
            {
                $this->setOrderAsUnPaid($order);
                return $order;
            }

            # verify that the data was not tempered, verify the hash
            $string = sprintf(
                '%s%s%s%s%s%s',
                $merchant_id,
                $json->status,
                $json->order_id,
                $json->transaction_id,
                $json->amount_paid,
                $json->msg
            );
            $hashed_string = hash_hmac('SHA256', $string, config('payment.senangpay.secret_key'));
            
            //check for status
            if ($payment_response->successful() && $hashed_string == $json->hash && $json->status  == '1') {
                $transaction->success = true;
                $transaction->transaction_id = $json->transaction_id;
                $transaction->date = Carbon::now();
                $transaction->card_type = 'CREDIT';
                $transaction->bank = $hashed_string;
                $transaction->brand = '';
                $transaction->is_local_bin = '';
                $transaction->save();

                $this->setOrderAsPaid($order, $transaction);
            }
            else{
                $this->setOrderAsUnPaid($order);
            }

            return $order;
        } catch (\Throwable $e) {

            $paymentResponseLogs    =   new PaymentResponseLogs;
            $paymentResponseLogs->response_json     =   $e->getMessage();
            $paymentResponseLogs->save();

            $this->setOrderAsUnPaid($order);
            return $order;
        }
    }

        return $order;
    }
    public function setOrderAsUnPaid($order){
        // ORDER_UNSUCCESSFUL for new order
        // ORDER_PENDING      for renew order
        $order->status = $order->type == Enum::ORDER_TYPE_NEW? Enum::ORDER_UNSUCCESSFUL:Enum::ORDER_PENDING;
        if($order->status == Enum::ORDER_UNSUCCESSFUL){
            Coverage::changeCoveragesToInactive($order->coverages);
            event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_CANCELLED));
        }
        if($order->status ==Enum::ORDER_PENDING ){
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
            // Coverage::changeCoveragesToActive($order->coverages);
            // event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_GRACE_UNPAID));
        }
        if($order->retries ==0 && $order->type =Enum::ORDER_TYPE_RENEW){
            $this->deactivateCoverage($order->coverages);
            if(!empty($order->parent)){
                $this->deactivateCoverage($order->parent->coverages);
            }

            $discount_amount =$order->true_amount - $order->amount;
            if($discount_amount > 0){

                Credit::createWithdrawSelf($this->owner_user_id,$order);

            }

            foreach($order->coverages as $coverage){
                $active_cov =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('payer_id',$coverage->payer_id)->where('state','active')->get();
                if($active_cov->isNotEmpty()){
                    foreach($active_cov as $cov){

                        $current_nxt_diff = date_diff(date_create(now()),date_create(Carbon::parse($cov->next_payment_on)));
			            $current_nxt_diff =$current_nxt_diff->invert? -$current_nxt_diff->format('%a'):$current_nxt_diff->format('%a');
		
                        if($current_nxt_diff <= 0){
                            $cov->status =Enum::COVERAGE_STATUS_FULFILLED_DEACTIVATE;
                            $cov->state ='inactive';
                            $cov->save();
                        }
                       
                    }
                }

            }
        }
        if($order->credit()->count() > 0){
            Credit::createWithdrawSelf($this->owner_user_id,$order);
        }
        return $order;
    }
    public function setOrderAsPaid($order, $transaction)
    {
        $order->status = Enum::ORDER_SUCCESSFUL;
        $order->save();

       
        // set fulfilled status for old coverages
        if ($order->type==Enum::ORDER_TYPE_RENEW){

            foreach($order->parent->coverages as $cov){
                if($cov->status ==Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                $active_coverages =Coverage::where('owner_id',$cov->owner_id)->where('product_id',$cov->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
                ->get()->filter(function ($item){

                    $current_nxt_diff = date_diff(date_create(now()),date_create(Carbon::parse($item->next_payment_on)));
			        $current_nxt_diff =$current_nxt_diff->invert? -$current_nxt_diff->format('%a'):$current_nxt_diff->format('%a');
		
                    if($current_nxt_diff <= 0){
                        return $item;
                    }
                });
                Coverage::changeCoveragesToInactive($active_coverages);
            }
            }
        }
      
        if(!empty($order->parent)){
        
        
            $parent_cov=[];
            foreach($order->parent->coverages as $coverage){

           $current_nxt_diff = date_diff(date_create(now()),date_create(Carbon::parse($coverage->next_payment_on)));
			$current_nxt_diff =$current_nxt_diff->invert? -$current_nxt_diff->format('%a'):$current_nxt_diff->format('%a');

            if($current_nxt_diff <= 0){
                $parent_cov[]=$coverage;
                    }
                }
            
            Coverage::changeCoveragesToInactive($parent_cov);
        }
        


        Coverage::changeCoveragesToActive($order->coverages);
        


        $thanksgivingsArray = Thanksgiving::where('individual_id',$this->owner_id)->pluck('id')->toArray();
        foreach ($order->coverages as $coverage){
     
           if($order->type==Enum::ORDER_TYPE_RENEW){
                
                $decrease_check = Coverage::where('product_id',$coverage->product_id)->where('owner_id',$coverage->owner->id)->where('status',Enum::COVERAGE_STATUS_DECREASE_UNPAID)->where('next_payment_on','=',Null)->first();
                if($decrease_check){
                    $decrease_check->status =Enum::COVERAGE_STATUS_DECREASE_TERMINATE;
                    $decrease_check->save();
                }

                if($coverage->status == Enum::COVERAGE_STATUS_ACTIVE_INCREASED ){
                  $next_payment =Coverage::where('product_id',$coverage->product_id)->where('owner_id',$coverage->owner->id)->where('status','active')->first()->next_payment_on;
                  $current_nxt_diff = date_diff(date_create(now()),date_create(Carbon::parse($next_payment)));
			      $current_nxt_diff =$current_nxt_diff->invert? -$current_nxt_diff->format('%a'):$current_nxt_diff->format('%a');

                  if($current_nxt_diff <= 0){
                    if($coverage->payment_term =='monthly'){
                        $next_payment = Carbon::parse($next_payment)->addMonth();
                        $coverage->next_payment_on =$next_payment;
                    }else{
                        $next_payment = Carbon::parse($next_payment)->addYear();;
                        $coverage->next_payment_on =$next_payment;

                    }
                  }else{
                    $coverage->next_payment_on =$next_payment;
                  }
                  
                }else{
                    $parent_cov =$order->parent->coverages->first();
                  
                    if($parent_cov->status ==Enum::COVERAGE_STATUS_DECREASE_TERMINATE ){
                        $last_payment =Coverage::where('id',$parent_cov->parent_id)->first()->last_payment_on; 
                    }else{
                        $last_payment =$order->parent->coverages->first()->last_payment_on;

                    }
                    //$coverage->next_payment_on = ($coverage->payment_term == 'monthly') ? Carbon::parse($last_payment)->addMonths(2) : Carbon::parse($last_payment)->addYears(2);
                    if($parent_cov->payment_term =='monthly' && $parent_cov->payment_term_new =='annually' ){
                        $coverage->next_payment_on =$parent_cov->renewal_date;
                    }elseif($parent_cov->payment_term =='annually' && $parent_cov->payment_term_new =='monthly'){
                        if($parent_cov->parent_id !=0){
                            $cov = Coverage::where('parent_id',$parent_cov->parent_id)->first();
                            $parent_last_payment_on =Carbon::parse($cov->last_payment_on)->addYear();
                            $coverage->next_payment_on =Carbon::parse($parent_last_payment_on)->addMonth();

                        }else{
                            $parent_last_payment_on =Carbon::parse($parent_cov->last_payment_on)->addYear();
                            $coverage->next_payment_on =Carbon::parse($parent_last_payment_on)->addMonth();

 
                        }
                       
                    }else{
                        $coverage->next_payment_on = ($coverage->payment_term == 'monthly') ? Carbon::parse($last_payment)->addMonths(2) : Carbon::parse($last_payment)->addYears(2);

                    }
              
                }
               
               

            }else{
                $check_corp_cov = Coverage::where('product_id',$coverage->product_id)->where('owner_id',$coverage->owner->id)->orderBy('created_at', 'asc')->where('status','active')->first();
                if($check_corp_cov){
                    $now = now();
                    $first_pay =Carbon::parse($check_corp_cov->first_payment_on);
                    $diff_day = date_diff(date_create($now), date_create($first_pay));
                    $diff = $diff_day->format("%y");
                    $diff_month = $diff_day->format("%m") + 1;

                    if($diff_day->format("%y") < 1){
                       if($coverage->payment_term == 'monthly'){
                        //$month_next = $first_pay->addYear();
                        $month_next = $first_pay->addMonth($diff_month);
                        $coverage->next_payment_on =  $month_next;
                            }else{
                        $coverage->next_payment_on = $first_pay->addYear();
                            }
                        }else{
                            if($coverage->payment_term == 'monthly'){
                                $month_next = $first_pay->addYear($diff);
                                $month_next = $month_next->addMonth($diff_month);
                                $coverage->next_payment_on =  $month_next;
                                    }else{
                                $diff = $diff + 1;
                                $coverage->next_payment_on = $first_pay->addYear($diff);
                                    }
                                }
                   // $coverage->next_payment_on = ($coverage->payment_term == 'monthly') ? Carbon::parse($check_corp_cov->first_payment_on)->addMonth() : Carbon::parse($check_corp_cov->first_payment_on)->addYear();
               
                
               }else{
               
                $coverage->next_payment_on = ($coverage->payment_term == 'monthly') ? now()->addMonth() : now()->addYear();
               }
            }
            
            $renewal = Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('status','active')->first()->renewal_date ?? null;
            $first_payment = Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('status','active')->first()->first_payment_on ?? null;
            $first = Carbon::parse($first_payment);
            $now = now();
            $diff_day = date_diff(date_create($now), date_create($first));
            $diff = $diff_day->format("%y") + 1;

            if ($order->type==Enum::ORDER_TYPE_NEW){

                if($coverage->status == Enum::COVERAGE_STATUS_ACTIVE){
                    $coverage->renewal_date = now()->addYear();
                }elseif(empty($renewal)){
                    if($diff_day->format("%y") < 1){
                        $coverage->renewal_date = $first->addYear();
                    }else{
                        $coverage->renewal_date = $first->addYear($diff);
                    }
                }else{
                    $coverage->renewal_date = $renewal;
                }
            }   
            
            $coverage->first_payment_on = $coverage->first_payment_on ?? now();
        
            //$coverage->last_payment_on = now();
            if ($order->type==Enum::ORDER_TYPE_NEW){
                $coverage->last_payment_on = now();
                }else{
                         $parentcov =Coverage::where('id',$coverage->parent_id)->first();
                         $coverage->last_payment_on =   $parentcov->next_payment_on;
                    
                }
        
            $coverage->save();
        //}
        if($order->type==Enum::ORDER_TYPE_RENEW){
               if($coverage->product_id ==5){
                    $active_med_coverages =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])
                    ->get()->filter(function ($item){

                    $current_nxt_diff = date_diff(date_create(now()),date_create(Carbon::parse($item->next_payment_on)));
                    $current_nxt_diff =$current_nxt_diff->invert? -$current_nxt_diff->format('%a'):$current_nxt_diff->format('%a');
                        
                    if($current_nxt_diff <= 0){
                        return $item;
                    }
                   });
                  // dd($active_med_coverages);
                   Coverage::changeCoveragesToInactive($active_med_coverages);
                 }
                }
            $coverage->thanksgivings()->attach($thanksgivingsArray);
        }
         

     
        if($this->from_ops){
            foreach ($order->coverages as $coverage){
                $parent = Coverage::where('id',$coverage->parent_id)->first();
                $coverage->next_payment_on =$parent->next_payment_on;
                $coverage->renewal_date =$parent->renewal_date;
                $coverage->first_payment_on =$parent->first_payment_on;
                $coverage->save();


            }
        }

        event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_ACTIVE));
        
        
        Credit::createDepositCharity($this->owner_user_id,$order);

        if($order->getCreditByThanksgiving(Enum::THANKSGIVING_TYPE_SELF)->count() > 0){
            Credit::createWithdrawSelf($this->owner_user_id,$order);
        }

        $thanksgivingPromoter = Thanksgiving::where('individual_id',$this->owner_id)->where('type',Enum::THANKSGIVING_TYPE_PROMOTER)->first();
        if(!empty($thanksgivingPromoter)){
            Credit::createDepositPromoter($this->owner_user_id,$order);
        }

        if($order->type ==Enum::ORDER_TYPE_NEW){
            $this->beneficiary();
        }

     

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
    public function beneficiary(): void
    {  
      
		$email_validation = Coverage::where("payer_id",$this->owner_user_id)->where("product_id",1)->orderBy("created_at","desc")->get()->first();
        if(!empty($email_validation)){
        $beneficiaries = Beneficiary::where('individual_id', $this->owner_id);
               
        foreach ($beneficiaries->get() as $beneficiary) {
            if($beneficiary->status == "registered"){
               
                if (!$beneficiary->isCharity()) {
                    if($email_validation->status =='active' && $email_validation->state =='active' && $email_validation->first_payment_on != null){
                        $data['title'] = __('web/messages.ben_email_title');
                        $data['subject'] = __('web/messages.ben_email_subject');
                        $textEmail  = __('web/messages.nominee_text_notification_email',['nominee' => $beneficiary->name, 'nominator' => $beneficiary->individual->name]);
                
                        Notification::route('mail',$beneficiary->email)->notify(new Email($textEmail, $data));   
                    
                    }
                }
            
            }
          
            elseif (!$beneficiary->isCharity()) { 
                 if($email_validation->status =='active' && $email_validation->state =='active' && $email_validation->first_payment_on != null){
 
                    $data['title'] = __('web/messages.ben_email_title');
	            $data['subject'] = __('web/messages.ben_email_subject');
                    $emailText = __('mobile.payment_success_nominee', ['nominee' => $beneficiary->name, 'nominator' => $beneficiary->individual->name]);
                    
                    Notification::route('mail', $beneficiary->email)->notify(new Email($emailText, $data));
                }
            }
        }
       //Dev 505 Email - Hide the email of Assignment for DearTime-Charity Fund 
       /* $beneficiaryCharity = $beneficiaries->where('type',Enum::BENEFICIARY_TYPE_HIBAH)
            ->where('status',Enum::BENEFICIARY_STATUS_PENDING)
            ->where('percentage','>',0)->first();
        if(!empty($beneficiaryCharity)){
            $emailText = __('mobile.send_assignment_mail');
            $beneficiaryCharity->status = Enum::BENEFICIARY_STATUS_SENT_EMAIL;
            $beneficiaryCharity->save();
            $path = resource_path(config('static.assignment_form'));
            Notification::route('mail', $beneficiaryCharity->individual->user->email)->notify(new Email($emailText,[
                'buttons'=>[['text'=>__('mobile.download'), 'link'=>route('download.resource', encrypt($path))]
                ],
            ]));
        }*/
        }
    }
}