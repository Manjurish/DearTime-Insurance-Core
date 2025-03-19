<?php     
namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class OrderController extends Controller
{
    public function getHistory(){
        $user = Auth::user()->profile;
        $paying_coverages = $user->coverages_payer()->with('payments')->get();

        return $paying_coverages;
    }
}
