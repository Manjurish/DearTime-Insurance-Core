<?php     

namespace App\Http\Controllers\Api;

use App\City;
use App\State;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function stateList(Request $request){

        if(!$request->id){
            $result = State::select("uuid","name")->get();
        } else {
            $res = State::whereUuid($request->id);
            if($res->count() != 0)
                $result = $res->first()->cities();
            else {
                $result = City::whereUuid($request->id)->first()->postalCodes();
            }

            $result = $result->select("uuid","name")->get();

        }
        if($request->initial) {
            return $result;
        }
        return ['status' => 'success', 'data' => $result];
    }
}
