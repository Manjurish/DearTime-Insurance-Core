<?php     

namespace App\Listeners;

use App\Helpers\Enum;
use App\Notifications\Email;
use App\Notifications\Sms;
use App\Beneficiary;
use App\Coverage;
use App\Referral;
use App\Credit;
use App\User;
use App\Thanksgiving;
use App\SpoCharityFunds;
use App\Individual;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class ChangedCoveragesStatusListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch ($event->status){
            case Enum::COVERAGE_STATUS_ACTIVE_INCREASED:
            case Enum::COVERAGE_STATUS_ACTIVE:
                $this->activeStatus($event->coverages,$event->order);
                break;
            case Enum::COVERAGE_STATUS_GRACE_UNPAID:
                $this->graceStatus($event->coverages,$event->order);
                break;
            case Enum::COVERAGE_STATUS_CANCELLED:
                $this->canceledStatus($event->coverages,$event->order);
                break;
            case Enum::COVERAGE_STATUS_UNPAID:
                $this->unpaidStatus($event->coverages);
                break;
            case Enum::COVERAGE_STATUS_PENDING:
                $this->pendingStatus($event->coverages);
                break;
            case Enum::COVERAGE_STATUS_DECREASE_UNPAID:
                $this->decreasedUnpaidStatus($event->coverages,$event->order);
                break;
            case Enum::COVERAGE_STATUS_EXPIRED:
                $this->expiredStatus($event->coverages);
                break;
            case Enum::COVERAGE_STATUS_FULFILLED:
            case Enum::COVERAGE_STATUS_FULFILLED_INCREASE:
                $this->fulfilledStatus($event->coverages,$event->order);
                break;

        }
    }

    public function activeStatus($coverages,$order){
        //$payer = $order->payer;
        $payer = $order->coverages()->first()->owner->user;
        $originalPayer = $order->coverages()->first()->payer->profile->user;
        $coverage_refno = '';
        $coveragesOwned = $payer->profile->coverages_owner->where('state',Enum::COVERAGE_STATE_ACTIVE);
        $data['title'] = __('web/messages.order_email_title');
        $data['subject'] = __('web/messages.order_email_subject');
        $translateData = ['amount'=>$order->amount,'trx'=>$order->transactions()->latest()->first()->transaction_id,'user'=>$originalPayer->name,'coverages'=>''];
        $plainText = __('mobile.paid_order',['amount'=>$order->amount,'trx'=>$order->transactions()->latest()->first()->transaction_id,'user'=>$originalPayer->name]);
        $emailText = __('web/order.paid_order',['amount'=>$order->amount,'trx'=>$order->transactions()->latest()->first()->transaction_id,'user'=>$originalPayer->name]);
        //$translateDatapayor = ['payor_name'=>$originalPayer->name,'owner_name'=>$payer->name,'amount'=>$order->amount,'mode'=>$order->coverages()->first()->payment_term,'coverages'=>''];
       // $plainTextpayor = __('mobile.paid_order_payor',['payor_name'=>$originalPayer->name,'owner_name'=>$payer->name,'amount'=>$order->amount,'mode'=>$order->coverages()->first()->payment_term,'coverages'=>'']);

        foreach ($coverages ?? [] as $coverage) {
            $translateData['coverages'] .=($coverage->product_name =='Medical'?(__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0")):(__('mobile.'.$coverage->product_name).": RM".( $coverage->coverage) ?? "0"))."\n";
            $plainText .= ($coverage->product_name =='Medical'?__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0"):__('mobile.'.$coverage->product_name).": RM".($coverage->coverage) ?? "0")."\n";
            $emailText .= ($coverage->product_name =='Medical'?__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0"):__('mobile.'.$coverage->product_name).": RM".($coverage->coverage) ?? "0"). "<br/>";
            //$plainTextpayor .= ($coverage->product_name =='Medical'?__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0"):__('mobile.'.$coverage->product_name).": RM".($coverage->coverage) ?? "0")."\n";
            $coverage_refno.=$coverage->ref_no.",";
            $policy =Coverage::where('owner_id',$payer->profile->id)->where('ref_no',$coverage->ref_no)->latest()->first();
            $policy->policy_issuance_date = Carbon::now();
            $policy->save();
            $order_created= strtotime($order->created_at);
            $policyissuance=strtotime($policy->policy_issuance_date);
            $policy->time_diff_issuance =($policyissuance - $order_created );
            $policy->save();
            
            $coverage_owner = Coverage::where('owner_id',$payer->profile->id)->where('payer_id',$payer->profile->user_id)->where('product_name',$coverage->product_name)->where('status','unpaid')->update(['status'=>Enum::COVERAGE_STATUS_TERMINATE]);

            if($order->payer->profile->id != $coverage->owner_id){
                $active_cov = Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('state','active')->get()->sum('coverage');
                if($coverage->product_id != 3 && $coverage->product_id != 5 ){
                $maxcov = $coverage->product->maxCoverage($coverage->owner);
                if($active_cov == $maxcov){
                    Coverage::where('owner_id',$payer->profile->id)->where('payer_id','!=',$order->payer->id)->where('product_name',$coverage->product_name)->whereIn('status',['unpaid','increase-unpaid'])->update(['status'=>Enum::COVERAGE_STATUS_TERMINATE]);
                 
                }
            }else{
                if($coverage->product_id == 3){
                   $death_active =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',1)->where('state','active')->get()->sum('coverage');
                  // $payer_unpaid =Coverage::where('owner_id',$coverage->owner_id)
                   $acc_active_cov = Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('state','active')->get()->sum('coverage');
                   $acc_unpaid =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])->get();
                   if($acc_unpaid->isNotEmpty()){
                    foreach($acc_unpaid as $acc){
                       $death_unpaid =Coverage::where('owner_id',$coverage->owner_id)->where('payer_id',$acc->payer_id)->where('product_id',1)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])->get();
                       if($death_unpaid->isNotEmpty()){
                       $acc_limit =  $death_active + $death_unpaid->sum('coverage');
                    }else{
                        $acc_limit = $death_active;
                    }
                    if($acc_limit ==  $acc_active_cov){
                        Coverage::where('owner_id',$payer->profile->id)->where('payer_id',$acc->payer_id)->where('product_name',$coverage->product_name)->whereIn('status',['unpaid','increase-unpaid'])->update(['status'=>Enum::COVERAGE_STATUS_TERMINATE]);
    
                       }
                   }
                }
                   
                }else{
                   $med_active = Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('state','active')->get();
                   if(!empty($med_active)){
                    
                    Coverage::where('owner_id',$payer->profile->id)->where('payer_id','!=',$order->payer->id)->where('product_name',$coverage->product_name)->whereIn('status',['unpaid','increase-unpaid'])->update(['status'=>Enum::COVERAGE_STATUS_TERMINATE]);

                   }
                }
            }
            }

        }
        $payer->sendNotification('mobile.we_got_you_covered', 'mobile.paid_order_notification', ['command' => 'next_page', 'data' => 'policies_page','translate_data'=>$translateData]);
        $payer->notify(new Sms($plainText));
        $payer->notify(new Email($emailText, $data));
        if($payer->id != $originalPayer->id ) {
          
                $locale = $originalPayer->locale;
                App::setLocale($locale ?? 'en');
                $data_payor['subject']=__('web/messages.order_email_subject_payor');
                $emailTextpayor = __('web/order.paid_order_payor',['payor_name'=>$originalPayer->name,'owner_name'=>$payer->name,'amount'=>$order->amount,'mode'=>$order->coverages()->first()->payment_term =='monthly' ? __('mobile.pay_monthly'):__('mobile.pay_yearly')]);
                $translateDatapayor = ['payor_name'=>$originalPayer->name,'owner_name'=>$payer->name,'amount'=>$order->amount,'mode'=>$order->coverages()->first()->payment_term =='monthly' ? __('mobile.pay_monthly'):__('mobile.pay_yearly'),'coverages'=>''];

                foreach ($coverages ?? [] as $coverage) {
                    $translateDatapayor ['coverages'] .=($coverage->product_name =='Medical'?(__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0")):(__('mobile.'.$coverage->product_name).": RM".( $coverage->coverage) ?? "0"))."\n";
                    $emailTextpayor .= ($coverage->product_name =='Medical'?__('mobile.medical_deductible').": RM".( $coverage->coverage ?? "0"):__('mobile.'.$coverage->product_name).": RM".($coverage->coverage) ?? "0"). "<br/>";

                }

                $originalPayer->sendNotification('mobile.order_success_title_payor', 'mobile.paid_order_payor', ['command' => 'next_page', 'data' => 'policies_page','translate_data'=>$translateDatapayor]);
                $originalPayer->notify(new Email($emailTextpayor, $data_payor));
               
            
            

        }
        $locale = $payer->locale;
        App::setLocale($locale ?? 'en');

        $user_id=Individual::where('id',$payer->profile->id)->first()->user_id;
        $thanksgiving=Thanksgiving::where('individual_id',$payer->profile->id)->where('type','charity')->latest()->first()->percentage;
        if($thanksgiving){
            $sop_fund= new SpoCharityFunds;
            $sop_fund->user_id =$user_id;
            $sop_fund->order_id=$order->id;
            $sop_fund->transaction_id= $order->transactions()->latest()->first()->id;
            $sop_fund->transactions_no=$order->transactions()->latest()->first()->transactions_ref;
            $sop_fund->amount =$order->amount;
            $sop_fund->percentage =$thanksgiving;
            $sop_fund->charity_fund=($order->true_amount*($thanksgiving/1000));
            if($payer->profile->freelook()){
            $sop_fund->status ='ON HOLD';
            }else{
            $sop_fund->status ='ADDED';
            }
            $sop_fund->save();
            }

         $coverage_ow = Individual::where('id',$coverage->owner_id)->first()->user_id;
         $user = User::where('id',$coverage_ow)->first();
       //$user=auth()->user();
        if($user->from_referrer != NULL){
            $user_id = Individual::where('user_id',$user->id)->first();
            $from_referrer = User::where('id',$user->from_referrer)->first()->id;
            $name = $user->from_referrer_name;
            $individual_id = Individual::where('user_id',$user->id)->first()->id;
            $thanksgiving = Thanksgiving::where('individual_id',$payer->profile->id)->where('type','promoter')->latest()->first();
            $order_id = $order->id;
            $percentage = Thanksgiving::where('individual_id',$payer->profile->id)->where('type','promoter')->latest()->first()->percentage;
            if($thanksgiving){
             $referrallist = new Referral;
             $referrallist->from_referrer = $from_referrer;
             $referrallist->to_referee = $user->id;
             $referrallist->from_referral_name = $name;
             $referrallist->to_referee_name = $user->profile->name;
             $referrallist->amount = round($order->true_amount*($percentage/1000),2);
             $referrallist->thanksgiving_percentage = $percentage;
             if($payer->profile->freelook()){
                $referrallist->payment_status ='ON HOLD';
                }else{
                $referrallist->payment_status ='PENDING';
                }
             $referrallist->transaction_ref = '';
             $referrallist->order_id = $order_id;
             $referrallist->month = Carbon::now()->getTranslatedMonthName();
             $referrallist->year  = Carbon::now()->format('Y');
             $referrallist->transaction_date  = '';
             $referrallist->save();
            }
        }
/***************** Dev 707 - Notification Email for Servicing Team (Increase of Coverage Amount) *****************/

$coverage_id = $coverages[0]->covered_id;

$nominees  = Beneficiary::where("individual_id",$coverage_id)->get();

$trustee_name = "";
$trustee_email = "";
$trustee_mobile = "";
$trustee_nric = "";
$trustee_mobile3 = "";
$trustee_mobile4 = "";

foreach ($nominees as $nominee) {
    
    if(($nominee->relationship == 'child') || ($nominee->relationship == 'parent') || ($nominee->relationship == 'spouse'))
    {

        $trustee_name = $trustee_name.",".$nominee->name;
        $trustee_email = $trustee_email.",".$nominee->email;
        $trustee_nric = $trustee_nric.",".$nominee->nric;

        if($nominee->nominee_id != null)
        {

            $trustee_mobile_2 = Individual::where("id",$nominee->nominee_id)->first();
            
            $trustee_mobile = $trustee_mobile.",".$trustee_mobile_2->mobile;

        }
        else{
            $trustee_mobile3 = '-';

            $trustee_mobile = $trustee_mobile.",".$trustee_mobile3;   
        }

        $trustee_mobile4 = $trustee_mobile;
    }
    
}

$trustee_mobile4 = trim($trustee_mobile4, ",");
$trustee_name = trim($trustee_name, ",");
$trustee_email = trim($trustee_email, ",");
$trustee_nric = trim($trustee_nric, ",");
$coverage_refno = trim($coverage_refno, ",");

/************************************* Email Sending ********************************************************/

    if(($trustee_name != null) ||($trustee_name != ''))
    {

        $data['title'] = __('web/messages.coverage_increase_servicingteam_email_title');

        $data['subject'] = __('web/messages.coverage_increase_servicingteam_email_subject',['owner_name' => $payer->profile->name, 'coverage_ref_no' => $coverage_refno]);

        $content = __('web/messages.coverage_increase_servicingteam_email_content',['coverage_ref_no' => $coverage_refno,'owner_name' => $payer->profile->name,'email_id' => $payer->email,'mobile_no' => $payer->profile->mobile, 'nric_no' => $payer->profile->nric, 'trustee_name' => $trustee_name, 'trustee_email_id' => $trustee_email, 'trustee_mobile_no' => $trustee_mobile4, 'trustee_nric_no' => $trustee_nric]);

        $email = __('mobile.test_recipient');

        try {
            Notification::route('mail', $email)->notify(new Email($content, $data));
        }catch (\Exception $e){
        }

    }
/************************************* Email Sending ********************************************************/

/***************** Dev 707 - Notification Email for Servicing Team (Increase of Coverage Amount) *****************/  

        //$this->sendForOwner($coverages,$payer,$plainText,'mobile.we_got_you_covered','mobile.paid_order_notification',$emailText,'paid_order',$translateData);
    }

    public function graceStatus($coverages,$order){
        //$payer = $order->payer;
        $payer = $order->coverages()->first()->owner->user;
        $day =  Carbon::parse($order->next_try_on)->diffInDays(Carbon::now(),false);
        $messageTitle = '';
        $messageBody = '';
        $messageKey = '';
        $translateData = [];
        if($day == -2){
            $messageTitle = 'notification.auto_billing_notice.title';
            $translateData = ['amount'=>$order->amount,'date'=>Carbon::parse($order->due_date)->format('dd/mm/yyyy')];
            $messageKey = 'notification.auto_billing_notice.body';
            $messageBody = __($messageKey,['amount'=>$order->amount,'date'=>Carbon::parse($order->due_date)->format('dd/mm/yyyy')]);
        }
        else{
            if($order->retries == 4){
                $messageTitle = 'notification.1st_attempt_failed.title';
                $translateData = ['amount'=>$order->amount,'date'=>Carbon::parse($order->next_tra_on)->format('dd/mm/yyyy')];
                $messageKey = 'notification.1st_attempt_failed.body';
                $messageBody = __($messageKey,['amount'=>$order->amount,'date'=>Carbon::parse($order->next_tra_on)->format('dd/mm/yyyy')]);
            }
            if($order->retries == 2){
                $messageTitle = 'notification.3rd_attempt_failed.title';
                $translateData = ['amount'=>$order->amount,'date'=>Carbon::parse($order->next_tra_on)->format('dd/mm/yyyy')];
                $messageKey = 'notification.3rd_attempt_failed.body';
                $messageBody = __($messageKey,['amount'=>$order->amount,'date'=>Carbon::parse($order->next_tra_on)->format('dd/mm/yyyy')]);
            }
        }

        if($messageTitle != '' && $messageBody != ''){
            $payer->sendNotification($messageTitle, $messageKey, ['command' => '', 'data' => '','translate_data'=>$translateData]);
            $payer->notify(new Sms($messageBody));

            $this->sendForOwner($coverages,$payer,$messageBody,$messageTitle,null,$messageKey,$translateData);
        }

    }

    public function canceledStatus($coverages,$order){
        //$payer = $order->payer;
        $payer = $order->coverages()->first()->owner->user;
        $translateData = ['coverages'=>''];
        $plainText = __('notification.coverage_canceled.body');
        $emailText = $plainText."<br/>";
        foreach ($coverages ?? [] as $coverage) {
            $translateData['coverages'] .=(':'.$coverage->product_name??'').": RM".($coverage->coverage ?? "0")."\n";
            $plainText .= ($coverage->product_name ?? '') . ': RM' . ($coverage->coverage ?? '0') . "\n";
            $emailText .= ($coverage->product_name ?? '') . ': RM' . ($coverage->coverage ?? '0') . "<br/>";
        }
        $payer->sendNotification('notification.coverage_canceled.title', 'notification.coverage_canceled.body', ['command' => '', 'data' => '','translate_data'=>$translateData]);
        $payer->notify(new Sms($plainText));
        $payer->notify(new Email($emailText));

        $this->sendForOwner($coverages,$payer,$plainText,'notification.coverage_canceled.title',$emailText,'notification.coverage_canceled.body',$translateData);
    }

    public function unpaidStatus($coverages){
        $plainText = __('notification.coverage_unpaid.body');
        $emailText = $plainText."<br/>";
        foreach ($coverages as $coverage) {
            $coverage->payer->sendNotification('notification.coverage_unpaid.title', 'notification.coverage_unpaid.body', ['command' => '', 'data' => '']);
            $coverage->payer->notify(new Email($emailText));
        }
    }

    public function pendingStatus($coverages){
        $plainText = __('notification.coverage_expire.body');
        $emailText = $plainText."<br/>";
        foreach ($coverages as $coverage) {
            $coverage->payer->sendNotification('notification.coverage_expire.title', 'notification.coverage_expire.body', ['command' => '', 'data' => '']);
            $coverage->payer->notify(new Email($emailText));
        }
    }

    public function decreasedUnpaidStatus($coverages,$order){

    $coverage_refno = '';

    //$payer = $order->payer;
    $payer = $order->coverages()->first()->owner->user;
    
        foreach ($coverages as $coverage) {
            $translateData['coverages'] = ':'.$coverage->product_name;
            $coverage->payer->sendNotification('notification.coverage_decreased.title', 'notification.coverage_decreased.body', ['command' => '', 'data' => '','translate_data'=>$translateData]);
            $emailText = __('notification.coverage_decreased.body',['coverage'=>__('mobile.'.$coverage->product_name)]);
            $coverage->payer->notify(new Email($emailText));
            $coverage_refno.=$coverage->ref_no.",";
        }
        
/***************** Dev 707 - Notification Email for Servicing Team (Decrease of Coverage Amount) *****************/
    
    $coverage_id = $coverages[0]->covered_id;

    $nominees  = Beneficiary::where("individual_id",$coverage_id)->get();


    $trustee_name = "";
    $trustee_email = "";
    $trustee_mobile = "";
    $trustee_nric = "";
    $trustee_mobile3 = "";
    $trustee_mobile4 = "";

    foreach ($nominees as $nominee) {
        
        if(($nominee->relationship == 'child') || ($nominee->relationship == 'parent') || ($nominee->relationship == 'spouse'))
        {

            $trustee_name = $trustee_name.",".$nominee->name;
            $trustee_email = $trustee_email.",".$nominee->email;
            $trustee_nric = $trustee_nric.",".$nominee->nric;

            if($nominee->nominee_id != null)
            {

                $trustee_mobile_2 = Individual::where("id",$nominee->nominee_id)->first();
                
                $trustee_mobile = $trustee_mobile.",".$trustee_mobile_2->mobile;

            }
            else{
                $trustee_mobile3 = '-';

                $trustee_mobile = $trustee_mobile.",".$trustee_mobile3;   
            }

            $trustee_mobile4 = $trustee_mobile;
        }
        
    }

    $trustee_mobile4 = trim($trustee_mobile4, ",");
    $trustee_name = trim($trustee_name, ",");
    $trustee_email = trim($trustee_email, ",");
    $trustee_nric = trim($trustee_nric, ",");
    $coverage_refno = trim($coverage_refno, ",");

    /************************************* Email Sending ********************************************************/

    if(($trustee_name != null) ||($trustee_name != ''))
    {

            $data['title'] = __('web/messages.coverage_decrease_servicingteam_email_title');

			$data['subject'] = __('web/messages.coverage_decrease_servicingteam_email_subject',['owner_name' => $payer->profile->name, 'coverage_ref_no' => $coverage_refno]);

			$content = __('web/messages.coverage_decrease_servicingteam_email_content',['coverage_ref_no' => $coverage_refno,'owner_name' => $payer->profile->name,'email_id' => $payer->email,'mobile_no' => $payer->profile->mobile, 'nric_no' => $payer->profile->nric, 'trustee_name' => $trustee_name, 'trustee_email_id' => $trustee_email, 'trustee_mobile_no' => $trustee_mobile4, 'trustee_nric_no' => $trustee_nric]);

			$email = __('mobile.test_recipient');

			try {
				Notification::route('mail', $email)->notify(new Email($content, $data));
			}catch (\Exception $e){
			}
    }

    /************************************* Email Sending ********************************************************/

    // echo (" Trustee Name : ".$trustee_name." | Trustee Email : ".$trustee_email." | Trustee Mobile : ".$trustee_mobile." | Trustee NRIC : ".$trustee_nric);

/***************** Dev 707 - Notification Email for Servicing Team (Decrease of Coverage Amount) *****************/        
        
    }

    public function expiredStatus($coverages){
        $plainText = __('notification.coverage_expired.body');
        $emailText = $plainText."<br/>";
        foreach ($coverages as $coverage) {
            $coverage->payer->sendNotification(__('notification.coverage_expired.title'), $plainText, ['command' => '', 'data' => '']);
            $coverage->payer->notify(new Email($emailText));
        }

    }

    public function fulfilledStatus($coverages,$order){
        //$payer = $order->payer;
        $payer = $order->coverages()->first()->owner->user;
        $plainText = __('notification.coverage_fulfilled.body');
        $emailText = $plainText."<br/>";
        $translateData = ['coverages'=>''];
        foreach ($coverages ?? [] as $coverage) {
            $translateData['coverages'] .=(':'.$coverage->product_name??'').": RM".($coverage->coverage ?? "0")."\n";
            $plainText .= ($coverage->product_name ?? '') . ': RM' . ($coverage->coverage ?? '0') . "\n";
            $emailText .= ($coverage->product_name ?? '') . ': RM' . ($coverage->coverage ?? '0') . "<br/>";
        }
        $payer->sendNotification('notification.coverage_fulfilled.title', 'notification.coverage_fulfilled.body', ['command' => '', 'data' => '','translate_data'=>$translateData]);
        $payer->notify(new Sms($plainText));
        $payer->notify(new Email($emailText));

        $this->sendForOwner($coverages,$payer,$plainText,__('notification.coverage_fulfilled.title'),$emailText,'notification.coverage_fulfilled.body',$translateData);
    }

    public function sendForOwner($coverages,$payer,$plainText,$noticeTitle,$emailText=null,$translateText='',$translateData=[]){
        if ($payer->isIndividual()) {
            $payer_profile_id = $payer->profile->id ?? 0;
        } else {
            $payer_profile_id = 0;
        }

        foreach ($coverages ?? [] as $coverage) {
            if (($coverage->owner_id ?? 0) != $payer_profile_id && !empty($coverage->owner->user)) {
                $owner = $coverage->owner->user;
                $owner->sendNotification($noticeTitle, $translateText, ['command' => 'next_page', 'data' => 'policies_page','translateData'=>$translateData]);
                $owner->notify(new Sms($plainText));
                if($emailText != null){
                    $owner->notify(new Email($emailText));
                }

            }
        }
    }

}