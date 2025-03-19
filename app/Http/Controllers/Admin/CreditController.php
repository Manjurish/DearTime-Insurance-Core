<?php     

namespace App\Http\Controllers\Admin;

use App\Credit;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function getUserCreditsLog(Request $request,$uuid){
        $user = User::where('uuid',$uuid)->first();
        $promote = $request->input('promote') ?? false;

        $breadcrumbs = [
            ['name'=>'Admin Area','link'=>route('admin.dashboard.main')],
            ['name'=>'User Detail','link'=>route('admin.User.show',$user->uuid)],
            ['name'=>'Credits','link'=>url()->current()],
        ];

        return view('admin.user.credit-list',compact('user','promote','breadcrumbs'));
    }

    public function credits(){
        $breadcrumbs = [
            ['name'=>'Admin Area','link'=>route('admin.dashboard.main')],
            ['name'=>'Credits','link'=>url()->current()],
        ];
        return view('admin.credit.credit-list',compact('breadcrumbs'));
    }
}
