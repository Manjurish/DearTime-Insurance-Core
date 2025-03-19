<?php     

namespace App\Http\Controllers\Api;
use App\SpoCharityFunds;
use Carbon\Carbon;
use App\CharityApplicant;
use App\SpoCharityFundApplication;
use App\SpoHouseholdMembers;
use App\Transaction; 
use App\Coverage;
use App\Individual;
use App\User;
use Illuminate\Http\Request;
use App\Document;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use PhpParser\Comment\Doc;
use App\Http\Controllers\Controller;


class SpoInsuranceController extends Controller
{
    public function sopsummary(Request $request){
        $charityfundsum=0;
	    //$sop=SpoCharityFunds::all();
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
		  $sopcovered=SpoCharityFundApplication::where('status','ACTIVE')->count();
		  $sopinline =SpoCharityFundApplication::where('status','QUEUE')->count();
          $sopappliedcount =SpoCharityFundApplication::WhereIn('status',['ACTIVE','QUEUE','SUBMITTED'])->count();
          $coveredrate =0;
          if($sopappliedcount!=0 && $sopcovered!=0 ){
          $coveredrate =round((($sopcovered/$sopappliedcount)*100),2);
          }
		  //$percentagecovered =$$sopcovered
          $user=auth()->user();
          $coverage =Coverage::where('payer_id', $user->profile->user_id)->get();

//           if($user->profile->household_income >3170){

//             $modal=[
//                 "title"   => "Attention",
//                 "body" => __('We regret to inform you that we are unable to offer you Sponsored Insurance because your monthly household income exceeds RM3,170.'),
//                 "buttons" => [
        
//                        [
//                       "title" => __('ok'),
//                       "action" => NextPage::DASHBOARD,
//                       "type" => "page",
//             ],
//             ]
            
// ];
//           return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
//           }

          $coverage =Coverage::where('payer_id', $user->profile->user_id)->where('sponsored',NULL)->where('status','active')->get();
          $spo_coverage=Coverage::where('payer_id', $user->profile->user_id)->where('sponsored',1)->where('status','active')->get();
//           if(($coverage)->isNotEmpty()){
//             $modal=[
//                 "title"   => "Attention",
//                 "body" => __('We regret to inform you that we are unable to offer you Sponsored Insurance because you are already covered with DearTime insurance.'),
//                 "buttons" => [
        
//                        [
//                       "title" => __('ok'),
//                       "action" => NextPage::DASHBOARD,
//                       "type" => "page",
//             ],
//             ]
            
// ];
//           return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
//           }

          $spo_applied =false;
          $spo_submitted =false;
          $submitted_on =null;
          $spoappliedcheck =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
          $latestapplication =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->latest()->first();
          if($spoappliedcheck){
            $spo_applied =true;
            $submitted_on = Carbon::parse($spoappliedcheck->submitted_on)->format('d M Y');
            if($spoappliedcheck->status != 'PENDING'){
                $spo_submitted =true;
            }
          }
          $spo_eligiblity =false;
          if( $user->profile->household_income <=3400 && !$user->profile->isOld() && !$user->profile->isChild()){
            if($spo_coverage->isNotEmpty()){
              if( $latestapplication->status =='EXPIRED'){
                $spo_eligiblity =true;
              }
            }else{
                if(!($coverage)->isNotEmpty()){
                    $spo_eligiblity =true;
                }
            }
            
        
          }
          
          $approve_wait_line = SpoCharityFundApplication::WhereIn('status',['ACTIVE','QUEUE'])->count();
          $queue_order = SpoCharityFundApplication::where('status','QUEUE')->orderBy('submitted_on','asc')->get()->toArray();

          $queue_current=-1;
          foreach ($queue_order as $n=>$arr) {
              foreach($arr as $valuee){
              if ((string)$user->profile->user_id==$valuee) {
                 
                  $queue_current = $n+1;
                }

             }
            
          }


          $payer_coverages =Coverage::where('owner_id',$request->user()->profile->id)->where('payer_id','<>',$request->user()->profile->user_id)->where('status','unpaid')->get();
          if($payer_coverages->count() > 0){
            $payer_offer = True;
          }else{
            $payer_offer = False;
          }

		 return ['status' => 'success', 'data' => ['charityfund' => round($charityfundsum,2), 'sop_covered' => $sopcovered,'sop_inline'=>$sopinline,'covered'=>(($coverage)->isNotEmpty()),'spo_applied'=>$spo_applied,'spo_eligiblity'=>$spo_eligiblity,'covered_rate'=> $coveredrate,'spo_submitted'=>$spo_submitted,'submitted_on'=>$submitted_on, 'queue_current'=>$queue_current,
         'approve_wait_line'=>$approve_wait_line,'payer_offer'=>$payer_offer]];

		//dd(($charityfundsum));
    }
	public function apply(Request $request)
    {

        

        if ($request->user()->profile->household_income > 3400)
            return ['status' => 'error', 'message' => __('web/messages.charity_household_error')];

       

         $expired_form = SpoCharityFundApplication::where('user_id', $request->user()->profile->user_id)->where('status','EXPIRED')->first();

            if(SpoCharityFundApplication::where('user_id', $request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first() != null)
                return ['status' => 'error', 'message' => 'You have already applied for charity program!'];

        //return $request->user()->profile->charity;
        if(empty($request->file('files'))){
            $modal=[
                "title"   => "attention",
                "body" => ('Please upload the required document to continue,would you like to continue?'),
                "buttons" => [
        
                       [
                      "title" => ('yes'),
                      
            ],
            [
                "title" => ('no'),
                "action" => NextPage::DASHBOARD,
                "type" => "page",
      ],
            ]
            
];
return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
        }

        if(!empty($expired_form)){
            $charity =$expired_form;
            //$expired_form->form_expiry =Carbon::now()->addMonths(6);
            $expired_form->status ='Pending';
            $expired_form->renewed =1;
            $expired_form->save();

        }else{
           
            $charity = SpoCharityFundApplication::updateOrCreate(['user_id' => $request->user()->profile->user_id,'form_expiry'=>Carbon::now()->addMonths(6),'status'=>'Pending',]);

        }

        $doc = [];
        
        //$test =$request->files;

        
        foreach ($request->file('files') as $file) {
            $doc[] = Helpers::crateDocumentFromUploadedFile($file, $charity, 'salary_proof');
          

        }

        $nextpage=NextPage::HOUSEMEMBER;
      

        // upload files here

        return ['status' => 'success', 'message' => ('Please provide your household member detail'), 'data' => ['files' => $doc,'next_page'=>$nextpage]];

    }

	public function addmember(Request $request){   

        $application =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first()->id;

        $member =SpoHouseholdMembers::where('sop_id',$application)->where('individual_id',$request->user()->profile->id)->where('nric',$request->nric)->first();
        if($member){

            $member->name =$request->name;
            $member->nric =$request->nric;
            $member->email =$request->email;
            $member->mobile =$request->mobile;
            $member->status =$member->document_path== NULL?'pending':'success';
            $member->relationship =$request->relationship;
            $member->document_type =$request->docutype;
            $member->occupation =$request->occupation;
            $member->industry =$request->industry;
            $member->personal_income =$request->personal_income;
            $member->save();
           
            $doc = [];
           // $filetest=json_encode($hm->files);
            if(!empty($request->file('files'))){
            foreach ($request->file('files') as $file) {
                $doc[] = Helpers::crateDocumentFromUploadedFile($file, $member, 'salary_proof');
            }
            $member->document_path = $member->documents->pluck('path')->implode(' ');
            $member->status ='success';
            $member->save();
        }
        //Doc part Pending
        }else{
            $house_hold_member = new SpoHouseholdMembers;
            $house_hold_member->individual_id =$request->user()->profile->id;
            $house_hold_member->sop_id =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first()->id;
            $house_hold_member->name =$request->name;
            $house_hold_member->nric =$request->nric;
            $house_hold_member->email =$request->email;
            $house_hold_member->mobile =$request->mobile;
            $house_hold_member->status =$house_hold_member->document_path== NULL?'pending':'success';
            $house_hold_member->relationship =$request->relationship;
            $house_hold_member->industry=$request->industry;
            $house_hold_member->occupation =$request->occupation;
            $house_hold_member->personal_income =$request->personal_income;
            $house_hold_member->document_type =$request->docutype;
            $house_hold_member->save();
            $doc = [];
        //    // $filetest=json_encode($hm->files->_parts);
          if(!empty($request->file('files'))){
            foreach ($request->file('files') as $file) {
                $doc[] = Helpers::crateDocumentFromUploadedFile($file, $house_hold_member, 'salary_proof');
              
    
            }
                
                $house_hold_member->document_path = $house_hold_member->documents->pluck('path')->implode(' ');
                $house_hold_member->status ='success';
                $house_hold_member->save();
           }
          
            //Doc part Pending
        }
   
        return [
			'status' => 'success',		
			
		];
      
    }

    public function memberdocupload(Request $request){
        $sop_id =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first()->id;
        $house_hold_member =SpoHouseholdMembers::where('sop_id', $sop_id)->where('nric',$request->nric)->first();
        if(!empty($request->file('files'))){
            foreach ($request->file('files') as $file) {
                $doc[] = Helpers::crateDocumentFromUploadedFile($file, $house_hold_member, 'salary_proof');
            }
                $house_hold_member->document_path = $house_hold_member->documents->pluck('path')->implode(' ');
                $house_hold_member->status ='success';
                $house_hold_member->save();
           }

           $next_page =NextPage::HOUSEMEMBER;

           return [
			'status' => 'success',
            'data'   =>[
                'next_page'=>$next_page,
               
            ],
			
		];
    }

    
    
   
    public function getmember(Request $request){
        $housemembers =$request->user()->profile->housemember; 
        $householdincome =$request->user()->profile->household_income;
        $checkhouseholdincome =$request->user()->profile->personal_income;
        $houseincomeexceed = false;
        $dtuser =false;
        foreach($housemembers as $housemember){
            $checkhouseholdincome += $housemember->personal_income;
            $dtindvcheck =User::where('email',$housemember->email)->first();
            if($dtindvcheck){
            $dtusercheck =Individual::where('user_id',$dtindvcheck->id)->first();
            if($dtusercheck){
                $dtuser =true;
            }
            }
            $housemember['dt_user'] =$dtuser;
        }
         
        if($checkhouseholdincome > $householdincome){
            $houseincomeexceed = true;
        }
        
    

        return [
			'status' => 'success',
			'data'   =>[
                'housemembers'=>$housemembers,
                'incomeexceed'=>$houseincomeexceed,
               
            ],
			
		];
    }

public function check(Request $request){

    $housemembers =$request->user()->profile->housemember; 
    
    $nextpage = 'underwriting_page';

    

    $newmails=[];
     $payload = json_decode($request->json('payload'), TRUE);
        $payload = json_decode(json_encode($payload));
        $newmails =[];
        foreach ($payload->housemembers as $hm) {
			$newmails[]		=	$hm->email;
		}

        $user =$request->user();

        $householdmembers =$request->user()->profile->housemember; 
       
        foreach($householdmembers as $hhm)
        {
            if(!in_array($hhm->email,$newmails))
                $user->profile->housemember()->where('email',$hhm->email)->delete();
        }


   
    
    foreach ($housemembers as $hm){
        $doc_check =SpoHouseholdMembers::where('sop_id',$request->user()->profile->charity->id)->where('individual_id',$request->user()->profile->id)->where('email',$hm->email)->first();
        if($doc_check){
            $doc =$doc_check->document_path;
        }
        if(empty($doc)){
            $modal=[
                "title"   => "attention",
                "body" => __('Please upload the required document to proceed further'),
                "buttons" => [
        
                       [
                      "title" => __('ok'),
                      "action" => NextPage::DASHBOARD,
                      "type" => "page",
            ],
            ]
            
             ];
     return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
        }
    }


      
      
      $spo_application=SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
      $member_income_sum =SpoHouseholdMembers::where('individual_id',$request->user()->profile->id)->where('sop_id',$spo_application->id)->sum('personal_income');
      $household_income =$request->user()->profile->personal_income + $member_income_sum;
      $addmember =false;
      
      if($request->user()->profile->household_income > $household_income){
       $addmember =true;
     }
      if($household_income > 3400){
    
        $spo_housemember = $request->user()->profile->housemember()->get();
        if($spo_housemember->isNotEmpty()){
            $request->user()->profile->housemember()->delete();
        }
        $spo_application->status ='REJECTED';
        $spo_application->active =0;
        $spo_application->save();
       // $request->user()->profile->housemember()->delete();
        $spo_application->delete();
        $modal=[
            "title"   => "attention",
            "body" => __('We regret to inform you that we are unable to offer you Sponsored Insurance because your monthly household income exceeds RM3,170.
            If this is not your latest monthly household income, please update it.'),
            "buttons" => [
    
                   [
                  "title" => __('ok'),
                  "action" => NextPage::DASHBOARD,
                  "type" => "page",
        ],
        ]
        
           ];
     return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
      }

      if($spo_application){
        //$spo_application->status ='SUBMITTED';
        $spo_application->active=1;
        $spo_application->save();
      }

      $coverages_id=$request->user()->profile->id;

      $fetchcoverage=Coverage::where('owner_id', $coverages_id)->where('status',Enum::COVERAGE_STATUS_UNPAID)->get();
  
      foreach($fetchcoverage as $fc){
        $fc->status = Enum::COVERAGE_STATUS_TERMINATE ;
        $fc->save();
      }
      return [
        'status' => 'success',
        'data'   =>[
            'next_page'=>$nextpage,
            'addmember'=>$addmember
           
        ],
        
    ];
    }

    public function doctype(){
        $docjson = file_get_contents(base_path('resources/json/doctype.json'));
        $docData = json_decode($docjson);
        return [
			'status' => 'success',
			'data'   => [
                'docData'=>$docData,
               
            ],
        ];
    }


    public function deletemember(Request $request){
        $sop_id =SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first()->id;
        $house_hold_member =SpoHouseholdMembers::where('sop_id', $sop_id)->where('nric',$request->member_kad)->first();
        $house_hold_member->delete();
        $nextpage=NextPage::HOUSEMEMBER;
        return [
			'status' => 'success',
			'data'   => [
                'nextpage'=>$nextpage,
               
            ],
        ];
			

    }

     public function deletespo(Request $request){
        $spo_application=SpoCharityFundApplication::where('user_id',$request->user()->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
        // $spo_housemember = $request->user()->profile->housemember()->get();
        // if($spo_housemember->isNotEmpty()){
        //     $request->user()->profile->housemember()->delete();
        // }
        $spo_application->status ='CANCELLED';
        $spo_application->active =0;
        if($spo_application->Corporate_SPO_confirm==1){
            $spo_application->remark = 'This application is cancelled due to corporate payer offer';
        }
        $spo_application->save();
        //$request->user()->profile->housemember()->delete();
        //$spo_application->delete();
        $sop_coverages=Coverage::where('payer_id',$request->user()->profile->user_id)->where('status','unpaid')->get();
			if($sop_coverages->isNotEmpty()){
			foreach ($sop_coverages as $sop_coverage){
				$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				$sop_coverage->save();
			}
		}



        return [
			'status' => 'success',
        ];
    }
    
    
     public function deletepayoroffer(Request $request){
        $payer_coverages =Coverage::where('owner_id',$request->user()->profile->id)->where('payer_id','<>',$request->user()->profile->user_id)->where('status','unpaid')->get();
        foreach( $payer_coverages as  $payer_cov){
            $payer_cov->status =Enum::COVERAGE_STATUS_TERMINATE;
            $payer_cov->save();
        }

        return [
			'status' => 'success',
        ];  
    }



}


