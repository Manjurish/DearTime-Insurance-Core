<?php     

namespace App\Http\Controllers\User;

use App\CharityApplicant;
use App\Http\Controllers\Controller;

class SponsorController extends Controller
{
    public function index()
    {
        return view('user.sponsor');
    }

    public function getData()
    {
        $charities = CharityApplicant::where("active","1")->oldest()->paginate(48);
        return ['data'=>$charities];
    }

}
