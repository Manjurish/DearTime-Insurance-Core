<?php     

namespace App\Http\Controllers\Admin;
use App\SpoCharityFunds;
use App\Individual;
use App\SpoHouseholdMembers;
use Carbon\Carbon;
use App\User;
use App\Order;
use App\Coverage;
use App\Transaction; 
use App\Notifications\Email;
use App\Helpers\Enum;
use App\SpoCharityFundApplication;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpoInsuranceController extends Controller
{

    public function index()
	{
		return view('admin.spo.index');
	}

    public function sopsummary(){

		$labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

		$title  = 'Sponsored Inurance Applied Monthly ' . Carbon::now()->year;
		$users   = [];

		for ($i = 1; $i <= 31; $i++) {
			$usersapplied[] = SpoCharityFundApplication::where(\DB::raw("DATE_FORMAT(created_at, '%m')"),$i)->count();
		}

		
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

		  $soponhold_fund =SpoCharityFunds::where('status','ON HOLD')->sum('charity_fund');

		  $sopcovered=SpoCharityFundApplication::where('status','ACTIVE')->count();
		  $sopinline =SpoCharityFundApplication::where('status','QUEUE')->count();
		
		  //$percentagecovered =$sopcovered

		return view('admin.spo.spodashboard',compact('charityfundsum','sopcovered','sopinline','soponhold_fund'))
		->with('labels',json_encode($labels))
		->with('users',json_encode($usersapplied,JSON_NUMERIC_CHECK));
		
		//dd(($charityfundsum));
    }
	public function details($uuid)
	{
		$data =SpoCharityFundApplication::where('uuid',$uuid)->first();
		$name =Individual::where('user_id',$data->user_id)->first()->name;
		$individual_id =Individual::where('user_id',$data->user_id)->first()->id;
		
		//dd($data);
		$docs = $data->documents()->get();
		$members = SpoHouseholdMembers::where('individual_id',$individual_id)->where('sop_id',$data->id)->get();
		//dd($docs);
		return view('admin.spoapplicant-details',compact('data','docs','name','members'));

 
	}
	public function updatestatus(Request $request){

		$input = $request->input();

		$data =SpoCharityFundApplication::where('uuid',$input['id'])->first();
		//dd($data);
		$data->status =$request->input('applicant_status');
		$data->save();
		$applicantuser =User::where('id',$data->user_id)->first();
		if($request->input('applicant_status')=='REJECTED'){

			$applicantuser->sendNotification('Attention', 'mobile.failed_spo_application', [
				'translate_data' => ['name' =>$applicantuser->profile->name],
				'buttons'   => ['Ok'],
			
			]);

            $sop_coverages=Coverage::where('payer_id',$applicantuser->id)->where('status','unpaid')->get();
			if($sop_coverages->isNotEmpty()){
			foreach ($sop_coverages as $sop_coverage){
				$sop_coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
				$sop_coverage->save();
			}
			//$applicantuser->profile->housemember()->delete();
			$data->active =0;
			$data->save();
            //$data->delete();
		}
	}
		if($request->input('applicant_status')=='QUEUE'){

			$applicantuser->sendNotification('Attention', 'mobile.verified_spo_application', [
				'translate_data' => ['name' =>$applicantuser->profile->name],
				'command'   => 'next_page',
				'data'      => 'spodashboard_page',
				//'id'        => 'verification',
				'buttons'   => ['Sponsored Insurance'],
				'auto_read' => FALSE
			]);

			$textEmail  = __('web/messages.verified_spo_application', ['name' =>  ucwords(strtolower($applicantuser->profile->name))]);


			$applicantuser->notify(new Email($textEmail));
			
		}

		
		
		return redirect()->route('admin.spo.index')->with("success_alert","Application status updated");



	}
}
