<?php     

namespace App\Http\Controllers\User;

use App\Address;
use App\CharityApplicant;
use App\Company;
use App\Country;
use App\Helpers;
use App\Http\Controllers\Controller;
use App\Individual;
use App\IndustryJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class ProfileController extends Controller
{
    public function index()
    {

        $user = auth()->user();
        if($user->isIndividual()) {
            $dob                    = '';
            $passport_expiry_date   = '';
            $profile                = $user->profile;
            if (empty($profile)) {

                $profile = new Individual();
                $profile->user_id   = $user->id;
                $profile->name      = '';
                $profile->mobile    = '';
                $profile->save();

            }
            $profile->nationality   = (empty($profile->nationality) ? 'Malaysian' : $profile->nationality);

            if(!empty($profile->dob)) {
                //$dob = $profile->dob->format('d/m/Y');
				$dob = Carbon::parse($profile->dob)->format('d/m/Y');
			}

            if(!empty($profile->passport_expiry_date)) {
                $passport_expiry_date = $profile->passport_expiry_date->format('d/m/Y');
            }

            return view('user.profile', compact('user', 'profile','passport_expiry_date','dob'));

        }elseif(auth()->user()->isCorporate()){
            $user = auth()->user();
            $profile = $user->profile;
            if (empty($profile)) {
                $profile = new Company();
                $profile->user_id   = $user->id;
                $profile->name      = '';
                $profile->reg_no    = '';
                $profile->save();
            }
            return view('corporate.profile',compact('user','profile'));
        }
    }

    public function store(Request $request)
    {
        if(auth()->user()->active != 1) {
            return redirect()->route('userpanel.dashboard.profile')->with("danger_alert", __('mobile.account_disabled'));
        }

        if(auth()->user()->isIndividual()) {

            $this->validate($request, [
                'nationality'               => ['required',Rule::exists('countries','uuid')->where('is_allowed','1')],
                'passport'                  => 'required',
                'name'                      => 'required',
                'dob'                       => 'required|date_format:d/m/Y',
                'address1'                  => 'required|max:30',
                'address2'                  => 'max:30',
                'address3'                  => 'max:30',
                'state'                     => 'required',
                'city'                      => 'required',
                'religion'                  => 'required|in:muslim,non_muslim',
                'zip_code'                  => 'required',
                'industry'                  => 'required',
                'gender'                    => 'required|in:male,female,Male,Female',
                'job'                       => 'required',
                'monthly_personal_income'   => 'required',
                'monthly_household_income'  => 'required',
            ]);
            if(Carbon::createFromFormat("d/m/Y",$request->input('dob'))->age < 16) {
                throw ValidationException::withMessages([
                    'dob' => __('mobile.dob_error_validation'),
                ]);
            }
            $user       = auth()->user();
            $individual = $user->profile;
            $new        = empty($individual->nric);
            $individual->nric               = $request->input('passport');
            $individual->name               = $request->input('name');
            $individual->religion           = $request->input('religion');
            $individual->country_id         = Country::whereUuid($request->input('nationality'))->first()->id ?? null;
            $individual->dob                = Carbon::createFromFormat("d/m/Y",$request->input('dob'));
            $individual->household_income   = $request->input('monthly_household_income');
            $individual->personal_income    = $request->input('monthly_personal_income');

            $job = IndustryJob::whereUuid($request->input('job'))->first()->id ?? 0;
            $individual->occ                        = $job;
            $individual->has_other_life_insurance   = $request->input('has_other_life_insurance') == '1' ? '1' : '0';
            $individual->passport_expiry_date       = empty($request->input('passport_expiry_date')) ? now() : Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));

            $address = Address::where("id", $individual->address_id)->first();

            if (empty($address)) {
                $address = new Address();
            }

            $address->type      = 'residential';
            $address->address1   = $request->input('address1');
            $address->address2   = $request->input('address2');
            $address->address3   = $request->input('address3');
            $address->city      = $request->input('city');
            $address->state     = $request->input('state');
            $address->postcode  = $request->input('zip_code');
            $address->country   = '';
            $address->save();

            $individual->address_id             = $address->id;
            $individual->gender                 = strtolower($request->input('gender'));
            $individual->is_restricted_foreign  = '0';
            $individual->save();

            if($new) {
                $user->sendNotification(__('notification.verification.title'), __('notification.verification.body'),
                    [
                        'command'   => 'next_page',
                        'data'      => 'verification_page',
                        'id'        => 'verification',
                        'buttons'   => [
                            'verify_now',
                            'cancell'
                        ],
                        'auto_read' => false
                    ]);
            }

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

                $individual = Individual::find($individual->id);
                if(!$individual->is_local()){
                    return redirect()->route('userpanel.foreign.index');
                }

                return redirect()->route('userpanel.dashboard.main');
            }
        }elseif(auth()->user()->isCorporate()){
            $this->validate($request,[
                'name'          => 'required',
                'address1'      => 'required|max:30',
                'address2'      => 'max:30',
                'address3'      => 'max:30',
                'state'         => 'required',
                'city'          => 'required',
                'zip_code'      => 'required',
                'relationship'  => 'required|in:'.implode(",",array_keys(config('static.relationships'))),
                'tel_no'        => 'required',
            ]);

            $user = auth()->user();
            $company = $user->profile;
            $company->type                  = 'corporate';
            $company->relationship          = $request->input('relationship');
            $company->tel_no                = $request->input('tel_no');
            $company->name                  = $request->input('name');
            $company->corporate_verified    = '0';

            $address = Address::where("id", $company->address_id)->first();
            if (empty($address)) {
                $address = new Address();
            }
            $address->type          = 'residential';
            $address->address1       = $request->input('address1');
            $address->address2       = $request->input('address2');
            $address->address3       = $request->input('address3');
            $address->city          = $request->input('city');
            $address->state         = $request->input('state');
            $address->postcode      = $request->input('zip_code');
            $address->country       = '';
            $address->save();
            $company->address_id    = $address->id;
            $company->save();

			$isPanelHospital = Company::query()->where('user_id', $user->id)->where('relationship', 'Panel Hospital')->exists();

			if($user->isCorporate() && $isPanelHospital){
				return redirect()->route('userpanel.hospital.claim');
			}

            return redirect()->back()->with("success_alert",__('mobile.profile_saved'));
        }
    }

    public function storeDocument(Request $request)
    {
        $user = auth()->user();
        $company = $user->profile;
        ////accessDenied($user->isIndividual());

        Helpers::crateDocumentFromUploadedFile($request->file('image'), $company, 'company_doc');

        return "1";
    }
    public function destroyDocument(Request $request)
    {
        $user = auth()->user();
        $company = $user->profile;
        ////accessDenied($user->isIndividual());

        $company->documents()->where("type","company_doc")->where("name",$request->input('id'))->delete();

        return '1';

    }

}
