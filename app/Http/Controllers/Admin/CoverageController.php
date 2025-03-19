<?php

namespace App\Http\Controllers\Admin;

use App\Coverage;
use App\IndustryJob;
use App\Underwriting;
use App\Helpers;
use App\Helpers\Enum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherDetails;

class CoverageController extends Controller
{
    public function index()
    {
        return view('admin.coverages.index');
    }

    public function configure()
    {
        $this->model = Coverage::class;
        $this->setTitle("Coverage");
        //        $this->addQueryCondition('where',['status','!=','Cancelled']);

        $this->addColumn("RefNo", 'ref_no');
        $this->addColumn("Owner", function ($q) {
            return '<a href="' . route('admin.User.show', $q->owner->user->uuid ?? '') . '">' . ($q->owner->name . '<br><small style="color: #005eea">' . $q->owner->user->ref_no . '</small>' ?? '') . "</a>";
        });
        $this->addColumn("Product", 'product_name');
        $this->addColumn("Status", 'status');
        $this->addColumn("State", 'state');
        $this->addColumn("Payment Term", 'payment_term');
        $this->addColumn("Coverage", function ($q) {
            return 'RM' . number_format($q->coverage, 2);
        });
        $this->addColumn(__('web/messages.created_at'), function ($q) {
            return Carbon::parse($q->created_at)->format(config('static.datetime_format'));
        });
        $this->addColumn(__('web/messages.payment_at'), function ($q) {
            if ($q->last_payment_on) {
                return Carbon::parse($q->last_payment_on)->format(config('static.datetime_format'));
            } else {
                return '-';
            }
        });
        $this->addColumn("Premium", function ($q) {
            return $q->payment_term == 'monthly' ? 'RM' . number_format($q->payment_monthly, 2) : 'RM' . number_format($q->payment_annually, 2);
        });


        $this->addAction('admin.Coverage.show', '<i class="feather icon-eye"></i>', 'View Details', ['$uuid'], Helpers::getAccessControlMethod());

        $this->addBladeSetting('hideCreate', true);

        return $this;
    }
    public function show($id)
    {
        if (is_numeric($id))
            $data = Coverage::where("id", $id);
        else
            $data = Coverage::where("uuid", $id);

        $data = $data->get()->first();
        $age = date_diff(date_create($data->owner->dob),date_create($data->next_payment_on))->format('%y');
        if($data->payer_id != $data->owner->user_id && $data->payer->corporate_type == 'payorcorporate'){
            $age = date_diff(date_create($data->owner->dob),date_create($data->ndd_payment_due_date))->format('%y');

        }else{
            $age = date_diff(date_create($data->owner->dob),date_create($data->next_payment_on))->format('%y');

        }
        
        $Vouchercheck = VoucherDetails::where("email",$data->owner->user->email)->first();
        //dd($Vouchercheck);
        if(!empty($Vouchercheck) && $data->owner->occ==null){
            $occ_loading='0';
        }else{
        $occ_loading=null;}
        
        //$occ_loading=null;
        //dd($age);
        if($data->product_name ==Enum::PRODUCT_NAME_MEDICAL){
            $price =$data->product->getPrice($data->owner,$data->coverage,$occ_loading,$age,$data->deductible)[0];
        }else{
            $price =$data->product->getPrice($data->owner,$data->coverage,$occ_loading,$age)[0];

        }
        
        if($data->payment_term == 'annually'){
            $renewal_date      =   $data->next_payment_on;
            }elseif($data->payment_term == 'monthly'){
                $now = now();
                $first = Carbon::parse($data->first_payment_on);
                $diff_day = date_diff(date_create($now), date_create($first));
                $diff = $diff_day->format("%y") + 1;
                if($diff_day->format("%y") < 1){
                    $renewal_date   = $first->addYear();
                }else{
                    $renewal_date   = $first->addYear($diff);
                }  
            //$renewal_date      =   Carbon::parse($data->first_payment_on)->addYear();
            }
            
            if($data->payer_id != $data->owner->user_id && $data->payer->corporate_type == 'payorcorporate'){
                $renewal_date = Carbon::parse($data->ndd_payment_due_date);
            }

       if($data->payer_id == $data->owner->user_id){
            if(($data->status == Enum::COVERAGE_STATUS_GRACE_UNPAID||$data->status == Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID)){
                $coverage_expiry = (Carbon::parse($data->parent->next_payment_on)->startOfDay()->diffInMonths($data->parent->first_payment_on) >= 23 )?Carbon::parse($data->parent->next_payment_on)->addDays(90):Carbon::parse($data->parent->next_payment_on)->addDays(30);
                //dd($data->parent->next_payment_on);
            }else{
                $coverage_expiry = (Carbon::parse($data->next_payment_on)->startOfDay()->diffInMonths($data->first_payment_on) >= 23 )?Carbon::parse($data->next_payment_on)->addDays(90):Carbon::parse($data->next_payment_on)->addDays(30);

            }
           
        }else{
            if(($data->status == Enum::COVERAGE_STATUS_GRACE_UNPAID||$data->status == Enum::COVERAGE_STATUS_GRACE_INCREASE_UNPAID)){
                $coverage_expiry = (Carbon::parse($data->parent->payor_next_payment_date)->startOfDay()->diffInMonths($data->parent->first_payment_on) >= 23 )?Carbon::parse($data->parent->payor_next_payment_date)->addDays(90):Carbon::parse($data->parent->payor_next_payment_date)->addDays(30);
                if($data->parent->payor_next_payment_date ==null){
                    $coverage_expiry = (Carbon::parse($data->parent->ndd_payment_due_date)->startOfDay()->diffInMonths($data->parent->first_payment_on) >= 23 )?Carbon::parse($data->parent->ndd_payment_due_date)->addDays(90):Carbon::parse($data->parent->ndd_payment_due_date)->addDays(30);

                }

            }else{
                $coverage_expiry = (Carbon::parse($data->payor_next_payment_date)->startOfDay()->diffInMonths($data->first_payment_on) >= 23 )?Carbon::parse($data->payor_next_payment_date)->addDays(90):Carbon::parse($data->payor_next_payment_date)->addDays(30);
                if($data->payor_next_payment_date ==null){
                    $coverage_expiry = (Carbon::parse($data->ndd_payment_due_date)->startOfDay()->diffInMonths($data->first_payment_on) >= 23 )?Carbon::parse($data->ndd_payment_due_date)->addDays(90):Carbon::parse($data->ndd_payment_due_date)->addDays(30);

                }
            }

        }

           
       if($data->payment_term_new != ''){
        if($data->payment_term_new == 'annually'){
        $premimum_amount_ndd      =   Helpers::round_up($price, 2);
        }elseif($data->payment_term_new == 'monthly'){
        $premimum_amount_ndd      =   Helpers::round_up($price * 0.085, 2);
        }
    }else{
        if($data->payment_term == 'annually'){
            $premimum_amount_ndd      =   Helpers::round_up($price, 2);
            }elseif($data->payment_term == 'monthly'){
            $premimum_amount_ndd      =   Helpers::round_up($price * 0.085, 2);
            }
    }

    if($data->owner->user_id != $data->payer_id){
        if($data->ndd_payment_due_date ==null){
            $next_payment =   $data->next_payment_on;

        }else{
            $next_payment = $data->ndd_payment_due_date;
        }
       
       }elseif($data->owner->user_id == $data->payer_id){
        $next_payment =   $data->next_payment_on;
        }

        $today = Carbon::now()->format('Y-m-d');
        
        $coverage_duration = date_diff(date_create($today), date_create($data->first_payment_on));

        $year = $coverage_duration->format('%y')==1 || $coverage_duration->format('%y')==0 ? 'year' : 'Years';
        $day =  $coverage_duration->format('%d')==1 || $coverage_duration->format('%d')==0 ? 'day':'days';
        $month =$coverage_duration->format('%m')==1 || $coverage_duration->format('%m')==0 ? 'month':'months';
        
        $cov_duration_format = $coverage_duration->format("%y $year %m $month %d $day");

        $underwriting =Underwriting::where('id',$data->uw_id)->first();
        $answers =[];
        if($underwriting){
            $answers =$underwriting->answers;
        }
    
        $exceptions=[];
        $occ_details=[];
        $occ =IndustryJob::where('id',$data->owner->occ)->first();
       if(empty($Vouchercheck) || $data->owner->occ!=null){
        if($data->product_name == 'Medical'){
            $occ_details['occ_load']=$occ->Medical;
            $occ_details['name']=$occ->name;
        }elseif($data->product_name == 'Accident'){
            $occ_details['occ_load']=$occ->Accident;
            $occ_details['name']=$occ->name;
        }elseif($data->product_name =='Disability'){
            $occ_details['occ_load']=$occ->TPD;
            $occ_details['name']=$occ->name;
        }elseif($data->product_name =='Death'){
            $occ_details['occ_load']=$occ->death;
            $occ_details['name']=$occ->name;
        }
    }

        if(!empty($answers)){

        if ($data->product_name == 'Critical Illness'){
       

          
           foreach ($answers['answers'] as $answer){
               $ans =\App\Uw::find($answer);
               
               if(!empty($ans->critical_en)){

                $exceptions[]= $ans->title.': '.$ans->critical_en;
                
               
            }      
              
           
        }
       } elseif ($data->product_name == 'Medical') {
   
       
           foreach ($answers['answers'] as $answer){
               $ans =\App\Uw::find($answer);
               if(!empty($ans->medical_en)){
                   
                  
                       $exceptions[]= $ans->title.': '.$ans->medical_en;
                    
                    
                   
                  }
               
           }
        

       }
    }

       $title =[];

       if(!empty($answers)){

       foreach($answers['answers'] as $answer){
       
        $loading =\App\UwsLoading::where('uws_id',$answer)->where('product_id',$data->product_id)->first();
       // dd($loading);
       
        if($loading){
            
            $ans_title =\App\Uw::find($answer);
           
            $sub_ans =\App\Uw::whereIn('id',$answers['answers'])->where('parent_uws_id',$answer)->get();
            
          //  dd($sub_ans);
           
            if($sub_ans->isNotEmpty()){
                $title[] =  $ans_title->title;
                foreach ($sub_ans as $sub){
                    $title[] = $sub->title;
                }
               
            }elseif($ans_title->parent_uws_id != -1){
                $parent_uws =\App\Uw::find($ans_title->parent_uws_id);
                if(!in_array($parent_uws->title,$title)){
                    $title[] =$parent_uws->title;
                }

                 $title[] =  $ans_title->title;

            }else{
                $title[] =  $ans_title->title;
            }
           
        }
       }
    }

     
        
        $coverage = $data;
        return view('admin.coverage-details', compact('coverage','premimum_amount_ndd','cov_duration_format','renewal_date','exceptions','title','occ_details','next_payment','coverage_expiry'));
    }
}