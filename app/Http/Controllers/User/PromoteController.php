<?php     

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PromoteController extends Controller
{
    public function index()
    {
        return view('user.promote');
    }

    public function myPromoted()
    {
        $promoteds = User::WithPendingPromoted()->where("promoter_id",auth()->id())->get();
        if($promoteds->count() == 0)
            return redirect()->back()->with("danger_alert",__('mobile.no_promoter'));
        return view('user.my_promoted',compact('promoteds'));
    }
    public function medicalSurvey(Request $request,$uid)
    {
        $user = User::OnlyPendingPromoted()->whereUuid($uid)->first();
        if(empty($user)) {
            abort(404);
        }

        $user_name = $user->profile->name ?? '-';
        $request->request->add(['uid'=>$uid]);

        return app(MedicalSurveyController::class)->index($request,'('.__('mobile.filling_for').' : '.$user_name.')');

    }
    public function product(Request $request,$uid)
    {
        $user = User::OnlyPendingPromoted()->whereUuid($uid)->first();
        if(empty($user)) {
            abort(404);
        }

        $request->request->add(['uid'=>$uid]);
        $user_name = $user->profile->name ?? '-';

        return app(ProductController::class)->index($request,'('.__('mobile.filling_for').' : '.$user_name.')');


    }


}
