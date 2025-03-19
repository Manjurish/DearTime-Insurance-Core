<?php     

namespace App\Http\Controllers\User;

use App\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForeignController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        //accessDenied($user->profile->is_local());

        $foreign_questions = \App\ForeignQuestion::get();
        $sources = [
            'nationalities' =>  Country::get(),
        ];
        return view('user.foreign',compact('foreign_questions','sources'));

    }

}
