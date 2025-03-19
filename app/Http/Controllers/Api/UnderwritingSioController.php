<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Coverage;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Individual;
use App\juvenileBmi;
use App\Underwriting;
use App\User;
use App\SpoCharityFundApplication;
use Validator;
use App\Http\Controllers\Controller;
use App\UwsGroupSio;
use App\UwsSio;

class UnderwritingSioController extends Controller
{
    //
    private $questions = [
        'height' => null,
        'weight' => null,
        'smoke' => null,
        'questions' => [],
    ];


    public function get(Request $request)
    {
        $user = $request->user();
        if (empty($user))
            $user = auth()->user();

        $request_user = $user;
       
        $age = $user->profile->age();

        if($age < 2)
            $uwgs = UwsGroupSio::get();
        else
            $uwgs = UwsGroupSio::where("name","!=","new3")->get();
            $questions = [];
            foreach ($uwgs as $uwg) {
                $gender = $user->profile->gender ?? 'male';
                if(strtolower($gender) == 'male')
                    $qs = $uwg->questions()->where(function ($q){
                        $q->where("gender",'all')->orWhere("gender",'male');
                    });
                elseif(strtolower($gender) == 'female')
                    $qs = $uwg->questions()->where(function ($q){
                        $q->where("gender",'all')->orWhere("gender",'female');
                    });
    
                $qs = $qs->get();
                $qs_list = [];
                foreach ($qs as $q) {
                    $qs_list[] = [
                        'id'=>$q->id,
                        'title'=>$q->title,
                        'info'=>$q->info,
                        'gender'=>$q->gender,
                        'value'=>null,
                        'is_accepted'=>$q->is_accepted,
                        'sub_questions' =>  $q->sub_questions()->get()
                    ];
                }
                $questions[$uwg->name] = [
                    'title' => $uwg->title,
                    'questions'=>$qs_list,
                ];
            }

            $this->questions['questions'] = $questions;
           

            if ($request->json('payload') != null) {

                $payload = $request->json('payload');
                $rules   = [
                    'weight'  => 'required|numeric',
                    'height'  => 'required|numeric',
                    'smoke'   => 'required|numeric' ,
    //                'smoke'   => 'required|numeric|lt:' . config('static.underwriting.allow_daily_smoke'),
    
                    'answers' => 'required|array',
                ];
    
                $messages = [
                    'lt' => __('web/messages.unable_subscribe_heavy_smoker')
                ];
    
                Validator::make($payload,$rules,$messages)->validate();
    
                //$accepted_answers = [53, 39, 34, 52,57,59,61]; // "None" & 'No' ids
                $accepted_answers   =   UwsSio::where('is_accepted', 1)->pluck('id')->toArray();
    
    
                $medical_issues = (count(array_diff($payload['answers'], $accepted_answers)) > 0) || ($payload['smoke'] >= config('static.underwriting.allow_daily_smoke'));
                $age = $user->profile->age();
    
                $bmi = round(($payload['weight'] / $payload['height'] / $payload['height']) * 10000);
    
                if ($age >= 17) {
                    $death = $bmi >= 17 && $bmi <= 31 && !$medical_issues;
                    $disability = $bmi >= 17 && $bmi <= 31 && !$medical_issues;
                    $ci = $bmi >= 18 && $bmi <= 29 && !$medical_issues;
                    $medical = $bmi >= 18 && $bmi <= 29 && !$medical_issues;
                } elseif ($age <= 16 && $age >= 13) {
                    $death = $bmi >= 15 && $bmi <= 27 && !$medical_issues;
                    $disability = $bmi >= 15 && $bmi <= 27 && !$medical_issues;
                    $ci = $bmi >= 15 && $bmi <= 27 && !$medical_issues;
                    $medical = $bmi >= 15 && $bmi <= 27 && !$medical_issues;
                } elseif ($age <= 12 && $age >= 9) {
                    $death = $bmi >= 14 && $bmi <= 22 && !$medical_issues;
                    $disability = $bmi >= 14 && $bmi <= 22 && !$medical_issues;
                    $ci = $bmi >= 14 && $bmi <= 22 && !$medical_issues;
                    $medical = $bmi >= 14 && $bmi <= 22 && !$medical_issues;
                } elseif ($age <= 8 && $age >= 2) {
                    $death = $bmi >= 14 && $bmi <= 19 && !$medical_issues;
                    $disability = $bmi >= 14 && $bmi <= 19 && !$medical_issues;
                    $ci = $bmi >= 14 && $bmi <= 19 && !$medical_issues;
                    $medical = $bmi >= 14 && $bmi <= 19 && !$medical_issues;
                } else { // child below 2 years old
    
                    $monthOld = $user->profile->ageMonths();
    
                    $allowed_juv_bmi = juvenileBmi::whereGender(strtolower($user->profile->gender))->whereAge($monthOld)->first();
                    if (($payload['weight'] >= $allowed_juv_bmi->weight_min && $payload['weight'] <= $allowed_juv_bmi->weight_max) &&
                        ($payload['height'] >= $allowed_juv_bmi->height_min && $payload['height'] <= $allowed_juv_bmi->height_max)) {
    
                        $death = $disability = $ci = $medical = !$medical_issues;
                    } else {
                        $death = $disability = $ci = $medical = false;
                    }
                }
    
    
                //check latest information
                $old_uw = $user->profile->underwritings;
                $goBackToProductPage = false;
    //            if (!empty($old_uw)) {
    //                if ($old_uw->death != $death || $old_uw->disability != $disability || $old_uw->ci != $ci || $old_uw->medical != $medical) {
    //                    $goBackToProductPage = true;
    //                }
    //            }
                //create or edit ?!
                $used_by_coverage = $user->profile->coverages_owner()->where("uw_id", $user->profile->underwritings->id ?? -1)->count() > 0;
                $old_uw = $user->profile->underwritings;
                if (empty($old_uw))
                    $is_changed = true;
                else {
                    $old_answers = ($old_uw->answers);
                    $is_changed =
                        $old_uw->death != $death ||
                        $old_uw->ci != $ci ||
                        $old_uw->disability != $disability ||
                        $old_uw->medical != $medical ||
                        count(array_diff($payload['answers'] ?? [1], $old_answers['answers'] ?? [])) > 0 ||
                        $old_answers['smoke'] != $payload['smoke'] ||
                        $old_answers['height'] != $payload['height'] ||
                        $old_answers['weight'] != $payload['weight'] ||
                        $old_uw->created_by != auth()->id();
                }
                //if (($is_changed && ($old_uw->created_by ?? null) != auth()->id()) || $used_by_coverage)
                    $uw = new Underwriting();
                //else
                 //   $uw = $old_uw;
    
                $uw->individual_id = $user->profile->id;
                $uw->answers = $payload;
                $uw->death = $death;
                $uw->ci = $ci;
                $uw->disability = $disability;
                $uw->medical = $medical;
                $uw->created_by = auth()->id();
                $uw->save();
                if( $user->profile->is_charity()){
                    $spo_application=SpoCharityFundApplication::where('user_id',$user->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
                    $spo_application->form_expiry =Carbon::now()->addMonths(6);
                    $spo_application->save();
                }
    
    //            $uw = Underwriting::updateOrCreate(['individual_id' => $user->profile->id], ['answers' => $payload, 'death' => $death, 'ci' => $ci, 'disability' => $disability, 'medical' => $medical,'created_by'=> auth()->id()]);
    
    
    
                $uwo = $uw;
                $uw = $uw->toArray();
    
                $charity = $user->profile->nominees()->whereEmail('Charity@Deartime.com')->first()->percentage ?? 0;
    
                // check for Death and Accident need nominee
                $coveragesCovered = Coverage::where('covered_id',$user->profile->id)
                    ->whereIn('product_name',[Enum::PRODUCT_NAME_ACCIDENT,Enum::PRODUCT_NAME_DEATH])
                    ->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                    ->get();
    
                $needNominee = $coveragesCovered->count()>0 && $user->profile->beneficiaries()->count() == 0;
    
    
                if (!empty($promoter)) {
                    $uw['next_page'] = NextPage::PRODUCT;
                    $uw['next_page_params'] = ['user_id' => $user->uuid, 'user_name' => $user->profile->name ?? 'Promoted User'];
                    $uw['next_page_url'] = route('userpanel.promote.product', $user->uuid);
                }elseif(!empty($p_other)){
    
                    $this_user = $user->profile ?? null;
                    $uw = ['next_page' => NextPage::ORDER_REVIEW,'next_page_url'=>route('userpanel.order.other',$user->profile->uuid),'next_page_params'=>['fill_type'=>'pay_for_others','user_id'=>$user->profile->uuid ?? 0]];
    
    //                    $chk = !empty($this_user->coverages_covered) ? (($this_user->coverages_covered()->where("status","unPaid")->first()->is_accepted_by_owner ?? 0) == 1) : false;
    //
    //                    if($chk) {
    //                        $uw = ['next_page' => 'order_review_page','next_page_params'=>['fill_type'=>'pay_for_others','user_id'=>$user->profile->uuid ?? 0]];
    //                    }else{
    //                        if (!empty($this_user->user))
    //                            $this_user->user->sendNotification("pay_other_notification_title", "pay_other_notification_body", ['command' => 'next_page', 'page_data' => ['fill_type' => 'pay_for_others', 'payer_id' => $this_user->uuid, 'user_id' => $user->profile->uuid ?? 0], 'data' => 'order_review_page', 'id' => 'pay_other', 'buttons' => [['title' => 'accept', 'action' => 'accept_pay_other'], ['title' => 'reject', 'action' => 'reject_pay_other']], 'auto_read' => false]);
    //                        $uw = ['next_page' => 'dashboard_page', 'msg' => 'wait_until_owner_accept_payment'];
    //                    }
    
                } elseif ($needNominee) {
                    $uw['next_page'] = NextPage::NOMINEE;
                }
                else if($user->profile->thanksgiving()->count() == 0)
                {
                    $uw['next_page'] = NextPage::THANKSGIVING;
                }
                else if (($user->profile->bankCards()->count() == 0 || $user->profile->bankAccounts()->count() == 0)&&(!$user->profile->is_charity())) {
                    $uw['next_page'] = NextPage::PAYMENT_DETAIL;
                }else if ($user->profile->bankAccounts()->count() == 0 && $user->profile->is_charity()) {
                    $uw['next_page'] = NextPage::PAYMENT_DETAILS_ACCOUNT;
                } else {
                    if( $user->profile->is_charity()){      
                        //$spo_coverage =Coverage::where('payer_id',$user->profile->user_id)->where('status','unpaid')->get();
                        
                        $spo_application=SpoCharityFundApplication::where('user_id',$user->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
                        if($spo_application->status !='QUEUE'){
                        $spo_application->status ='SUBMITTED';
                        }
                        $spo_application->active=1;
                        if($spo_application->renewed!=1){
                            $spo_application->submitted_on =Carbon::now();
                            $spo_application->form_expiry =Carbon::now()->addMonths(6);
                            $spo_application->save();
                        }else{
                                $spo_application->renewed_at =Carbon::now();
                                $spo_application->save();
                        }
                        if($spo_application->status =='QUEUE'){
                            $modal = [
                                
                     
                                "title"   => __('mobile.sponsored_insurance'),
                                "body"    => ('you are in the waiting list. Once your coverage is active, you will be notified.'),
                                "buttons" => [
                                    [
                                        "title"  => __('ok'),
                                        "action" => NextPage::DASHBOARD,
                                        "type"   => "page",
                                    ],
                                    
                     
                                ]
                            ];
                            return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                            }else{
                                $modal = [
                                
                     
                                    "title"   => __('mobile.sponsored_insurance'),
                                    "body"    => ('We have sucessfully received your application for Sponsored Insurance. Please allow us 5 working days for processing before we notify you on the status of your application.'),
                                    "buttons" => [
                                        [
                                            "title"  => __('ok'),
                                            "action" => NextPage::DASHBOARD,
                                            "type"   => "page",
                                        ],
                                        
                         
                                    ]
                                ];
                                return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                            }
                        
                    }else{
                    if(!$user->profile->isVerified()){
                        $uw['next_page'] = NextPage::VERIFICATION;
                    }
                    else{
                        $uw['next_page'] = NextPage::ORDER_REVIEW;
                    }
                    }
                }
                if ($goBackToProductPage) {
                    $uw['next_page'] = NextPage::PRODUCT;
                }
    
                if($request_user->isCorporate()){
                    if($request_user->profile->isClinic() ){
                        $uw['next_page_url'] = route('userpanel.clinic.review');
                    }
                }
                 $uw['underwriting_reject'] = FALSE;
                //check for limit
    
                if(!$uwo->canBuyCoverage()){
                    //$uw['msg'] = __('mobile.uw_limit_err');
                    Coverage::where(["owner_id" => $user->profile->id])
                            ->whereIn('status', ['unpaid', 'increase-unpaid'])
                            ->update(['is_deleted' => 1]);
                    $uw['underwriting_reject'] = TRUE;
                    // $modal=[
                    //     "body" => __('mobile.medical_survey_reject'),
                    //     "buttons" => [
                    //         [
                    //             "title" => __('mobile.ok'),
                    //             "action" => "",
                    //             "type" => "",
                    //         ],
                    //         [
                    //             "title" => __('web/menu.dashboard'),
                    //             "action" => NextPage::DASHBOARD,
                    //             "type" => "page",
                    //         ],
                    //     ]
                    // ];
                    //return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
                }
                if($uwo->canBuyCoverage()){
                        Coverage::where(["owner_id" => $user->profile->id])
                          ->whereIn('status', ['unpaid', 'increase-unpaid'])
                          ->update(['is_deleted' => 0]);
                }
                        
                return ['status' => 'success', 'data' => $uw];
            }
            else {
                $uw = $user->profile->underwritings ?? null;
                if (!$uw || $request->input('fill_type') == 'pay_for_others') {
                    $this->questions['exists'] = false;
                    return ['status' => 'success', 'data' => $this->questions];
                }
                $this->questions['exists'] = true;
    
                $this->questions['weight'] = $uw->answers['weight'] ?? 0;
                $this->questions['height'] = $uw->answers['height'] ?? 0;
                $this->questions['smoke'] = $uw->answers['smoke'] ?? 0;
    
                $selectedAnswers    =   $uw->answers['answers'] ?? [];
                foreach ($this->questions['questions'] as $ques) {
                    foreach ($ques['questions'] as $ques2) {
                        foreach ($ques2['sub_questions'] as $ques3) {
                            //dd($ques3);
                            $ques3['value'] = null;
                            if(in_array($ques3->id, $selectedAnswers)) {
                                $ques3['value'] = true;
                            }
                        }
                    }
                }
    
                foreach ($uw->answers['answers'] ?? [] as $answer) {
                    foreach ($this->questions['questions']['health']['questions'] as $index => $health_question)
                        if ($health_question['id'] === $answer)
                            $this->questions['questions']['health']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['health2']['questions'] as $index => $health_question)
                        if ($health_question['id'] === $answer)
                            $this->questions['questions']['health2']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['family']['questions'] as $index => $family_question)
                        if ($family_question['id'] === $answer)
                            $this->questions['questions']['family']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['lifestyle']['questions'] as $index => $lifestyle_question)
                        if ($lifestyle_question['id'] === $answer)
                            $this->questions['questions']['lifestyle']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['new1']['questions'] ?? [] as $index => $lifestyle_question)
                        if ($lifestyle_question['id'] === $answer)
                            $this->questions['questions']['new1']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['new2']['questions'] ?? [] as $index => $lifestyle_question)
                        if ($lifestyle_question['id'] === $answer)
                            $this->questions['questions']['new2']['questions'][$index]['value'] = true;
    
                    foreach ($this->questions['questions']['new3']['questions'] ?? [] as $index => $lifestyle_question)
                        if ($lifestyle_question['id'] === $answer)
                            $this->questions['questions']['new3']['questions'][$index]['value'] = true;
    
                }
    
                $gender = $user->profile->gender ?? 'male';
                $removeGender = $gender == 'male' ? 'female' : 'male';
    
                $health = [];
                foreach ($this->questions['questions']['health']['questions'] as $index => $health_question) {
                    if ($health_question['gender'] != $removeGender)
                        $health[] = $health_question;
                }
    
                $health2 = [];
                foreach ($this->questions['questions']['health2']['questions'] as $index => $health_question) {
                    if ($health_question['gender'] != $removeGender)
                        $health2[] = $health_question;
                }
    
                $family = [];
                foreach ($this->questions['questions']['family']['questions'] as $index => $family_question) {
                    if ($family_question['gender'] != $removeGender)
                        $family[] = $family_question;
                }
    
                $lifestyle = [];
                foreach ($this->questions['questions']['lifestyle']['questions'] as $index => $lifestyle_question) {
                    if ($lifestyle_question['gender'] != $removeGender)
                        $lifestyle[] = $lifestyle_question;
                }
                $this->questions['questions']['health']['questions'] = $health;
                $this->questions['questions']['health2']['questions'] = $health2;
                $this->questions['questions']['family']['questions'] = $family;
                $this->questions['questions']['lifestyle']['questions'] = $lifestyle;

        return ['status' => 'success', 'data' =>  $this->questions];
            }
    }

public function test()
{
 
    $underwriting = Underwriting::whereId('4909')->first();
        // $this->underwriting = $underwriting;

        $answers = $underwriting->answers;

    return ['status' => 'success', 'data' =>   $answers];
    
}


}
