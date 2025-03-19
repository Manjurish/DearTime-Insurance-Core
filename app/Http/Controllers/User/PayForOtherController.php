<?php     

namespace App\Http\Controllers\User;

use App\Individual;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayForOtherController extends Controller
{
    public function medicalSurvey(Request $request,$uid)
    {
        $user = User::whereUuid($uid)->WithPendingPromoted()->first();
        if(empty($user)) {

            $individual = Individual::OnlyChild()->where("uuid", $uid)->first();
            if(empty($individual)) {
                abort(404);
            }

            $request->request->add(['uid'=>$uid,'fill_type'=>'pay_for_others']);
            $user_name = $individual->name ?? '-';

        }else{
            $request->request->add(['uid'=>$uid,'fill_type'=>'pay_for_others']);
            $user_name = $user->profile->name ?? '-';
        }
        return app(MedicalSurveyController::class)->index($request,'('.__('mobile.filling_for').' : '.$user_name.')');
    }

    public function product(Request $request,$uid)
    {
        $user = User::whereUuid($uid)->WithPendingPromoted()->first();
        if(empty($user)) {

            $individual = Individual::OnlyChild()->where("uuid", $uid)->first();
            if(empty($individual)) {
                abort(404);
            }

            $request->request->add(['uid'=>$uid,'fill_type'=>'buy_for_others_child']);
            $user_name = $individual->name ?? '-';

        }else{
            $request->request->add(['uid'=>$uid,'fill_type'=>'buy_for_others']);
            $user_name = $user->profile->name ?? '-';
        }

        return app(ProductController::class)->index($request,'(Fill for : '.$user_name.')');
    }
}
