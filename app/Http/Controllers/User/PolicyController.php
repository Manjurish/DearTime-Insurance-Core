<?php     

namespace App\Http\Controllers\User;

use App\Beneficiary;
use App\CharityApplicant;
use App\CustomerVerification;
use App\Helpers;
use App\Underwriting;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $list = app(\App\Http\Controllers\Api\PolicyController::class)->getList($request);
        if($list['status'] == 'success') {
            return view('user.policy', compact('list'));
        }
        else return redirect()->route('userpanel.dashboard.main')->with("danger",$list['data'] ?? ' ');
    }

    public function product(Request $request,$uid)
    {
        $user = User::withPendingPromoted()->whereUuid($uid)->first();
        if(empty($user)) {
            abort(404);
        }

        $request->request->add(['uid'=>$uid,'fill_type'=>'buy_for_others','user_id'=>$user->uuid]);
        $user_name = $user->profile->name ?? '-';
        return app(ProductController::class)->index($request,'('.__('mobile.filling_for').' : '.$user_name.')');
    }

    public function history()
    {
        $histories = auth()->user()->orders_payer()->select('id','uuid','amount','status','transaction_id','card_no','payer_id','created_at')->where("amount",">","0")->orderBy("created_at","desc")->get();

        return view('user.payment_history',compact('histories'));
    }


}
