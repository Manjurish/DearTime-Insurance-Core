<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class TimeTubeController extends Controller
{
    public function index()
    {
        return view('pages.time_tube');
    }

}
