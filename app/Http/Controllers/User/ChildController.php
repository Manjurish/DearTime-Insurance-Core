<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ChildController extends Controller
{
    public function index()
    {
        $childs = auth()->user()->profile->childs;

        foreach ($childs as $child) {
            if(!empty($child->dob)) {
                $child->date_birth = Carbon::parse($child->dob)->format('d/m/Y');
            }
        }

        return view('user.child',compact('childs'));
    }

}
