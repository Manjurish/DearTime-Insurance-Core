<?php     

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class BeneficiaryController extends Controller
{
    public function index()
    {
        $nominees = auth()->user()->profile->nominees;
        foreach ($nominees as $nominee) {
            if(!empty($nominee->dob)) {
                $nominee->dob = Carbon::parse($nominee->dob)->format('d/m/Y');
            }
            if(!empty($nominee->passport_expiry_date)) {
                $nominee->passport_expiry_date = Carbon::parse($nominee->passport_expiry_date)->format('d/m/Y');
            }
        }

        return view('user.beneficiary',compact('nominees'));
    }

    public function store(Request $request)
    {

        $payload = json_decode($request->input('nominees_data'));
        $request->request->add([
            'payload'   =>  json_encode(['nominees'=>$payload])
        ]);
        return app(\App\Http\Controllers\Api\BeneficiaryController::class)->set($request);
    }

}
