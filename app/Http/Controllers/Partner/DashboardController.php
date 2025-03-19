<?php     

namespace App\Http\Controllers\Partner;

use App\Address;
use App\CharityApplicant;
use App\Company;
use App\Helpers;
use App\Http\Controllers\Controller;
use App\Individual;
use App\IndustryJob;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function main()
    {
        return view('pages.dashboard');
    }

    public function profile()
    {
        $user = auth()->user();
        if($user->isIndividual()) {
            $dob = '';
            $passport_expiry_date = '';
            $profile = $user->profile;
            if (empty($profile)) {
                $profile = new Individual();
                $profile->user_id = $user->id;
                $profile->name = '';
                $profile->mobile = '';
                $profile->save();
            }
            $profile->nationality = (empty($profile->nationality) ? 'Malaysian' : $profile->nationality);

            if(!empty($profile->dob))
                $dob = $profile->dob->format('d/m/Y');

            if(!empty($profile->passport_expiry_date))
                $passport_expiry_date = $profile->passport_expiry_date->format('d/m/Y');

            return view('user.profile', compact('user', 'profile','passport_expiry_date','dob'));
        }elseif(auth()->user()->isCorporate()){
            $user = auth()->user();
            $profile = $user->profile;
            if (empty($profile)) {
                $profile = new Company();
                $profile->user_id = $user->id;
                $profile->name = '';
                $profile->reg_no = '';
                $profile->save();
            }
            return view('corporate.profile',compact('user','profile'));
        }

    }

    public function saveProfile(Request $request)
    {
        if(auth()->user()->isIndividual()) {

            $this->validate($request, [
                'nationality' => 'required|in:' . implode(",", config('static.nationalities')),
                'passport' => 'required',
                'name' => 'required',
                'dob' => 'required',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'zip_code' => 'required',
                'industry' => 'required',
                'gender' => 'required|in:male,female',
                'job' => 'required',
                'monthly_personal_income' => 'required',
                'monthly_household_income' => 'required',
            ]);
            $user = auth()->user();
            $individual = $user->profile;
            $firstTime = !$user->ProfileDone;
            $individual->nric = $request->input('passport');
            $individual->name = $request->input('name');
            $individual->nationality = $request->input('nationality');
            $individual->dob = Carbon::createFromFormat("d/m/Y",$request->input('dob'));
            $individual->household_income = $request->input('monthly_household_income');
            $individual->personal_income = $request->input('monthly_personal_income');
            $job = IndustryJob::whereUuid($request->input('job'))->first()->id ?? 0;
            $individual->occ = $job;
            $individual->has_other_life_insurance = $request->input('has_other_life_insurance') == '1' ? '1' : '0';
            $individual->passport_expiry_date = empty($request->input('passport_expiry_date')) ? now() : Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));
            $address = Address::where("id", $individual->address_id)->first();
            if (empty($address)) {
                $address = new Address();
            }
            $address->type = 'residential';
            $address->address = $request->input('address');
            $address->city = $request->input('city');
            $address->state = $request->input('state');
            $address->postcode = $request->input('zip_code');
            $address->country = '';
            $address->save();
            $individual->address_id = $address->id;
            $individual->gender = $request->input('gender');
            $individual->save();
            if ($individual->household_income >= 3000) {
                $applications = CharityApplicant::where("individual_id", $individual->id)->first();
                if (!empty($applications)) {
                    $applications->documents()->delete();
                    $applications->delete();
                }
            }
            if ($request->input('charity') == '1' && $individual->household_income < 3000) {

                return redirect()->route('userpanel.charity.index');
            } else {
                if($firstTime)
                    return redirect()->route('userpanel.product.index');

                return redirect()->route('userpanel.dashboard.profile');
            }
        }elseif(auth()->user()->isCorporate()){
            $this->validate($request,[
                'name' => 'required',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'zip_code' => 'required',
                'relationship' => 'required|in:'.implode(",",array_keys(config('static.relationships'))),
                'tel_no' => 'required',
            ]);
            $user = auth()->user();
            $company = $user->profile;
//            $company->name = '';
//            $company->reg_no = ;
            $company->type = 'corporate';
            $company->relationship = $request->input('relationship');
            $company->tel_no = $request->input('tel_no');
            $company->name = $request->input('name');
            $company->corporate_verified = '0';

            $address = Address::where("id", $company->address_id)->first();
            if (empty($address)) {
                $address = new Address();
            }
            $address->type = 'residential';
            $address->address = $request->input('address');
            $address->city = $request->input('city');
            $address->state = $request->input('state');
            $address->postcode = $request->input('zip_code');
            $address->country = '';
            $address->save();
            $company->address_id = $address->id;
            $company->save();
            return redirect()->back()->with("success_alert","Profile Data Updated Successfully");


        }

    }

    public function saveProfileDoc(Request $request)
    {
        $user = auth()->user();
        $company = $user->profile;
        //accessDenied($user->isIndividual());

        $selfie = Helpers::crateDocumentFromUploadedFile($request->file('image'), $company, 'company_doc');
    }

    public function removeProfileDoc(Request $request)
    {
        $user = auth()->user();
        $company = $user->profile;
        //accessDenied($user->isIndividual());

        $company->documents()->where("type","company_doc")->where("name",$request->input('id'))->delete();
        return '1';

    }

}
