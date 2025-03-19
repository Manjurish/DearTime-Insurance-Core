<?php     

namespace App\Http\Controllers\Api;

use App\Claim;
use App\Company;
use App\Coverage;
use App\Document;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\Controller;
use App\QR;
use App\UserClaimQuestion;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Image;

class ClaimController extends Controller
{
	public function getList()
	{
		$user = Auth::user()->profile;
		$userClaims		=	$user->claims;

		$userClaims->map(function ($claim) {
            $claim['is_panel_hospital'] = !empty($claim->panel_id) ? true : false;
            return $claim;
        });
		return ['status' => 'success','data' => $userClaims];
	}

	/**
	 * @param Request $request
	 * @return array
	 * @api {post} api/getClaim get claim
	 * @apiVersion 1.0.0
	 * @apiName GetClaim
	 * @apiGroup Claim
	 *
	 * @apiDescription It gets a claim
	 * @apiUse AuthHeaderToken
	 *
	 * @apiParam (Request) {String} type null/edit
	 * @apiParam (Request) {String} uuid claim uuid
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {Array} data[questions]
	 * @apiSuccess (Response (200) ) {Number} data[questions][id]
	 * @apiSuccess (Response (200) ) {String} data[questions][title]
	 * @apiSuccess (Response (200) ) {String} data[questions][type]
	 * @apiSuccess (Response (200) ) {Array} data[questions][content] answers array
	 * @apiSuccess (Response (200) ) {Number} data[questions][content][id]
	 * @apiSuccess (Response (200) ) {String} data[questions][content][title]
	 * @apiSuccess (Response (200) ) {Array} data[data]
	 * @apiSuccess (Response (200) ) {String} data[data][occ_name]
	 * @apiSuccess (Response (200) ) {Number} data[data][income]
	 */
	public function getClaim(Request $request)
	{

		$user = Auth::user()->profile;
		if($request->input('type') == 'edit'){
			$claim = Claim::whereUuid($request->input('uuid'))->first();
			$claim = Coverage::whereId($claim->coverage_id)->first();

		}else{
			$claim = Coverage::whereUuid($request->input('uuid'))->first();
			$hasclaim = Claim::where("coverage_id",$claim->id)->where("created_by",$user->user_id)->latest()->first();
			if(!empty($hasclaim) && ($hasclaim->status != 'cancelled' ) && ($hasclaim->status != 'rejected')){
				  $modal=[
				     "title"   => "attention",
					 "body" => __('mobile.already_claimed'),
					 "buttons" => [
			 
							[
						   "title" => __('ok'),
						   "action" => NextPage::CLAIMS,
						   "type" => "page",
				 ],
				 ]
				 
	 ];
	 return Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);
          }
			}
			if ($claim->product_name=="Accident"){
			        if($user->id==$claim->covered_id){
					    $claim->product_id="2";
						$questions=$claim->product->claimQuestions;
					  }
					  else{
			                  $claim->product_id="1";
							  $questions=$claim->product->claimQuestions;	  
					 }
	    }	
	    else{
		$questions = $claim->product->claimQuestions;
    }
		
		$out       = [];
		foreach ($questions as $question) {
			$ans = [];
			foreach ($question->answers as $answer) {
				$ans[] = ['id' => $answer->answer_id,'title' => $answer->title];
			}
			$out[] = ['id' => $question->id,'title' => $question->title,'type' => $question->type,'data' => json_decode($question->data),'content' => $ans];
		}

		$data = [
			'occ_name' => $user->occupationJob->name ?? '-',
			'income'   => (int)intval($user->personal_income),
		];
		return ['status' => 'success','data' => ['questions' => $out,'data' => $data]];

	}


	public function refreshQR(Request $request)
	{
		//   return $this->morphMany(QR::class, 'action')->last();
	}

	/**
	 * @param Request $request
	 * @return array
	 * @api {post} api/claims add claim
	 * @apiVersion 1.0.0
	 * @apiName AddClaim
	 * @apiGroup Claim
	 *
	 * @apiDescription If uuid is sent, this api could edit claim
	 * @apiUse AuthHeaderToken
	 *
	 * @apiParam (Request) {Array} files
	 * @apiParam (Request) {File} files[files] jpg/jpeg/png/bmp/pdf and max:5000
	 * @apiParam (Request) {File} files[questions_files] jpg/jpeg/png/bmp/pdf and max:5000
	 * @apiParam (Request) {String} policy
	 * @apiParam (Request) {String} [uuid] claim uuid
	 *
	 * @apiSuccess (Response (200) ) {String} status success
	 * @apiSuccess (Response (200) ) {String} message
	 * @apiSuccess (Response (200) ) {Array} data
	 * @apiSuccess (Response (200) ) {Object} data[claim]
	 * @apiSuccess (Response (200) ) {String} data[next_page]
	 *
	 * @apiError {String} status error
	 * @apiError {String} message
	 */
	public function add(Request $request)
	{
		
		$questions = ($request->input('questions'));
		if ($questions != null && $questions != "") {
			$questions = (str_replace('\"', '"', $questions));
			$questionsArr = json_decode($questions);
		}
		//claim related submission
		$request->validate([
							   //'questions'     => 'required',
							   'coverage_uuid' => 'required|string|exists:coverages,uuid',
						   ]);

		$user         = Auth::user();
		$coverageUuid = $request->coverage_uuid;
		$coverage     = Coverage::whereUuid($coverageUuid)->first();
		$questions    = $request->questions;
		$hasclaim = Claim::where("coverage_id",$coverage->id)->where('created_by',$user->id )->latest()->first();

       if(empty($hasclaim) || ($hasclaim->status == 'cancelled' ) || ($hasclaim->status == 'rejected')){
		   $claim = Claim::create(
			[
				'individual_id' => $user->profile->id,
				'coverage_id'   => $coverage->id,
				'owner_id'      => $coverage->owner_id,
				'status'        => Enum::CLAIM_STATUS_DRAFT,
				'created_by'    => $user->id,
				//'panel_id'      => null,
			]);
		}else {
			
		$claim= Claim::where("coverage_id",$coverage->id)->where('created_by',$user->id )->first();
			
			
		}

		$req = Request::create('/doc','GET',[
			'app_view'   => Helpers::isFromApp() ? '1' : '2',
			'claimUuid' => $claim->uuid,
			'type'       => 'consent',
			'need_save'  => TRUE
		]);
		app()->handle($req);


		if ($questionsArr != null) { 
			foreach ($questionsArr as $key => $value) {
				$ans              = new UserClaimQuestion();
				$ans->claim_id    = $claim->id;
				$ans->question_id = $key;
				$ans->value       = $value;
				// var_dump($ans);
				$ans->save();
			}
		}

        foreach ($request->file('files') ?? [] as $file) {
			$doc[] = Helpers::crateDocumentFromUploadedFile($file,$claim);
		}

		return [
			'status'  => 'success',
			'message' => __('web/messages.claim_submitted'),
			'data'    => [
				'claim' => $claim,
			]];
	}

	/*public function add(Request $request)
	{

		//TODO cannot add more more claims for a product already in pending claim (except claim)

		$request->validate([
							   'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf|max:5000',
							   'policy'  => 'required|string|exists:coverages,uuid',
							   'uuid'    => 'nullable|string|exists:claims',
							   //    'deleted' => 'string'
						   ]);


		$claim    = Claim::whereUuid($request->uuid)->first();
		$coverage = Coverage::whereUuid($request->policy)->first();

		if($claim){

			foreach ($request->deleted ?? [] as $deleted)
				$claim->documents()->whereUrl($deleted)->delete();

			//    $claim->documents()->delete();
			$claim->updated_at = Carbon::now();
			$claim->save();
		}else{
			//check if claim exists
			$claim = Claim::where("coverage_id",$request->policy)->first();
			if(empty($claim))
				$claim = Claim::create(['individual_id' => $request->user()->profile->id ?? $coverage->owner_id,'owner_id' => $coverage->owner_id,'coverage_id' => $request->policy,'created_by' => $request->user()->id]);

			$claim->updated_at = Carbon::now();
			$claim->save();

		}


		$doc = [];
		UserClaimQuestion::where("claim_id",$claim->id)->delete();

		foreach ($request->file('files') ?? [] as $file) {
			$doc[] = Helpers::crateDocumentFromUploadedFile($file,$claim);
		}
		foreach ($request->input('questions') ?? [] as $i => $item) {
			$ans              = new UserClaimQuestion();
			$ans->claim_id    = $claim->id;
			$ans->question_id = $i;
			$ans->value       = $item;
			$ans->save();
		}
		foreach ($request->file('questions_files') ?? [] as $i => $item) {
			$ans              = new UserClaimQuestion();
			$ans->claim_id    = $claim->id;
			$ans->question_id = $i;
			$ans->value       = 'upload';
			$ans->save();
			Helpers::crateDocumentFromUploadedFile($item,$ans);
		}


		return ['status' => 'success','message' => __('web/messages.claim_submitted'),'data' => ['claim' => $claim,'next_page' => 'claims_page']];


	}*/


	public function delete(Request $request)
	{
		$user    = $request->user()->profile;
		$deleted = $user->claims()->whereUuid($request->uuid)->where('status','draft')->delete();
		if($deleted > 0){
			return ['status' => 'success','message' => __('web/messages.claim_deleted'),'data' => []];

		}else
			return ['status' => 'error','message' => __('web/messages.claim_not_exists'),'data' => []];
	}

	public function scan(Request $request)
	{
		Helpers::flushExpiredQR();

		$request->validate([
							   'uuid' => 'string|exists:q_r_s',
						   ]);

		$qr = QR::whereUuid($request->uuid)->first();
		if($qr->action_type == 'App\Coverage'){
			$coverage = Coverage::select(['uuid','owner_id','covered_id','product_name'])->whereUuid($qr->action_uuid)->first();

			return ['status' => 'success','data' => ['next_page' => 'claim_page','coverage' => $coverage]];
		}


	}

	public function store(Request $request)
	{
		// dd($request->get('coverage_uuid'));

		// requests
		$hasPanel     = $request->get('has_panel');
		$coverageUuid = $request->get('coverage_uuid');
		$hospitalUuid = $request->get('hospital_uuid');
		$user         = Auth::user();

		// coverage
		$coverage = Coverage::where('uuid',$coverageUuid)->first();
		// dd($coverage);

		// hospital
		$hospital = Company::where('uuid',$hospitalUuid)->first();
		// dd($hospital);
		//dev-499 Claim related blocking issue
		// if($hasPanel){
			$claim = Claim::create(
				[
					'individual_id' => $user->profile->id,
					'coverage_id'   => $coverage->id,
					'owner_id'      => $coverage->owner_id,
					'status'        => Enum::CLAIM_STATUS_DRAFT,
					'created_by'    => $user->id,
					'panel_id'      => $hasPanel ? $hospital->id : null,
				]);
		// }

// Created by Kishor

      $req = Request::create('/doc','GET',[
					'app_view'   => Helpers::isFromApp() ? '1' : '2',
					'claimUuid' => $claim->uuid,
					'type'       => 'consent',
					'need_save'  => TRUE
		]);
		app()->handle($req);
		

		if(!empty($claim)){
			return response()->json(
				[
					'status' => 'success',
					'data'   => [
						'claim_code' => $claim->ref_no,
						'claim' => $claim
					],
				]);
		}
	}

	public function assignHospital(Request $request)
	{

		$request->validate(
			[
				'hospital_uuid' => 'required|string|exists:companies,uuid',
				'claim_uuid'    => 'required|string|exists:claims,uuid',
			]);

		$hospitalUuid = $request->hospital_uuid;
		$hospital     = Company::where('uuid',$hospitalUuid)->first();
		$claimUuid    = $request->claim_uuid;
		$claim        = Claim::where('uuid',$claimUuid)->first();

		if(!empty($hospital) && !empty($claim)){
			$claim->update(
				[
					'panel_id' => $hospital->user->id
				]
			);

			return [
				'status' => 'success',
				'data'   => [
					'claim' => $claim,
				]];
		}else{
			return response()->json(['status' => 'error'],404);
		}
	}

	public function detail(Request $request)
	{
		$request->validate(
			[
				'claim_uuid' => 'required|string|exists:claims,uuid',
			]);

		$claimUuid = $request->claim_uuid;
		$claim     = Claim::where('uuid',$claimUuid)->first();
		$docs      = [];


		if($claim->coverage->product_name == Enum::PRODUCT_NAME_DISABILITY){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_DISABILITY);
		}elseif($claim->coverage->product_name == Enum::PRODUCT_NAME_DEATH){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_DEATH);
		}elseif($claim->coverage->product_name == Enum::PRODUCT_NAME_CRITICAL_ILLNESS){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_CRITICAL_ILLNESS);;
		} elseif($claim->coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_MEDICAL);;
		}elseif($claim->coverage->product_name == Enum::PRODUCT_NAME_ACCIDENT){
			if($claim->claimantName==$claim->ownerName){
			   $docs = Helpers::getDocs(Enum::PRODUCT_NAME_DISABILITY);
			}else{
				$docs = Helpers::getDocs(Enum::PRODUCT_NAME_DEATH);
			}
		
		}

		//dd($docs);

		foreach ($docs as $key => $doc) {
			if(!empty($docs[$key]['link'])){
				//$docs[$key]['link'] = base64_encode($docs[$key]['link']);
				$docs[$key]['link'] = $docs[$key]['link'];
			}
			$docs[$key]['upload'] = [];
			$docs[$key]['canUpload'] = true;
			
			if(str_contains($docs[$key]['name'],'Consent'))
			{
				
				$consentDoc		=	$claim->documents()->where("type", 'consent')->first();
				$docs[$key]['link'] = $consentDoc['link'];
				$docs[$key]['canUpload'] = false;

				$doc2 = $docs[$key]['link'];

				$doc2 = str_replace('{{name_of_claimant}}',$claim->claimant_name,$doc2);

				// $res = [
				// 	//'bucket' => env('AWS_BUCKET'),
				// 	'doc' => $doc2,
				// 	'type' => 'consent',
				// 	'data' => [
				// 		'name_of_claimant' => $claim->claimant_name ?? 'ali',
				// 		'claimant_nric' => '$claim->profile->nric',
				// 		'claimant_is_foreign' => '$claim->profile->is_local()',
				// 		// 'claimant_date' => Carbon::parse($claim->created_at)->format(config('static.datetime_format')),
				// 		'name_of_insured' => $claim->owner_name,
				// 		'insured_nric' => '$claim->owner->nric',
				// 		'insured_is_foreign' => '$claim->owner->is_local()',
				// 		// 'now' => Carbon::now()->format(config('static.datetime_format')),
				// 	]
				// ];
				
					$docs[$key]['link'] = $doc2;	       
			}
			
			foreach ($claim->documents()->where("type",$doc['name'])->get() ?? [] as $item) {
				array_push($docs[$key]['upload'],$item['link']);
			}
		}

		return [
			'status' => 'success',
			'data'   => [
				'docs' => $docs,
			]];
	}


/********************* Dashboard FAQ Documents  ***************************/

    public function detail_faq(Request $request)
    {

        $docs      = [];

        $docs = Helpers::getDocs_faqs();

        foreach ($docs as $key => $doc) {
            if(!empty($docs[$key]['link'])){
                $docs[$key]['link'] = $docs[$key]['link'];
            }

        }
        return [
            'status' => 'success',
            'data'   => [
                'docs' => $docs,
            ]];
    }

/************************ Dashboard FAQ Documents  ***************************/

	public function showDocumentResize($type,$path,$ext)
	{
		echo $path;exit;
		$document = Document::where("url",$path);
		$document = $document->get()->first();

		empty(($document));

		$path = $document->S3Url;
		empty(($path));

		$img = ['png','jpg','gif','webp'];
		if(!in_array($document->ext,$img)){
			//response as file
			$content_type = [
				'pdf'  => 'application/pdf',
				'docx' => 'application/vnd.ms-word',
				'doc'  => 'application/vnd.ms-word',
				'xls'  => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.ms-excel',
			];

			$headers = [
				'X-Vapor-Base64-Encode' => TRUE,
				'Content-type'          => $content_type[$document->ext] ?? '',
				'Content-Disposition'   => 'attachment; filename="' . $document->name . '"',
			];
			return response()->make($path,200,$headers);
		}

		//        if(!auth('internal_users')->check()){
		//            if($document->created_by != auth()->id() && $document->type != 'selfie')
		//                abort(403);
		//        }

		switch ($type) {
			case 'tiny':
				$width = '100';
				break;
			case 'small':
				$width = '200';
				break;
			case 'medium':
				$width = '300';
				break;
			default :
				$width = '500';
		}

		if($type == 'thumb'){

			$response = Response::make($document->ThumbS3Url,200);
			$response->header("Content-Type",$type);

			return $response;
		}

		$img = Image::cache(function ($image) use ($type,$width,$path) {
			$image->make($path);

			if($type == 'square')
				$image->resize($width,$width);

			elseif($type != 'actual')
				$image->resize($width,NULL,function ($constraint) {
					$constraint->aspectRatio();
				});

		},100,TRUE);
		return $img->response();
	}

	public function downloadResource($path)
	{
		$path = decrypt($path);

		if(!File::exists(resource_path($path))) {
			return response()->json(['message' => 'File not found.'], 404);
		}

		$path = resource_path($path);

		$file = File::get($path);
		$type = File::mimeType($path);
		$fileName = File::basename($path);

		$response = Response::make($file, 200);
		$response->header('X-Vapor-Base64-Encode','true');
		$response->header('Content-Type', $type);
		$response->header('Content-Disposition: attachment; filename=', $fileName);

		return $response;
	}

	public function uploadDoc(Request $request)
	{
		$request->validate(
			[
				'claim_uuid' => 'required|string|exists:claims,uuid',
				'doc_name'   => 'required|string',
				//comented below for single upload old claim changes 
				// 'files'       => 'required',
				'file'       => 'required',
			]);

		$claimUuid = $request->claim_uuid;
		$claim     = Claim::where('uuid',$claimUuid)
					 ->whereIn("status",[Enum::CLAIM_STATUS_DRAFT,ENUM::CLAIM_STATUS_PENDING_FOR_OS_DOCUMENT])
					 ->first();

		//added below single line for single upload old claim changes 				 
	    $file    = $request->file('file');
	

		$docName = $request->doc_name;

		// foreach ($request->file('files') as $file) {
			// Helpers::crateDocumentFromUploadedFile($file,$claim,$docName);
		// }

		Helpers::crateDocumentFromUploadedFile($file,$claim,$docName);

		return ['status' => 'success'];
	}

}