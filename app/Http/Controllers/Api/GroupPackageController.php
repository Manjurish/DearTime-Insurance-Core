<?php     

namespace App\Http\Controllers\Api;

use App\GroupPackage;
use App\GroupPackageMember;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Console\Input\Input;

class GroupPackageController extends Controller
{
    public function getPackages(Request $request)
    {
        $company = $request->user()->profile;
        return ['status' => 'success', 'data' => $company->packages];

    }


    public function createPackage(Request $request)
    {
        $company = $request->user()->profile;

        $request->validate([
            'name' => 'required|string',
            'DTH' => 'numeric',
            'ADD' => 'numeric',
            'TPD' => 'numeric',
            'MC1' => 'numeric|in:1,0',
            'CI' => 'numeric',
            'payment_term' => 'required|in:monthly,annually',
            'uuid' => 'string'
        ]);

        $package = GroupPackage::whereUuid($request->uuid)->first();
        if ($package) { //edit
//            $request->merge([''=>]);

            GroupPackage::whereUuid($request->uuid)->update($request->all());
            $medical = Product::find(5)->quickQuoteFor($company);
            $ci = Product::find(4)->quickQuoteFor($company);
            $accident = Product::find(3)->quickQuoteFor($company);
            $disability = Product::find(2)->quickQuoteFor($company);
            $death = Product::find(1)->quickQuoteFor($company);
            $total_annully =
                ($medical['annually'] ?? 0) +
                ($ci['annually'] ?? 0) +
                ($accident['annually'] ?? 0) +
                ($disability['annually'] ?? 0) +
                ($death['annually'] ?? 0);

            $total_monthly =
                ($medical['monthly'] ?? 0) +
                ($ci['monthly'] ?? 0) +
                ($accident['monthly'] ?? 0) +
                ($disability['monthly'] ?? 0) +
                ($death['monthly'] ?? 0);


            $package = GroupPackage::whereUuid($request->uuid)->first();
            $package->payment_annually = $total_annully;
            $package->payment_monthly = $total_monthly;
            $package->save();

            return ['status' => 'success', 'message' => __('web/messages.package_edit_successful'), 'data' => $package];

        } else {
            $request->merge(['company_id' => $company->id, 'status' => 'Draft']);
            $package = GroupPackage::create($request->except(['id']));
            return ['status' => 'success', 'message' => __('web/messages.package_edit_successful'), 'data' => $package];

        }


    }

    public function getMembers(Request $request)
    {
        $request->validate(['uuid' => 'required|string|exists:group_packages']);

        $company = $request->user()->profile;
        $package = $company->packages()->whereUuid($request->uuid)->first();

        return ['status' => 'success', 'data' => $package->members];
    }

    public function createMember(Request $request)
    {
        $company = $request->user()->profile;

        $request->validate([
            'uuid' => 'required|string|exists:group_packages',
            'name' => 'required|string',
            'email' => 'required|email',
            'nationality' => 'required|string',
            'nric' => 'required',
            'gender' => 'required|in:male,female',
            'dob' => 'required|date_format:d/m/Y',
            'passport_expiry_date' => 'required_unless:nationality,Malaysian|nullable|date',
            'mobile' => 'required',
            'member_uuid' => 'string'
        ]);


        $package = $company->packages()->whereUuid($request->uuid)->first();

        $packageMember = $package->members()->whereUuid($request->member_uuid)->first();
        if ($packageMember) {
            $request->merge(['package_id' => $package->id, 'status' => 'Draft']);

            GroupPackageMember::whereUuid($request->member_uuid)->update($request->except(['uuid', 'member_uuid']));
            $packageMember = GroupPackageMember::whereUuid($request->member_uuid)->first();
            return ['status' => 'success', 'message' => __('web/messages.member_edit_successful'), 'data' => $packageMember];

        } else {
            $request->merge(['package_id' => $package->id, 'status' => 'Draft']);

            $packageMember = GroupPackageMember::create($request->except('uuid'));
            return ['status' => 'success', 'message' => __('web/messages.package_add_successful'), 'data' => $packageMember];

        }

    }


    public function deleteMember(Request $request)
    {
        $company = $request->user()->profile;
        $request->validate(['uuid' => 'required|string|exists:group_package_members', 'package_uuid' => 'required|string|exists:group_packages,uuid']);

        // TODO: perform due diligence before deleting if active
        $package = $company->packages()->whereUuid($request->package_uuid)->first();
        $package->members()->whereUuid($request->uuid)->delete();
        return ['status' => 'success', 'message' => __('web/messages.member_deleted_successful'), 'data' => null];


    }


    public function deletePackage(Request $request)
    {
        $company = $request->user()->profile;
        $request->validate(['uuid' => 'required|string|exists:group_packages']);

        // TODO: perform due diligence before deleting if active
        $package = $company->packages()->whereUuid($request->uuid)->first();
        $package->members()->delete();
        $package->delete();

        return ['status' => 'success', 'message' => __('web/messages.member_deleted_successful'), 'data' => null];

    }
}
