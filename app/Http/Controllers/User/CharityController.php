<?php     

namespace App\Http\Controllers\User;

use App\CharityApplicant;
use App\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CharityController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if(($user->profile->household_income ?? 0) >= 3000) {
            return redirect()->back()->with("danger_alert", __("web/charity.not_eligable"));
        }

        $charity = CharityApplicant::where("individual_id",$user->profile->id ?? 0)->first();

        if(empty($charity)){
            $charity = new CharityApplicant();
            $charity->individual_id = $user->profile->id ?? 0;
            $charity->active        = 3;
            $charity->dependants    = 0;
            $charity->save();
        }
        if($charity->active == '1') {
            return redirect()->back()->with("success_alert", __("web/charity.approved"));
        }

        return view('user.charity',compact('user','charity'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'about_self'            =>  'required',
            'sponsor_thank_note'    =>  'required',
            'dependants'            =>  'required|numeric',
        ]);

        $user = auth()->user();

        $charity = CharityApplicant::where("individual_id",$user->profile->id ?? 0)->first();
        $charity->about_self            = $request->input('about_self');
        $charity->sponsor_thank_note    = $request->input('sponsor_thank_note');
        $charity->dependants            = $request->input('dependants');
        $charity->active                = 0;
        $charity->save();
        if(!empty($request->file('selfie'))){
            $charity->documents()->where("type","selfie")->delete();
            Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $charity, 'selfie');
        }
        if(!empty($user->profile) && !$user->profile->is_local()){
            return redirect()->route('userpanel.foreign.index');
        }

        return redirect()->route('userpanel.dashboard.main');

    }

    public function uploadDoc(Request $request,$id)
    {
        $charity = CharityApplicant::where("uuid",$id)->first();
        if(empty($charity)) {
            abort(404);
        }
        //accessDenied($charity->individual_id != (auth()->user()->profile->id ?? 0));

        Helpers::crateDocumentFromUploadedFile($request->file('image'), $charity, 'salary_proof');
    }

    public function uploadSelfie(Request $request,$id)
    {
        $charity = CharityApplicant::where("uuid",$id)->first();
        if(empty($charity)) {
            abort(404);
        }
        //accessDenied($charity->individual_id != (auth()->user()->profile->id ?? 0));

        $charity->documents()->where("type","selfie")->delete();
        Helpers::crateDocumentFromUploadedFile($request->file('image'), $charity, 'selfie');
        return '1';
    }

    public function removeDoc(Request $request ,$id)
    {
        $charity = CharityApplicant::where("uuid",$id)->first();
        if(empty($charity)) {
            abort(404);
        }
        //accessDenied($charity->individual_id != (auth()->user()->profile->id ?? 0));

        $charity->documents()->where("name",$request->input('id'))->delete();
        return '1';
    }
}
