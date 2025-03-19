<?php

namespace App\Http\Controllers\Admin;

use App\Beneficiary;
use App\Individual;
use App\User;
use App\Country;
use App\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BeneficiaryController extends Controller
{
   
    public function show($id)
    {
        if (is_numeric($id))
            $data = Beneficiary::where("id", $id);
        else
            $data = Beneficiary::where("uuid", $id);

        $data = $data->withTrashed()->get()->first();
        $user =User::where("email",$data->email)->get()->first();
        $country =Country::where('id',$data->nationality)->get();
        $nationality = $country->pluck('nationality')->implode(' ');
        // dd($nationality);
        
        
        
        $nominee = $data;
        return view('admin.ben-details', compact('nominee','nationality'));
    }
}