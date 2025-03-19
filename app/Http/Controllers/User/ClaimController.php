<?php     

namespace App\Http\Controllers\User;

use App\Claim;
use App\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
	public function index(Request $request)
	{
		$claim_history      = app(\App\Http\Controllers\Api\ClaimController::class)->getList()['data'] ?? [];
		$policy_list        = app(\App\Http\Controllers\Api\PolicyController::class)->getList($request)['data'] ?? [];
		$own_claims         = $policy_list['owner'] ?? [];
		$beneficiary_claims = $policy_list['beneficiary'] ?? [];

		return view('user.claim.claim_list',compact('claim_history','own_claims','beneficiary_claims'));
	}

	public function create(Request $request)
	{
		$uuid       = $request->input('uuid');
		$hospital   = $request->input('hospital');
		$claim_uuid = $request->input('claim_uuid');
		$request->request->replace(['uuid' => $uuid]);
		if(empty($uuid)){
			return redirect()->back()->with("danger_alert",__('web/claim.no_policy'));
		}

		$claim = app(\App\Http\Controllers\Api\ClaimController::class)->getClaim($request);

		if($claim['status'] != 'success'){
			return redirect()->back()->with("danger_alert",__('web/claim.no_policy'));
		}

		return view('user.claim.create_claim',compact('claim','uuid','hospital','claim_uuid'));
	}

	public function edit($id)
	{
		$claim = Claim::whereUuid($id)->first();
		if(empty($claim)){
			abort(404);
		}

		return view('user.claim.create_claim',compact('claim'));
	}

	public function store(Request $request)
	{
		$store = app(\App\Http\Controllers\Api\ClaimController::class)->add($request);

		if($store['status'] == 'success'){
			return redirect()->route('userpanel.claim.index')->with('success_alert',__('web/claim.save_success'));
		}
		return redirect()->back()->with("danger_alert",$store['message'] ?? __('mobile.error'));
	}

	public function upload(Request $request,$id)
	{
		$claim = Claim::where("uuid",$id)->first();

		//accessDenied(!empty($claim) && ($claim->individual_id != (auth()->user()->profile->id ?? 0)));

		$type = $request->input('doc_type');
		if(!in_array($type,config('static.claim_documents'))){
			$type = 'document';
		}

		if(empty($claim)){
			Helpers::crateDocumentFromUploadedFile($request->file('image'),NULL,$type,$id);
		}else{
			Helpers::crateDocumentFromUploadedFile($request->file('image'),$claim,$type);
		}
	}

	public function destroy($id)
	{
		$claim = Claim::where("uuid",$id)->first();

		if(empty($claim)){
			abort(404);
		}

		$claim->documents()->delete();
		$claim->delete();
		return redirect()->back()->with("success_alert",__('web/claim.destroy_success'));

	}
}
