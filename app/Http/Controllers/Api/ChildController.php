<?php     

namespace App\Http\Controllers\Api;


use App\Country;
use App\Helpers;
use App\Individual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class ChildController extends Controller
{
    public function set(Request $request)
    {
        $request->request->add(['nationality'=>'Malaysian']);
        $user = $request->user()->profile;
        $request->validate([
           'name'=>'required',
           'gender'=>'required|in:male,female',
            'dob' => 'required|date_format:d/m/Y',
            'passport' => 'required',
            'nationality' => 'required|string',
            'passport_expiry_date' => 'required_unless:nationality,Malaysian|nullable|date_format:d/m/Y',

        ]);

        $dob = Carbon::createFromFormat("d/m/Y",$request->input('dob'));
        if($dob->age >= 16)
            throw ValidationException::withMessages([
               'dob'=>__('web/messages.child_above_16'),
            ]);

        if(!empty($request->input('uuid'))){
            $ind = $request->user()->profile->childs()->where("uuid",$request->input('uuid'))->first();

            //accessDenied(empty($ind));

        }else{
            $ind = new Individual();
        }
        $ind->user_id = $user->user_id;
        $ind->name = $request->input('name');
        $ind->nric = $request->input('passport');
        $ind->nationality = $request->input('nationality');
        $ind->country_id = Country::where("nationality",$request->input('nationality'))->first()->id ?? null;
        $ind->dob = Carbon::createFromFormat("d/m/Y",$request->input('dob'));
        $ind->gender = $request->input('gender');
        $ind->mobile = '';
        $ind->occ = '650';//child


        if(!empty($request->input('passport_expiry_date')))
            $ind->passport_expiry_date = Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));

        $ind->has_other_life_insurance = ($request->input('other_life_insurance') == '1') ? 1 : 0 ;
        $ind->in_restricted_age = '1';
        $ind->type = 'child';
        $ind->owner_id = $user->id;
        $ind->save();

        if($request->hasFile('birth_cert')) {
            $ind->documents()->where("type", "birth_cert")->delete();
            $birth_cert = Helpers::crateDocumentFromUploadedFile($request->file('birth_cert'), $ind, 'birth_cert');
        }

        return ['status' => 'success', 'data' => ['uuid'=>$ind->uuid]];
    }

    public function get(Request $request)
    {
        $childs = $request->user()->profile->childs;
        foreach ($childs as $child) {
            $child->d_o_b = $child->dob->format("d/m/Y");
        }

        return ['status'=>'success', 'data' => ['childs' => $childs]];
    }

    public function delete(Request $request)
    {
        $uuid = $request->input('uuid');
        $user = $request->user()->profile->childs()->where("uuid",$uuid)->first();
        //unAuthorized(empty($user));
        $user->delete();

        return ['status' => 'success', 'data' => []];
    }
}
