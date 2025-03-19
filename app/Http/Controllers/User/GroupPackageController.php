<?php     

namespace App\Http\Controllers\User;

use App\CharityApplicant;
use App\GroupPackage;
use App\GroupPackageMember;
use App\Helpers;
use App\Individual;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupPackageController extends Controller
{

    public function index()
    {
        $packages = auth()->user()->profile->packages ?? [];
        return view('corporate.package',compact('packages'));
    }

    public function newPackage()
    {
        return view('corporate.product');
    }

    public function editPackage($uuid)
    {
        $package = GroupPackage::where("uuid",$uuid)->first();
        if (empty($package)) {
            abort(404);
        }
        //accessDenied($package->company_id != auth()->user()->profile->id);

        return view('corporate.product',compact('package'));

    }

    public function savePackage(Request $request)
    {
        $this->validate($request,[
            'title' => 'required|string',
            'dth'   => 'numeric',
            'add'   => 'numeric',
            'tpd'   => 'numeric',
            'mc1'   => 'numeric',
            'ci'    => 'numeric',
        ]);
        if(!empty($request->input('package_id'))){
            $package = GroupPackage::where("uuid",$request->input('package_id'))->first();
            if (empty($package)) {
                abort(404);
            }
            //accessDenied($package->company_id != auth()->user()->profile->id);
        }else{
            $package = new GroupPackage();
        }

        $package->company_id    = auth()->user()->profile->id ?? '';
        $package->name          = $request->input('title');
        $package->DTH           = $request->input('dth') > 0 ? $request->input('dth') : null;
        $package->ADD           = $request->input('add') > 0 ? $request->input('add') : null;
        $package->TPD           = $request->input('tpd') > 0 ? $request->input('tpd') : null;
        $package->MC1           = $request->input('mc1') > 0 ? $request->input('mc1') : null;
        $package->CI            = $request->input('ci') > 0 ? $request->input('ci') : null;
        $package->status        = 'Draft';
        $package->payment_term  = $request->input('cycle') == '1' ? 'annually' : 'monthly';
        $package->save();

        return redirect()->route('userpanel.groupPackage.index');

    }

    public function destroyPackage($id)
    {
        $package = GroupPackage::where("uuid",$id)->first();
        if (empty($package)) {
            abort(404);
        }
        //accessDenied($package->company_id != auth()->user()->profile->id);
        $package->members()->delete();
        $package->delete();

        return redirect()->back();

    }

    public function packageMembers($id)
    {
        $package = GroupPackage::where("uuid",$id)->first();
        if (empty($package)) {
            abort(404);
        }
        //accessDenied($package->company_id != auth()->user()->profile->id);

        $members = $package->members ?? [];
        foreach ($members as $member) {
            if(!empty($member->dob)) {
                $member->dob = Carbon::parse($member->dob)->format('d/m/Y');
            }
            if(!empty($member->passport_expiry_date)) {
                $member->passport_expiry_date = Carbon::parse($member->passport_expiry_date)->format('d/m/Y');
            }
        }

        return view('corporate.product_member',compact('package','members'));

    }

    public function savePackageMembers(Request $request , $id)
    {
        $package = GroupPackage::where("uuid",$id)->first();
        if (empty($package)) {
            abort(404);
        }
        //accessDenied($package->company_id != auth()->user()->profile->id);

        $package->members()->delete();

        $members = json_decode($request->input('members_data'));
        foreach ($members as $member) {
            $createMember = new GroupPackageMember();
            $createMember->package_id = $package->id;
            $individual = User::where("type","individual")->where("email",$member->email)->get()->first()->id ?? null;
            $createMember->individual_id        = $individual;
            $createMember->name                 = $member->name ?? null;
            $createMember->email                = $member->email ?? null;
            $createMember->nationality          = $member->nationality ?? null;
            $createMember->nric                 = $member->passport ?? null;
            $createMember->gender               = $member->gender ?? null;
            $createMember->dob                  = empty($member->dob) ? now() : Carbon::createFromFormat("d/m/Y",$member->dob);
            $createMember->passport_expiry_date = empty($member->passport_expiry_date) ? null : Carbon::createFromFormat("d/m/Y",$member->passport_expiry_date);
            $createMember->mobile               = $member->mobile ?? '';
            $createMember->status               = 'Draft';
            $createMember->save();
        }

        return redirect()->back();
    }
}
