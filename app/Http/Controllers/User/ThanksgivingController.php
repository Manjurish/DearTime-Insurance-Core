<?php     

namespace App\Http\Controllers\User;

use App\User;
use App\Http\Controllers\Controller;

class ThanksgivingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $firstTime  = $user->profile->thanksgiving()->count() == 0;
        $self       = @ ($user->profile->thanksgiving()->where("type","self")->first()->percentage ?? 0) /10;
        $charity    = @ ($user->profile->thanksgiving()->where("type","charity")->first()->percentage ?? 0) /10;
        $promoter   = @ ($user->profile->thanksgiving()->where("type","promoter")->first()->percentage ?? 0) /10;


        $promoter_allowed = $user->promoter_id != null;
        $self_allowed = $user->profile->household_income <= 4000;
        if(!$promoter_allowed && !$self_allowed){
            $charity = 10;
        }

        $promoter_name = User::find($user->promoter_id)->name ?? '';
        return view('user.thanksgiving',compact('firstTime','self','charity','promoter','promoter_allowed','self_allowed','promoter_name'));
    }
}
