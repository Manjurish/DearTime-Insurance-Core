<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Underwriting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    public function index()
    {
        return view('user.clinic');
    }

    public function review()
    {
        $uws = Underwriting::where("created_by",auth()->id())->orderBy("created_at","desc")->get()->unique('individual_id');
        return view('corporate.review',compact('uws'));
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'email' =>  'required|email|exists:users,email',
            'nric'  =>  'required|exists:individuals,nric',
        ]);

        $user = User::where("email",$request->input('email'))->first();
        if(($user->profile->nric ?? null) != $request->input('nric')) {
            throw ValidationException::withMessages([
                'nric' => __('mobile.nric_mismatch'),
            ]);
        }

        $uw = Underwriting::where("individual_id",$user->profile->id)->where("created_by",'-1')->first();
        if(empty($uw)) {
            $uw = new Underwriting();
        }

        $uw->individual_id  = $user->profile->id;
        $uw->answers        = json_encode(['weight'=>0,'height'=>0,'smoke'=>0,'answers'=>[]]);
        $uw->death          = 0;
        $uw->disability     = 0;
        $uw->ci             = 0;
        $uw->medical        = 0;
        $uw->created_by     = auth()->id();
        $uw->save();

        return redirect()->route('userpanel.clinic.fill',$uw->uuid);
    }

    public function fill(Request $request,$id)
    {
        $uw = Underwriting::where('uuid',$id)->first();

        //accessDenied(empty($uw) || $uw->created_by != auth()->id());

        $title = $uw->individual->name ?? '';
        $request->merge(['uid'=>$uw->individual->user->uuid]);
        return app('App\Http\Controllers\User\MedicalSurveyController')->index($request,$title);

    }

}
