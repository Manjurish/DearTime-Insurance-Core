<?php      
 // ALL RIGHTS RESERVED Ã‚Â® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Helpers\Enum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\UwsLoading;
use App\Uw;
use App\VoucherDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\juvenileBmi;

class Product extends Model
{

    public $product_payer_id; 
    
    public function quickQuoteFor($user = null, $coverage = -1, $deathCoverage = null,$payer = null, $ddct = null,$decrease_cov = false,$diff_med = null) 
    {

        if(!empty($ddct)){
            switch($ddct){
                case 0:
                    $ddct = 0;
                break;
                case 1:
                    $ddct = 500;
                break;
                case 2:
                    $ddct = 1000;
                break;
                case 3:
                    $ddct = 2000;
                break;
                case 4:
                    $ddct = 5000;
                break;
                case 5:
                    $ddct = 10000;
                break;
            }
        }

        $options = json_decode($this->options);

        if(empty($user->user))
            $is_individual = true;
        else
            $is_individual = empty($user->user) ?  true : $user->user->isIndividual();

        if (!$user)
            $user = Auth::user()->profile;


        if (!$is_individual) { // group insurance using preset values
            $user = new Individual();
            $user->dob = Carbon::now()->subYears(30);
            $user->gender = 'Male';
            $user->occ = 1140; // Manager - should have no loading
        }

        $uw = $user->underwritings;
        $charity = $user->is_charity();

        $min_coverage = 0;

        $max_coverage = 0;
        $coverage_with_loading = $coverage;
        $message_bag = null;
        $covstatus = null;
        $allowed = true;
        $restrict_increase =false;
        $med_flag = $diff_med;
        
        $Vouchercheck = VoucherDetails::where("email",$user->user->email)->first();
        //dd($Vouchercheck);
        if(!empty($Vouchercheck) && $user->occ==null){
            $occ_loading='0';
        }


        switch ($this->name) {
            case 'Death' :
                $has_death = Coverage::where("covered_id",$user->id)->where("product_name","Death")->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
                
            
                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                $max_coverage = $this->maxCoverage($user);

                /*if ($uw && !$uw->death)
                    $max_coverage = $options->guaranteed_acceptance;*/

                $numberCoverage = $this->getCountActiveCoverageByProduct($user);

                $allowed = ($numberCoverage == 0) ? ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age)) : true;
                //$allowed = ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age));
              
                if (!$allowed && $user->age()>$options->max_age){
                     // $coverage = 0;
                     list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                      $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                 } 

                if ($user->age()>$options->max_age && $has_death){
                    $death= Coverage::where("covered_id",$user->id)->where("product_name","Death")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                    if(($decrease_cov? $coverage:($coverage+$death))>$death){
                        $coverage = $death;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                        $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }


    
              }else if($user->age()>$options->max_age && !$has_death){
                $coverage = 0;
                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                
            }
                if(!empty($has_death)){
                    $covstatus =$has_death->status;
                    // Underwriting failed 

$death= Coverage::where("covered_id",$user->id)->where("product_name","Death")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
if($this->product_payer_id != $user->user_id){
    $death= Coverage::where("covered_id",$user->id)->where('payer_id',$this->product_payer_id )->where("product_name","Death")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

}                     
if ( !empty($death) && ($decrease_cov? $coverage:($coverage+$death))>$death &&  ($this->UwChecking($uw,'death',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'death',$user)==true)){
   
    if(!empty($payer)){
        Coverage::where(['covered_id'=>$user->id,'payer_id'=> $this->product_payer_id,'status' => Enum::COVERAGE_STATUS_INCREASE_UNPAID,'product_name'=>'Death'])->update(['status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE]);

       }
    $allowed     = FALSE;
    $coverage = $death;
    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
    $message_bag = !empty($has_death) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
}


                   
                }



                $restrict_increase =  ($this->UwChecking($uw,'death',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'death',$user)==true || (($user->age()>$options->max_age && $has_death)));
				$this->checkAllowWithUw($uw);
                if($this->checkAllowWithUwLoading($uw,'death',$deathCoverage,$user))
                {
                Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Death'])->update(['status' => 'terminate']);

                 $coverage = 0;
                 $allowed     = FALSE;
                 $price = 0;
                 $message_bag = !empty($has_death) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }

				break;
            case 'Disability' :
            
                $has_tpd = Coverage::where("covered_id",$user->id)->where("product_name","Disability")->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
                

                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);

                $max_coverage = $this->maxCoverage($user);

               /*if ($coverage > $max_coverage) {
                    $coverage = $max_coverage;
                    $price = $this->getPrice($user, $coverage, $occ_loading);
                    $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }*/

                if ($occ_loading > -1) {
              //      $price = $price * $occ_loading;
                } else {
                    $coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $message_bag = __('web/server.occ_product_reject');
                }

                /*if ($uw && !$uw->disability)
                    $max_coverage = $options->guaranteed_acceptance;*/

                $numberCoverage = $this->getCountActiveCoverageByProduct($user);

                $allowed = ($numberCoverage == 0) ? ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1)) : true;
                //$allowed = ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1));

               if (!$allowed && $user->age()>$options->max_age){
                    //$coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                } 

               if ($user->age()>$options->max_age&& $has_tpd){
                    $disability= Coverage::where("covered_id",$user->id)->where("product_name","Disability")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                    if(($decrease_cov? $coverage:($coverage+$disability))>$disability ||  $this->UwChecking($uw,'disability',$deathCoverage,$user)==true){
                        $coverage = $disability;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                        //$message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }
              }else if($user->age()>$options->max_age && !$has_tpd){
                $coverage = 0;
                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                
            }



                if(!empty($has_tpd)){
                    
                    // Underwriting failed 

$disability= Coverage::where("covered_id",$user->id)->where("product_name","Disability")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

if($this->product_payer_id != $user->user_id){
    $disability= Coverage::where("covered_id",$user->id)->where('payer_id',$this->product_payer_id )->where("product_name","Disability")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

}  

if ( !empty($disability) && ($decrease_cov? $coverage:($coverage+$disability))>$disability &&  ($this->UwChecking($uw,'disability',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'disability',$user)==true)){
    if(!empty($payer)){
        Coverage::where(['covered_id'=>$user->id,'payer_id'=> $this->product_payer_id,'status' => Enum::COVERAGE_STATUS_INCREASE_UNPAID,'product_name'=>'Disability'])->update(['status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE]);

       }
    $allowed     = FALSE;
    $coverage = $disability;
    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
    $message_bag = !empty($has_tpd) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
}
                    
                    $covstatus =$has_tpd->status;
                    
                }
				/*if ($uw && !$uw->disability){
					$allowed = false;
					$message_bag = __('web/server.uw_failed_product');
				}*/
                $restrict_increase =($this->UwChecking($uw,'disability',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'disability',$user)==true || ($user->age()>$options->max_age&& $has_tpd));
				$this->checkAllowWithUw($uw);
             

                if($this->checkAllowWithUwLoading($uw,'disability',$deathCoverage,$user))
                {
                 Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Disability'])->update(['status' => 'terminate']);
                 
                 $coverage = 0;
                 $allowed     = FALSE;
                 $price = 0;
                 $message_bag = !empty($has_tpd) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }



                break;
            case 'Accident':
                $has_accident = Coverage::where("covered_id",$user->id)->where("product_name","Accident")->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
                

                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);

                $numberCoverage = $this->getCountActiveCoverageByProduct($user);

                $allowed = ($numberCoverage == 0) ? ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1) && ($max_coverage > -1)) : true;
                //$allowed = ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1) && ($max_coverage > -1));

                if (!$allowed && $user->age()>$options->max_age){
                    //$coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                 } 

                //check if dun have death then cannot buy this one


                if ($occ_loading > -1) {
                //    $price = $price * $occ_loading;
                } else {
                    $coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $max_coverage = 10000;
                    $message_bag = __('web/server.occ_product_reject');
                    // if($this->checkAllowWithUwLoading($uw,'accident',$deathCoverage))
                    // {
                    // Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Accident'])->update(['status' => 'terminate']);
                    // }
                    break;
                }

                if ($deathCoverage) {
                    $max_coverage = min(3000000, ($deathCoverage ?? 1) * 1); //Accident MSA = 1x Death SA
                } else {
                    //$coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $max_coverage = 10000;
                    $message_bag = __('web/server.accident_choose_death');
                    // if($this->checkAllowWithUwLoading($uw,'accident',$deathCoverage))
                    // {
                    // Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Accident'])->update(['status' => 'terminate']);
                    // }
                    break;
                }

                if ($user->age()>$options->max_age && $has_accident){
                    $accident= Coverage::where("covered_id",$user->id)->where("product_name","Accident")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                    if(($decrease_cov? $coverage:($coverage+$accident))>$accident){
                        $coverage = $accident;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                        $message_bag =__('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }
              }else if($user->age()>$options->max_age && !$has_accident){
                $coverage = 0;
                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                
            }

            if(!empty($has_accident)){
                $covstatus =$has_accident->status;
            
                // Underwriting failed 

$accident= Coverage::where("covered_id",$user->id)->where("product_name","Accident")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
      if($this->product_payer_id != $user->user_id){

        $accident= Coverage::where("covered_id",$user->id)->where('payer_id',$this->product_payer_id )->where("product_name","Accident")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

          }              
               if ( !empty($accident) && ($decrease_cov? $coverage:($coverage+$accident))>$accident &&  ($this->UwChecking($uw,'accident',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'accident',$user)==true)){
               
                if(!empty($payer)){
                    Coverage::where(['covered_id'=>$user->id,'payer_id'=> $this->product_payer_id,'status' => Enum::COVERAGE_STATUS_INCREASE_UNPAID,'product_name'=>'Accident'])->update(['status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE]);
            
                   }
                        $allowed     = FALSE;
                        $coverage = $accident;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                        $message_bag = !empty($has_accident) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }
            


             
            
            }


				/*if ($uw && !$uw->death)
					$max_coverage = $options->guaranteed_acceptance;*/

				/*if ($uw && !$uw->death){
					$allowed = false;
					$message_bag = __('web/server.uw_failed_product');
				}*/

                $restrict_increase =($this->UwChecking($uw,'accident',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'accident',$user)==true || ($user->age()>$options->max_age && $has_accident));

				$this->checkAllowWithUw($uw);
                // $this->checkAllowWithUwLoading($uw,'accident');
                if($this->checkAllowWithUwLoading($uw,'accident',$deathCoverage,$user))
                {
                Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Accident'])->update(['status' => 'terminate']);

                 $coverage = 0;
                 $allowed     = FALSE;
                 $price = 0;
                 $message_bag = !empty($has_accident) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }
                

				break;
            case 'Critical Illness':
                $has_ci = Coverage::where("covered_id",$user->id)->where("product_name","Critical Illness")->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
               


                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);

                $max_coverage = $this->maxCoverage($user);

                $numberCoverage = $this->getCountActiveCoverageByProduct($user);

                $allowed = ($numberCoverage == 0) ? ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age)) : true;
                //$allowed = ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age));

                if (!$allowed && $user->age()>$options->max_age){
                    //$coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                 } 

                if ($user->age()>$options->max_age && $has_ci){
                    $ci= Coverage::where("covered_id",$user->id)->where("product_name","Critical Illness")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');
                    if(($decrease_cov? $coverage:($coverage+$ci))>$ci){
                        $coverage = $ci;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                        $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }

                }else if($user->age()>$options->max_age && !$has_ci){
                    $coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
                    
                }



                if(!empty($has_ci)){

                    // Underwriting failed 

$ci= Coverage::where("covered_id",$user->id)->where("product_name","Critical Illness")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

if($this->product_payer_id != $user->user_id){

    $ci= Coverage::where("covered_id",$user->id)->where('payer_id',$this->product_payer_id )->where("product_name","Critical Illness")->where('state', Enum::COVERAGE_STATE_ACTIVE)->sum('coverage');

      }                    
if ( !empty($ci) && ($decrease_cov? $coverage:($coverage+$ci))>$ci &&  ($this->UwChecking($uw,'critical_illiness',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'critical_illiness',$user)==true)){
    if(!empty($payer)){
        Coverage::where(['covered_id'=>$user->id,'payer_id'=> $this->product_payer_id,'status' => Enum::COVERAGE_STATUS_INCREASE_UNPAID,'product_name'=>'Critical Illness'])->update(['status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE]);

       }
    $allowed     = FALSE;
    $coverage = $ci;
    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, $coverage, $occ_loading, null, null, null, null, $diff_med);
    $message_bag = !empty($has_ci) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
}

                    $covstatus =$has_ci->status;
                    
                 }

                /*if ($uw && !$uw->ci) {
					$allowed = false;
					$message_bag = __('web/server.uw_failed_product');
				}*/

                $restrict_increase =($this->UwChecking($uw,'critical_illiness',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'critical_illiness',$user)==true || ($user->age()>$options->max_age && $has_ci));
				$this->checkAllowWithUw($uw);
                // $this->checkAllowWithUwLoading($uw,'critical_illiness');
                if($this->checkAllowWithUwLoading($uw,'critical_illiness',$deathCoverage,$user))
                {
                 Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Critical Illness'])->update(['status' => 'terminate']);

                 $coverage = 0;
                 $allowed     = FALSE;
                 $price = 0;
                 $message_bag = !empty($has_ci) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }
                


				break;
            case 'Medical':
                $occ_loading = $user->occupationJob->Medical;
                $deductible = $ddct ?? 500;


                //check if has already purchased medical
                $has_medical = Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state',Enum::COVERAGE_STATE_ACTIVE)->first();
                
                if(empty($payer))
                    $payer = $user;
                if(!empty($has_medical) && ($has_medical->payer_id != $payer->user_id && $payer->id != $user->id)){
                // The deductible was 500 and coverage was payor's coverage which we changed due to IPFO Flow on 14-May-2024 
                // $deductible     = 500;
                // $coverage       = ($payer->id == $user->id || empty($payer)) ? $has_medical->RealCoverage : 0;
                    $deductible     = 0;
                    $coverage       = 0;
                    $message_bag    = __('web/server.unable_to_purchase_medical');
                }


                list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, null, $occ_loading, null, $deductible, null, null, $diff_med);
                if ($occ_loading > -1) { // we already added loading in getPrice
//                    $price = $price * $occ_loading;
                } else {
                    $coverage = 0;
                    $price = 0;
                    $message_bag = __('web/server.occ_product_reject');
                }
                

                $max_coverage = $charity ? 100000 : 100000; // 200k for normal, 100k for charity

                $numberCoverage = $this->getCountActiveCoverageByProduct($user);

                $allowed = ($numberCoverage == 0) ? ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1)) : true;
                //$allowed = ($options->min_age <= $user->age(true) && ($user->age() <= $options->max_age) && ($occ_loading > -1));

                if (!$allowed && $user->age()>$options->max_age){
                    //$coverage = 0;
                   list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, null, $occ_loading, null, $deductible, null, null, $diff_med);
                   $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                } 
                
                /*if ($uw && !$uw->medical) {
                    $message_bag = __('web/server.uw_failed_product');
                    $allowed = false;
                }*/
                if ($user->age()>$options->max_age && $has_medical){
                    $medical= Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state', Enum::COVERAGE_STATE_ACTIVE)->orderBy('id','desc')->first()->real_coverage;
                    $covstatus =$has_medical->status;
                    if($coverage<$medical){
                        $coverage = $medical;
                        $deductible =Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state', Enum::COVERAGE_STATE_ACTIVE)->orderBy('id','desc')->first()->coverage;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, null, $occ_loading, null, $deductible, null, null, $diff_med);
                        $message_bag = __('web/server.coverage_more_than_max',['max'=>$max_coverage]);
                }

                }else if($user->age()>$options->max_age && !$has_medical){
                    $deductible=0;
                    $coverage = 0;
                    list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, null, $occ_loading, null, $deductible, null, null, $diff_med);
                   // $message_bag="test";
                    
                }


                if(!empty($has_medical)){
                  // Underwriting failed 

$medical= Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state', Enum::COVERAGE_STATE_ACTIVE)->orderBy('id','desc')->first()->real_coverage;

if ( !empty($medical) && $coverage<$medical &&  ($this->UwChecking($uw,'medical',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'medical',$user)==true)){
                       if(!empty($payer)){
                        Coverage::where(['covered_id'=>$user->id,'payer_id'=> $this->product_payer_id,'status' => Enum::COVERAGE_STATUS_INCREASE_UNPAID,'product_name'=>'Medical'])->update(['status' => Enum::COVERAGE_STATUS_INCREASE_TERMINATE]);

                       }

                        $coverage = $medical;
                        $deductible =Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state', Enum::COVERAGE_STATE_ACTIVE)->orderBy('id','desc')->first()->coverage;
                        $allowed     = FALSE;
                        list($price, $uw_price, $uw_percent,$baseamount) = $this->getPrice($user, null, $occ_loading, null, $deductible, null, null);
                    

                        $message_bag = !empty($has_medical) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
                }
            
                  
                    $covstatus =$has_medical->status;
                    
                  }

                  $restrict_increase =($this->UwChecking($uw,'medical',$deathCoverage,$user)==true || $this->smoke_height_weight($uw,'medical',$user)==true || ($user->age()>$options->max_age && $has_medical));

				$this->checkAllowWithUw($uw);

               if($this->checkAllowWithUwLoading($uw,'medical',$deathCoverage,$user))
               {
               Coverage::where(['covered_id'=>$user->id,'status' => 'unpaid','product_name'=>'Medical'])->update(['status' => 'terminate']);

                $allowed     = FALSE;
                $coverage = 0;
                $price = 0;
                $message_bag = !empty($has_medical) ? __('web/server.sio_product_reject_exist')  : __('web/server.sio_product_reject');
               }

                if($payer->id != $user->id)
                  $allowed = false;

                if(!empty($payer)){
                    $buyed = (int) Coverage::where("product_id",$this->id)->where("covered_id",$user->id)->where("status","active")->where("payer_id",'!=',$payer->user_id)->sum('coverage') ?? 0;
                    $buy_own = Coverage::where("product_id",$this->id)->where("covered_id",$user->id)->where("state","active")->where("payer_id",'!=',$payer->user_id)->latest()->first() ?? 0;
                    $min_coverage = $buyed;
                    if($buy_own){
                    if($buy_own->payer_id == $payer->user_id){
                        if($coverage < $min_coverage)
                         $coverage = $min_coverage;
                }
            }
            }

				break;
        }

        if(!empty($payer)){
            
            $buyed = (int) Coverage::where("product_id",$this->id)->where("covered_id",$user->id)->where("state","active")->where("payer_id",'!=',$payer->user_id)->sum('coverage') ?? 0;
            //dump($buyed);
            $min_coverage = $buyed;
            //if($coverage < $min_coverage)
                //$coverage = $min_coverage;
        }

        // validation from Coverage Moderation Action
        $coverageModerationAction = CoverageModerationAction::where('individual_id', $user->id)->where('product_id', $this->id)->latest()->first();

        if(!empty($coverageModerationAction)){
            if($coverageModerationAction->action == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE){
                $allowed = false;
                $message_bag = __('mobile.disallow_purchase_msg') ;
            }elseif($coverageModerationAction->action == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE){
				$max_coverage = Coverage::where('covered_id', $user->id)->where('product_id', $this->id)->where('state', Enum::COVERAGE_STATE_ACTIVE)->first()->coverage;
                /*if($this->name == 'Accident'){
                    // death id is 1
                    $max_coverage = Coverage::where('covered_id', $user->id)->where('product_id', 1)->where('status', Enum::COVERAGE_STATUS_ACTIVE)->first()->coverage;
                }else{
                    $max_coverage = Coverage::where('covered_id', $user->id)->where('product_id', $this->id)->where('status', Enum::COVERAGE_STATUS_ACTIVE)->first()->coverage;
                }*/
            }
        }

        //if(request()->json()->count() && request()->json('mode') == null){
            //$payer_unpaid_cov_amount = (int) Coverage::where("product_id",$this->id)->where("covered_id", $user->id)->whereIn("status",["unpaid","increase-unpaid"])->where("payer_id",'!=',$user->user_id)->sum('coverage') ?? 0;
            //$coverage   =   $coverage + $payer_unpaid_cov_amount;
            //$payment_annually = Coverage::where("product_id",$this->id)->where("covered_id", $user->id)->whereIn("status",["unpaid","increase-unpaid"])->where("payer_id",'!=',$user->user_id)->sum('payment_annually') ?? 0;
           // $price = $payment_annually + Helpers::round_up($price, 2);

        //}

        return [
            'is_charity'=>$charity,
			'allowed' => $allowed,
			'message' => $message_bag,
            'covstatus'  =>  $covstatus?? null,
			'coverage' => $coverage ?? '0',
			'deductible' => $deductible ?? '0',
			'max_coverage' => $max_coverage,
			'annually' => Helpers::round_up($price, 2),
			'min_coverage' => $min_coverage,
			'd_cov' => $deathCoverage,
			'monthly' => Helpers::round_up($price * 0.085, 2),
            'uw_price' => Helpers::round_up($uw_price, 2),
            'uw_percent' => $uw_percent,
            'without_loading'=> Helpers::round_up($baseamount,2),
            'restrict_increase'=>$restrict_increase,
            'med_flag'         =>$diff_med
   		];
    }
    public function getPrice($user, $coverage, &$occ_loading = null, $u_age = null, $deductible = null, $underwriting = null, $gender = null, $diff_med = null, $campaign=false)
    {
        $age    = $u_age  ?? $user->age();
        $gender = $gender ?? $user->gender;
        $uw = $underwriting ?? $user->underwritings;
        $answers    =   $uw->answers['answers'] ?? [];
        $uws_loading_percentage    =   (int) UwsLoading::where('product_id', $this->id)->whereIn('uws_id', $answers)->sum('percentage') ?? 0;

        // todo add ooc_loading
        switch ($this->name) {
            case 'Death' :
                //$occ_loading = $user->occupationJob->death;
                
                $occ_loading = $occ_loading ?? $user->occupationJob->death;
             //   $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender);
                $premium_rate = $campaign ? $this->campaignPremiumRateAnnually(/*$user,*/ $age, $gender) : $this->premiumRateAnnually(/*$user,*/ $age, $gender);
                $base_amount =($premium_rate)* $coverage/1000;

                if ($coverage > 0 && $occ_loading > 0)
                    $premium_rate += $occ_loading;

                $fprice      =   ($premium_rate * $coverage) / 1000;
                //$perPrice    =   $this->premiumRateAnnually(/*$user,*/ $age, $gender) * $coverage / 1000 * $uws_loading_percentage / 100;
                $perPrice    =   ($campaign ? $this->campaignPremiumRateAnnually(/*$user,*/ $age, $gender) : $this->premiumRateAnnually(/*$user,*/ $age, $gender)) * $coverage / 1000 * $uws_loading_percentage / 100;
                
                return [($fprice + $perPrice), $perPrice, $uws_loading_percentage,$base_amount];
                break;

            case 'Disability':
                //$occ_loading = $user->occupationJob->TPD;
               
                $occ_loading = $occ_loading ?? $user->occupationJob->TPD;
            
                $premium_rate = $campaign ? $this->campaignPremiumRateAnnually(/*$user,*/ $age, $gender) : $this->premiumRateAnnually(/*$user,*/ $age, $gender);
             // $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender);
                $base_amount =($premium_rate*$coverage/1000);

                    if ($coverage > 0 && $occ_loading > 0)
                        // $premium_rate *= $occ_loading;
    
                    $fprice      =   ($premium_rate * $coverage) / 1000;
                    $perPrice =  ($premium_rate * $coverage) / 1000 * ($occ_loading + $uws_loading_percentage/100);
                    return [$perPrice, $perPrice, $uws_loading_percentage,$base_amount];
                    break;

            case 'Accident':
                //$occ_loading = $user->occupationJob->Accident;
                
                $occ_loading = $occ_loading ?? $user->occupationJob->Accident;
             // $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender);
             $premium_rate = $campaign ? $this->campaignPremiumRateAnnually(/*$user,*/ $age, $gender) : $this->premiumRateAnnually(/*$user,*/ $age, $gender);
          
             
             $base_amount =($premium_rate*$coverage/1000);

                    if ($coverage > 0 && $occ_loading > 0)
                        // $premium_rate *= $occ_loading;
    
                    $fprice      =   ($premium_rate * $coverage) / 1000;
                    $perPrice = $premium_rate * ($coverage / 1000) * ($occ_loading + $uws_loading_percentage/100);  
                    return [$perPrice, $perPrice, $uws_loading_percentage,$base_amount];
                    break;

            case 'Critical Illness':

                $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender);
                #$fprice      =   ($this->premiumRateAnnually(/*$user,*/ $age, $gender) * $coverage) / 1000;
                #$perPrice    =   ($this->premiumRateAnnually(/*$user,*/ $age, $gender) * $coverage) / 1000 * (1 + $uws_loading_percentage / 100);
                #$base_amount =($premium_rate * $coverage) / 1000;
                //$premium_rate = $campaign ? $this->premiumRateAnnually(/*$user,*/ $age, $gender) : $this->campaignPremiumRateAnnually(/*$user,*/ $age, $gender);
          
                $fprice      =   ($premium_rate* $coverage) / 1000;
                $perPrice    =   ($premium_rate * $coverage) / 1000 * (1 + $uws_loading_percentage / 100);
                $base_amount =($premium_rate * $coverage) / 1000;
                
                
                return [$perPrice, $perPrice, $uws_loading_percentage,$base_amount];
                break;

            case 'Medical':
                //$occ_loading = $user->occupationJob->Medical;
               
                $occ_loading = ($occ_loading == 0 || $occ_loading==null) ? $user->occupationJob->Medical:$occ_loading;
                if($diff_med){
                $old_ded = Coverage::where("covered_id",$user->id)->where("product_name","Medical")->where('state', Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->deductible;
                $old_premium_rate = $this->premiumRateAnnuallyMedical(/*$user,*/ $age, $old_ded, $gender);
                $new_premium_rate = $this->premiumRateAnnuallyMedical(/*$user,*/ $age, $deductible, $gender);
                $price = $new_premium_rate - $old_premium_rate;
                }else{
                $price = $this->premiumRateAnnuallyMedical(/*$user,*/ $age, $deductible, $gender);
             
        
        }
                $base_amount = $price;
                if($occ_loading > -1)
                    // $price = $price * $occ_loading;
                    $perPrice    =   $price * ($occ_loading + $uws_loading_percentage / 100);
                    $ffprice     = $price * ($occ_loading + $uws_loading_percentage / 100);
                    return [$ffprice,$ffprice , $uws_loading_percentage,$base_amount];
        }
    }

    private function premiumRateAnnually(/*$user,*/ $age, $gender)
        {
            //$age = $u_age ?? $user->age();
    
            foreach ($this->options()->premium_rates as $pr) {
                if ($pr[0] == $age) {
                    if ($gender == Enum::INDIVIDUAL_GENDER_MALE) {
                        return $pr[1];
                    } else {
                        return $pr[2];
                    }
                }
            }
        }
    // Campaign premiumRate 
    
    private function campaignPremiumRateAnnually(/*$user,*/ $age, $gender)
        {
            //$age = $u_age ?? $user->age();
    
            foreach ($this->options()->campaign_uw_loading as $pr) {
                if ($pr[0] == $age) {
                    if ($gender == Enum::INDIVIDUAL_GENDER_MALE) {
                        return $pr[1];
                    } else {
                        return $pr[2];
                    }
                }
            }
        }
        
    private function premiumRateAnnuallyMedical(/*$user,*/  $age , $deductible, $gender){
        //$age = $u_age ?? $user->age();

        foreach($this->options()->plans as $plan){
            if($plan->deductible == $deductible)
            {
                foreach ($plan->premium_rates as $pr) {
                    if ($pr[0] == $age) {
                        if ($gender == Enum::INDIVIDUAL_GENDER_MALE) {
                            return $pr[1];
                        } else {
                            return $pr[2];
                        }
                    }
                }
                break;
            }
        }

    }
    public function options()
    {
        return json_decode($this->options);
    }
    
    public function maxCoverage($user)
    {
        $options = $this->options();
        $charity = $user->is_charity();

        // student 1067
        // housewife 618
        // househusband 617
        // retiree 277
        // working = any other than above


        if ($charity) {
            foreach ($options->max_coverage_by_age_charity as $cba) {
                if ($this->ageBetween($user, $cba->from, $cba->to)) { // in age range
                    if ($user->age() <= 15)  // CHILD
                        return $cba->coverage;
                    // check for occ
                    
                        if (is_object($cba->coverage)) {
                            return $this->findMin($user->age(), $cba->to, $cba->coverage->max, $user->personal_income, 25, 15);
                        } else{
                            return $cba->coverage;
                        }

                    //   return $cba;
                }
            }
        } else {

            foreach ($options->max_coverage_by_age as $cba) {
                if ($this->ageBetween($user, $cba->from, $cba->to)) { // in age range
                    if ($user->age() <= 15)  // CHILD
                        return $cba->coverage;
                    // check for occ
                    if ($this->occ_match($user->occ, $cba->occ))
                        if (is_object($cba->coverage)) {

                            switch ($this->name) {
                                case 'Death' :
                                    $min = 25; $max = 10;
                                    break;

                                case 'Disability' :
                                    $min = 25; $max = 10;
                                    break;

                                case 'Critical Illness' :
                                    $min = 20; $max = 5;
                                    break;
                            }
                            return $this->findMin($user->age(), $cba->to, $cba->coverage->max, $user->personal_income, $min, $max);
                        } else
                            return $cba->coverage;

                    //   return $cba;
                }
            }

        }

    }
    private function ageBetween($user, $from, $to)
    {
        $age_year = $user->age();
        $age_days = $user->age(true);

        if ($from < 0) {
            $ok_from = $age_days >= ($from * -1);
        } else {
            $ok_from = $age_year >= $from;
        } // calculate by days
        if ($to < 0) {
            $ok_to = $age_days <= ($to * -1);
        } else {
            $ok_to = $age_year <= $to;
        }

        return $ok_from && $ok_to;

    }
    private function occ_match($userOcc, $occList)
    {
        $excluded = false;
        foreach ($occList as $occ) {
            if ($occ < 0) {// must exclude
                $excluded = true;

                if (($occ * -1) == $userOcc)
                    return false;


            } else
                if ($occ == $userOcc)
                    return true;
        }

        return $excluded ? true : false;
    }
    private function findMin($entryAge, $maxAge, $default, $monthlyIncome, $min, $max)
    {
        return min($default,  max(min($min, $maxAge - $entryAge), $max) * (12 * $monthlyIncome));
    }

    public function claimQuestions()
    {
        return $this->hasMany(ClaimQuestion::class,'product_id');
    }

    public function covertAnnuallyToMonthly($price){
        return Helpers::round_up($price * 0.085, 2);
    }

    public function isMedical()
    {
        return $this->name == 'Medical' ? true : false;
    }

	/**
	 * @param $user
	 * @return mixed
	 */
	private function getCountActiveCoverageByProduct($user)
	{
		return Coverage::where('covered_id',$user->id)->where('product_id',$this)->where('state',Enum::COVERAGE_STATE_ACTIVE)->count();
	}

	/**
	 * @param $uw
	 * @return array
	 */
	private function checkAllowWithUw($uw)
	{
		if($uw){
			$uwExiststInCoverage = Coverage::where('uw_id',$uw->id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->exists();
			if($uwExiststInCoverage){
				$allowed     = FALSE;
				$message_bag = __('web/server.uw_failed_product');
			}
	
    
           


    
    
        }
	}



private function smoke_height_weight($uw,$product,$user)
{
    $height_weight = false;

    Log::info('Smoke is '.config('static.underwriting.allow_daily_smoke'));
    
 if ($uw){

    $bmi = round(($uw->answers['weight'] / $uw->answers['height'] / $uw->answers['height']) * 10000);
    $smoke =$uw->answers['smoke']>=config('static.underwriting.allow_daily_smoke');
    Log::info('bmi is '.$bmi);

    

    $age = $user->age();

    if ($age >= 17) {
        if($product == 'death' || $product == 'disability'){
            $height_weight = $bmi >= 17 && $bmi <= 31 && !$smoke;

        }elseif($product == 'critical_illiness'|| $product == 'medical'){
            $height_weight = $bmi >= 18 && $bmi <= 29 && !$smoke;
       }else{
            $height_weight =true;
       }
   
    } elseif ($age <= 16 && $age >= 13) {
        $height_weight  = $bmi >= 15 && $bmi <= 27 && !$smoke;

    } elseif ($age <= 12 && $age >= 9) {
        $height_weight = $bmi >= 14 && $bmi <= 22 && !$smoke;

    } elseif ($age <= 8 && $age >= 2) {
        $height_weight= $bmi >= 14 && $bmi <= 19 && !$smoke;

    } else { // child below 2 years old

        $monthOld = $user->profile->ageMonths();

        $allowed_juv_bmi = juvenileBmi::whereGender(strtolower($user->profile->gender))->whereAge($monthOld)->first();
        if (($uw->answers['weight'] >= $allowed_juv_bmi->weight_min && $uw->answers['weight'] <= $allowed_juv_bmi->weight_max) &&
            ($uw->answers['height'] >= $allowed_juv_bmi->height_min && $uw->answers['height'] <= $allowed_juv_bmi->height_max)) {
                $height_weight = !$smoke;
          
        } else {
         
            $height_weight = false;
        }
    }


    if($height_weight)
    {
        return false;
    }
    return true;

    }
}


   private function  UwChecking($uw,$product,$deathCoverage,$user)
   {


/*

    Log::info('Smoke is '.config('static.underwriting.allow_daily_smoke'));

    if($uw->answers['smoke']>=config('static.underwriting.allow_daily_smoke'))
    {
        return true;
    }

*/
	if($uw && $product!='accident'){
			
        $answers    =   $uw->answers['answers'] ?? [];


        $results = DB::table('uws')
->select("$product as result")
->whereIn('id', $answers)
->distinct()
->pluck('result');

if($this->smoke_height_weight($uw,$product,$user)==true){
    return true;
}
if ($results->count() > 1) {
return true;
            $message_bag = __('web/server.uw_failed_product');
} elseif ($results->count() == 1) {

if ($results->first() == '0') {
    return true;
}
else 
{
    return false;
}
}

    } 
    else 
    {

        

    
        $mc_check = true;    
        $answers    =   $uw->answers['answers'] ?? [];

        Log::info('value is '.in_array("53", $answers, TRUE));
        $results = DB::table('uws')
->select("$product as result")
->whereIn('id', $answers)
->distinct()
->pluck('result');

if($this->smoke_height_weight($uw,$product,$user)==true){
    return true;
}
if ($results->count() > 1) {
    $mc_check = false;
            $message_bag = __('web/server.uw_failed_product');
} elseif ($results->count() == 1) {

if ($results->first() == '0') {
    $mc_check = false;
}
}
//$user = Auth::user()->profile;  

//$uwExiststInCoverage = Coverage::where("covered_id",$user->id)->where("product_name",'Death')->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_ACTIVE])->first();


if( $deathCoverage && $mc_check) 
{
    return false; 
}
else { 
     if($uw == null){
        return false; 
    }
  /*  
    if ($uwExiststInCoverage && $mc_check==false) 
    {
if (in_array("53", $answers, TRUE))
  {
    Log::info('value is +++ '.in_array("53", $answers, TRUE));
    return false;
  }
  
else
  {
return true ;
  } 

} 
*/   
return true; 
}
// if not have death  


}
   }

private function checkAllowWithUwLoading($uw,$product,$deathCoverage,$user)
{


    
$product_check = $product=='critical_illiness' ? 'Critical Illness':ucwords($product); 
$uwExiststInCoverage = Coverage::where("covered_id",$user->id)->where("product_name",$product_check)->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
$product_payer_id =null;
if (Auth::check()){
    $product_payer_id =Auth::user()->id;

}

$product_payer_cov = Coverage::where("covered_id",$user->id)->where("product_name",$product_check)->where('payer_id',$product_payer_id)->where('state', Enum::COVERAGE_STATE_ACTIVE)->first();
  
if($uwExiststInCoverage && $product_payer_id == $user->user_id)
{
   return false;

}

if($product_payer_cov && $product_payer_id != $user->user_id ){
    return false;
}

  return  $this->UwChecking($uw,$product,$deathCoverage,$user);
	


}

}