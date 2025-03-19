<?php     

namespace App\Http\Controllers\Admin;
use App\Individual;
use Carbon\Carbon;
use App\Referral;
use App\User;
use App\Helpers\Enum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReferralController extends Controller
{

    public function index()
	{
		return view('admin.referral.index');
	}
	
	public function details($uuid)
	{
       
		$data = Referral::where('uuid',$uuid)->first();
		$transaction_ref = $data->transaction_ref;
		$transaction_date = $data->transaction_date;

	   $created = $data['created_at'];
	   $now = Carbon::now();

	   $day = (date_diff(date_create(($now)), date_create(($created)))->format('%a'));

	   $days = $day." days";

		return view('admin.referral-details',compact('data','day','transaction_ref','transaction_date','uuid'));

	}
	
    public function updatestatus(Request $request, $uuid){

		$input = $request->input();

		$data =Referral::where('uuid',$uuid)->first();
		$data->transaction_date = $request->input('from');
		$data->payment_status =$request->input('payment_status');
		$data->transaction_ref = $request->input('transaction_ref');
 		$data->save();
		
		return redirect()->route('admin.referral.index')->with("success_alert","Payment status updated");

	}
}
