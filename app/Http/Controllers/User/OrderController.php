<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Individual;
use App\User;

class OrderController extends Controller
{
    public function index()
    {
        $covs = auth()->user()->profile->coverages_owner()->where("status","unPaid")->count();
        if($covs == 0) {
            return redirect()->route('userpanel.product.index')->with("danger_alert", __('mobile.no_coverage'));
        }

        if(empty(auth()->user()->profile->bankCards()->first()->token)) {
            return redirect()->route('userpanel.bank_account.index')->with("danger_alert", __('mobile.no_credit_card'));
        }

        return view('user.order_detail');
    }
    public function other($uid)
    {
        $user = User::withPendingPromoted()->whereUuid($uid)->first();
        if(empty($user)){
            $individual = Individual::withChild()->where("uuid", $uid)->first();
            if(empty($individual)) {
                abort(404);
            }
            $user = $individual->user;
            $user->profile = $individual;
        }

        $covs = auth()->user()->profile->coverages_payer()->where("covered_id",$user->profile->id ?? null)->where("status","unPaid")->count();
        if($covs == 0) {
            return redirect()->route('userpanel.product.index')->with("danger_alert",__('mobile.no_coverage'));
        }

        if(empty(auth()->user()->profile->bankCards()->first()->token)) {
            return redirect()->route('userpanel.bank_account.index')->with("danger_alert", __('mobile.no_credit_card'));
        }

        return view('user.order_detail',compact('uid'));
    }
}
