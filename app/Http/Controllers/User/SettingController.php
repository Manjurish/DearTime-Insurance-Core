<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function language()
    {
        return redirect()->route('userpanel.dashboard.main');

    }


}
