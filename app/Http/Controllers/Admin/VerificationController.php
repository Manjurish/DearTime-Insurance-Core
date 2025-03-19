<?php     

namespace App\Http\Controllers\Admin;

use App\CustomerVerification;
use App\CustomerVerificationDetail;
use App\Helpers;
use App\Individual;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Beneficiary;
use App\InternalUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mmeshkatian\Ariel\BaseController;


class VerificationController extends Controller
{
	public function index()
	{
		return view('admin.ekyc.index');
	}

    public function configure()
    {
        $this->addBladeSetting('hideCreate',true);
        $this->set("disableSort",true);
        $this->model = CustomerVerification::class;
        $this->addQueryCondition("orderBy",[DB::raw('FIELD(status, "Pending", "Rejected", "Accepted")')]);
        $this->addQueryCondition("orderBy",['updated_at','asc']);

        $this->setTitle("User Verification");
        $this->addColumn("RefNo",'individual.user.ref_no');
        $this->addColumn("Name",'individual.user.name');
        $this->addColumn("Selfie Match",function ($q){
            return ($q->individual->selfieMatch->percent ?? 0 ).' %';
        });

        $this->addColumn("Last Action",function ($q){
            $last_action = $q->lastDetail;
            if(empty($last_action)){
                return "-";
            }else {
                return "By : " . ($last_action->creator->name ?? '-') . "<br>" .
                    "Update : " . ($last_action->updated_at->format("d/m/Y H:i A") ?? '-') . "<br>" .
                    "Status : " . ($last_action->status ?? '-');
            }
        });
        $this->addColumn("Submitted At",function ($q){
            return Carbon::parse($q->updated_at)->format('d/m/Y H:i A');
        });


        $this->addAction('admin.Verification.verify','<i class="feather icon-user-check"></i>','Check Verification',['$id'],Helpers::getAccessControlMethod());

        return $this;

    }

    public function verify($id)
    {
        $kyc = CustomerVerification::find($id);
        $data = $kyc->individual->user ?? null;
        if(empty($data))
            abort(403);
        $details =CustomerVerificationDetail::where('kyc_id',$kyc->id)->latest()->first();
        $verification_doc =$details->documents()->get();
        if($verification_doc->isEmpty()){
            $doc_exist =True;
            $ver_doc = CustomerVerificationDetail::where('kyc_id',$kyc->id)->first();     
        }else{
            $doc_exist =False;
            $ver_doc =null;
        }
       
        $class = CustomerVerificationDetail::where('kyc_id',$kyc->id)->latest()->first();
         $classification_1 = $class->classification;
        if($classification_1 == ''){
            $data1 =True;
            $ver_data =null;
        }else{
            $data1 =False;
            $ver_data =$class->classification;
        }

        return view('admin.verify',compact('data','kyc','doc_exist','ver_doc','data1','ver_data'));
    }

    public function submitVerify($id,Request $request)
    {
        $kyc = CustomerVerification::find($id);
        $data = $kyc->individual->user ?? null;
        if(empty($data))
            abort(403);
       
        //$this->validate($request,[
           //'verification_status'    => 'required|in:Accepted,Rejected',
        //]);
        if(empty($request->input('verification_status')) && !empty($request->input('classification'))){
            $kycnew = CustomerVerificationDetail::where('kyc_id',$kyc->id)->latest()->first();
            $kycnew->classification = $request->input('classification');
            $kycnew->classification_created_by =InternalUser::where('id',auth('internal_users')->id())->first()->name;
            $kycnew->classification_created_at =Carbon::now();
            $kycnew->save();
            
        }else{
        $detail = new CustomerVerificationDetail();
        $detail->kyc_id = $kyc->id;
        $detail->status = $request->input('verification_status');
        if ($detail->status == 'Rejected'){
            $detail->note = implode(" . ",$request->input('verification_details'));
            }
            else{
                ($detail->note = $request->input('verification_details'));
            }
        $detail->classification = $request->input('classification');
        $detail->reason_for_ekyc = $request->input('note');
        $detail->description = $request->input('description');
        $detail->type = 'staff';
        $detail->created_by = auth('internal_users')->id();
        if(!empty($request->input('classification'))){
        $detail->classification_created_by =InternalUser::where('id',auth('internal_users')->id())->first()->name;
        $detail->classification_created_at =Carbon::now();
        }
        $detail->save();

        $kyc->status = $request->input('verification_status');
        $kyc->save();

        if($request->input('verification_status') == 'Rejected'){
            if($request->input('redo_request')){
                $buttons = ['verify_now'];
            }else{
                $buttons = [];
            }

            if($detail->note == 'Other'){
                $data->sendNotification(
                    __('notification.KYC_verification_rejected.title'),
                    __('notification.KYC_verification_rejected.body'). $request->input('note'),
                    [
                        'command'   =>  'next_page',
                        'data'      =>  'verification_page',
                        'buttons'   =>  $buttons
                    ]);
            }else{
                $data->sendNotification(
                    __('notification.KYC_verification_rejected.title'),
                    __('notification.KYC_verification_rejected.body'). $detail->note,
                    [
                        'command'   =>  'next_page',
                        'data'      =>  'verification_page',
                        'buttons'   =>  $buttons
                    ]);
            }
           
        }
        
                $email =$kyc->individual->user->email;
                $beneficiary = Beneficiary::where("email",$email)->get();
                foreach($beneficiary as $bn){
                    if($request->input('verification_status') == 'Accepted'){
                        $bn->status     = 'registered';
                        $bn->save();
                     }elseif($request->input('verification_status') == 'Rejected'){
                        $bn->status     = 'pending';
                        $bn->save();
                     }
                  }
                }
        return redirect()->route('admin.Verification.index')->with("success_alert","Verification status updated");

    }
}
