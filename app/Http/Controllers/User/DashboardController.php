<?php     

namespace App\Http\Controllers\User;

use App\Company;
use App\Helpers;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function main()
    {
		$user = \Auth::user();
		$isPanelHospital = Company::query()->where('user_id', $user->id)->where('relationship', 'Panel Hospital')->exists();
		if($user->isCorporate() && $isPanelHospital){
			return redirect()->route('userpanel.hospital.claim');
		}
        return view('pages.dashboard');
    }

    public function go($route)
    {
        return redirect(Helpers::route($route));
    }

}
