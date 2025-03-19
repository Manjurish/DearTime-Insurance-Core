<?php     

namespace App\Http\Controllers\Api;

use App\Address;
use App\Company;
use App\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class CompanyController extends Controller
{
	public static function updateProfile(Request $request)
	{
		$request->validate(
			[
				'tel_no'        => 'required|string',
				'address'       => 'required',
				'state'         => 'required',
				'city'          => 'required',
				'postcode'      => 'required',
				'type'          => 'required',
				'director_ic.*' => 'required|mimes:jpg,jpeg,png,bmp,pdf|max:5000',
				'form_9'        => 'mimes:jpg,jpeg,png,bmp,pdf|max:5000',
				'form_24'       => 'mimes:jpg,jpeg,png,bmp,pdf|max:5000',
				'form_44'       => 'mimes:jpg,jpeg,png,bmp,pdf|max:5000',
				'form_49'       => 'mimes:jpg,jpeg,png,bmp,pdf|max:5000',
			]);

		//   $corporate = $request->user()->profile;

		$request->user()->profile->documents()->delete();


		foreach ($request->file('director_ic') ?? [] as $file) {
			Helpers::crateDocumentFromUploadedFile($file,$request->user()->profile,'directors_ic');
		}

		if($request->hasFile('form_9'))
			Helpers::crateDocumentFromUploadedFile($file,$request->user()->profile,'form_9');

		if($request->hasFile('form_24'))
			Helpers::crateDocumentFromUploadedFile($file,$request->user()->profile,'form_24');

		if($request->hasFile('form_44'))
			Helpers::crateDocumentFromUploadedFile($file,$request->user()->profile,'form_44');

		if($request->hasFile('form_49'))
			Helpers::crateDocumentFromUploadedFile($file,$request->user()->profile,'form_49');

		Address::whereId($request->user()->profile->address_id)->delete();
		$address               = Address::create($request->only(['address','state','city','postcode']));
		$request['address_id'] = $address->id;
		$request->user()->profile->update($request->only(['tel_no','type','address_id']));
		//       $request->user()->profile->fresh();
		return ['status' => 'success','message' => __('web/messages.profile_updated'),'data' => ['user' => $request->user()]];
	}

	public function search(Request $request)
	{
		$request->validate(
			[
				'search_term'  => 'required',
				'relationship' => 'required',
			]);

		$relationship = $request->get('relationship');
		$searchTerm   = $request->get('search_term');

		$hospitals = Company::query()
							->where('relationship',$relationship)
							->where('name','like',"%{$searchTerm}%")
							->without('documents')
							->get();

		if($hospitals->isNotEmpty()){
			return response()->json(
				[
					'status' => 'success',
					'data'   => [
						'hospitals' => $hospitals
					],
				]);
		}else{
			return response()->json(
				[
					'status' => 'error',
				],404);
		}
	}

	public function hospitals(Request $request)
	{
		$hasPanel = $request->get('has_panel');

		if($hasPanel){
			$hospitals = Company::query()
								->where('relationship','Panel Hospital')
								->without('documents')
								->orderBy('created_at','asc')
								->get();
		}

		if(!empty($hospitals)){
			return response()->json(
				[
					'status' => 'success',
					'data'   => [
						'hospitals' => $hospitals
					],
				]);
		}
	}
}
