<?php     

namespace App\Http\Controllers\User;

use App\CharityApplicant;
use App\Coverage;
use App\Helpers;
use App\Individual;
use App\Underwriting;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MedicalSurveyController extends Controller
{
    public function index(Request $request,$title = '')
    {

        $user = auth()->user();
        $profile = $user->profile;

        if($request->has('start-over')){

            $uw = $profile->underwritings()->where("created_by","-1")->first();
            if(Coverage::where("uw_id",$uw->id)->count() == 0){
                $uw->delete();
                return redirect()->route('userpanel.MedicalSurvey.index');
            }
        }
        if(!empty($request->input('uid'))){

            //check if clinic
            if($user->isCorporate() && !empty($user->profile) && $user->profile->isClinic()){

                $u = User::where("uuid", $request->input('uid'))->withPendingPromoted()->first();
                $uw = $u->profile->underwritings ?? null;
                //accessDenied(empty($uw));
                $user = $u;

                $profile = $user->profile;

            }else {
                $user = User::where("uuid", $request->input('uid'))->onlyPendingPromoted()->where("promoter_id", $user->id);
                if ($user->count() == 0){
                    $profile = Individual::OnlyChild()->where("uuid", $request->input('uid'))->first();
                    $user = $profile->user;
                    //accessDenied(empty($user));

                }else {
                    $user = $user->first();
                    $profile = $user->profile;
                }
            }
        }

        $hasUnderWriting = $profile->underwritings()->count() > 0;
        $uid = $request->input('uid') ?? 0;
        if(!empty($uid)) {
            $hasUnderWriting = true;
        }

        if($request->has('fill-by-clinic')){
            if(!$hasUnderWriting) {
                $underWriting = New Underwriting();
                $underWriting->individual_id    = auth()->user()->profile->id ?? 0;
                $underWriting->answers          = '{}';
                $underWriting->death            = 0;
                $underWriting->disability       = 0;
                $underWriting->ci               = 0;
                $underWriting->medical          = 0;
                $underWriting->created_by       = -1;//by clinic
                $underWriting->save();

                return redirect()->route('userpanel.MedicalSurvey.index',['uid'=>$uid]);
            }
        }

        $clinic = false;
        if($hasUnderWriting) {
            $clinic = ($profile->underwritings->created_by ?? null) == -1;
        }

        $firstTime = $profile->underwritings()->count() == 0;

        if($clinic || $request->has('fill-by-clinic')) {
            return view('user.clinic');
        }

        $fill_type = $request->input('fill_type') == 'pay_for_others' ? 'pay_for_others' : '';
        return view('user.medicalsurvey',compact('hasUnderWriting','firstTime','uid','title','fill_type'));
    }
}
