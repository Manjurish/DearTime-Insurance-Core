<?php     

namespace App\Http\Controllers\User;

use App\Claim;
use App\Coverage;
use App\Document;
use App\Helpers;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\QR;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class HospitalController extends Controller
{
    public function review()
    {
        $claims = [];
        return view('user.hospital',compact('claims'));
    }
    
    public function checkSelfie(Request $request)
    {
        $selfie = Helpers::crateDocumentFromUploadedFile($request->file('selfie'), null, 'selfie_claim', true);

        $params = [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'selfie' => $selfie->path,
        ];

        $response = Http::asForm()->post(env('FACE_API_URL') . '/match', $params);
        $json = json_decode($response->body());

        if(count($json->face) > 0){
            $user = User::where("uuid",$json->face[count($json->face)-1]->Face->ExternalImageId)->first();
            if(!empty($user)) {
                $claim = Helpers::generateTemporaryQR($user);
                return ['status' => 'success', 'data' => $claim->uuid];
            }
        }
        return ['status'=>'failed','data'=>'Not the right person? '];
    }
    public function create(Request $request)
    {
        // TODO flush qr for production
//        Helpers::flushExpiredQR();

        $qr = QR::whereUuid($request->input('uuid'))->first();

        if(empty($qr)) {
            $claim = Claim::where("uuid",$request->input('uuid'))->where("panel_id",auth()->id())->where("status","draft")->first();
            if(empty($claim)){
                //check if coverage
                $coverage = Coverage::whereUuid($request->input('uuid'))->first();
                if(!empty($coverage)){
                    $claim = Claim::where("coverage_id",$coverage->uuid)->where("status","draft")->first();
                    if(empty($claim)) {
                        $claim = new Claim();
                        $claim->individual_id   = $coverage->owner_id;
                        $claim->owner_id        = $coverage->owner_id;
                        $claim->coverage_id     = $coverage->uuid;
                        $claim->status          = 'draft';
                        $claim->created_by      = auth()->id();
                        $claim->save();
                    }
                    $claim->panel_id            = auth()->id();
                    $claim->save();

                    return view('user.hospital',compact('coverage','claim'));
                }
            }else {
                $coverage = $claim->coverage;
                return view('user.hospital', compact('coverage', 'claim'));
            }
        }else {

            if ($qr->action_type == 'App\Coverage') {
                $coverage = Coverage::whereUuid($qr->action_uuid)->first();
                $claim = Claim::where("coverage_id", $coverage->uuid)->where("status", "draft")->first();
                if (empty($claim)) {
                    $claim = new Claim();
                    $claim->individual_id   = '';
                    $claim->coverage_id     = '';
                    $claim->status          = 'draft';
                    $claim->created_by      = auth()->id();
                    $claim->save();
                }
                $claim->panel_id            = auth()->id();
                $claim->save();

                return view('user.hospital', compact('coverage', 'claim'));

            } elseif ($qr->action_type == 'App\User') {

                $user = User::whereUuid($qr->action_uuid)->first();
                if (empty($user))
                    abort(404);

                $coverages = Coverage::where("owner_id", $user->profile->id ?? 0)->active()->groupBy("product_id")->get();
                return view('user.hospital', compact('user', 'coverages'));

            } else abort(400);
        }
    }
    public function uploadDoc($uuid,Request $request)
    {
        $claim = Claim::where("uuid",$uuid)->where("status","draft")->first();
        if(empty($claim)) {
            abort(404);
        }
        if(is_array($request->file('claim_form'))) {
            foreach ($request->file('claim_form') as $k=>$val) {
                Helpers::crateDocumentFromUploadedFile($val, $claim, $k);
            }
        }
        return "1";
    }
    public function dlDoc($id)
    {
        $Claim = Claim::where("uuid",$id)->first();
        if(empty($Claim)) {
            abort(404);
        }
        $doc = $Claim->documents()->get()->first();
        if(empty($Claim)) {
            abort(404);
        }

        return app(\App\Http\Controllers\Admin\DashboardController::class)->showDocument($doc->url,'');
    }
    public function removeDoc($id)
    {
        $doc = Document::where("url",$id)->first();
        if(empty($doc)) {
            abort(404);
        }
        //accessDenied($doc->documentable_type != 'App\Claim');

        $doc->delete();
        return "1";
    }

    public function claim()
    {
        $claims = Claim::query()->where("panel_id",auth()->id())->orderBy('created_at', 'desc')->get();
        return view('user.hospital.claims',compact('claims'));
    }

    public function details($uuid)
    {
        $claim = Claim::whereUuid($uuid)->first();
        if(empty($claim)) {
            abort(404);
        }
        //accessDenied($claim->panel_id != auth()->id());


		$coverage = $claim->coverage;
		$docs = $this->getDocs($coverage);

		return view('user.hospital.details',compact('claim','coverage','docs'));
    }

    public function scan()
    {
        return view('user.hospital.scan');
    }

    public function parse(Request $request)
    {
        $type = $request->input('type');
        $qr = QR::whereUuid($request->input('uuid'))->first();
        if(empty($qr)) {
            abort(404);
        }

        if($type == 'face'){
            $user = User::whereUuid($qr->action_uuid)->first();
            if(empty($user)) {
                abort(404);
            }

            $coverages = Coverage::where("owner_id", $user->profile->id ?? 0)->active()->groupBy("product_id")->get();

            if($coverages->count() == 0) {
                return redirect()->back()->with("danger_alert", __('mobile.no_coverage'));
            }

            return view('user.hospital.coverages',compact('coverages'));


        }elseif($type == 'barcode'){
            $coverage = Coverage::whereUuid($qr->action_uuid)->first();
            if(empty($coverage))
                abort(404);
            return redirect()->route('userpanel.hospital.coverage',$coverage->uuid);


        }else abort(404);
    }

    public function coverage($id)
    {
        $coverage = Coverage::whereUuid($id)->first();
        if(empty($coverage)) {
            abort(404);
        }

        $claim = Claim::where("coverage_id",$coverage->uuid)->first();
        if(empty($claim)){
            $claim = new Claim();
            $claim->individual_id   = $coverage->owner_id;
            $claim->coverage_id     = $coverage->uuid;
            $claim->owner_id        = $coverage->owner_id;
            $claim->status          = 'draft';
            $claim->created_by      = $coverage->owner_id;
            $claim->panel_id        = auth()->id();
            $claim->save();
        }else{
            if(!empty($claim->panel_id) && $claim->panel_id != auth()->id())
                return redirect()->back()->with("danger_alert",__('mobile.claim_exists'));
            $claim->panel_id = auth()->id();
            $claim->save();

        }
        return redirect()->route('userpanel.hospital.details',$claim->uuid);
    }

	public function claimDeatil($uuid)
	{
		//$coverage = Coverage::where('uuid',$cuuid)->firstOrFail();
		//$profile  = Individual::where('uuid',$puuid)->firstOrFail();
		$claim    = Claim::query()->where('uuid',$uuid)->first();
		$coverage = $claim->coverage;
		$docs     = $this->getDocs($coverage,$claim);
        $consent_doc     = $claim->documents()->where("type",'consent')->first();

		$hasConsent = $claim->documents()->where("type",'consent')->exists();

		return view('user.hospital.details',compact('coverage','claim','docs','hasConsent', 'consent_doc'));
	}

	/*public function claimDeatil($cuuid, $puuid)
	{
		$coverage = Coverage::where('uuid',$cuuid)->firstOrFail();
		$profile  = Individual::where('uuid',$puuid)->firstOrFail();

		$docs = $this->getDocs($coverage);

		$claim = Claim::query()->where('coverage_id',$coverage->id)->where('individual_id',$profile->id)->first();

		dd($claim->documents,$claim);

		return view('user.hospital.details',compact('coverage','claim','docs'));
	}*/

	/**
	 * @param $coverage
	 * @return array|void
	 */
	private function getDocs($coverage,$claim)
	{
		/*$death_docs = Helpers::getDocs(Enum::PRODUCT_NAME_DEATH);

		$disability_docs = Helpers::getDocs(Enum::PRODUCT_NAME_DISABILITY);

		$ci_docs = Helpers::getDocs(Enum::PRODUCT_NAME_CRITICAL_ILLNESS);*/

		/*$ciAllFiles  = array_diff(scandir(resource_path('documents/claims/ci')),array("..","."));
		natsort($ciAllFiles);
		$ci_docs = [];

		foreach ($ciAllFiles as $filename) {
			array_push($ci_docs,
					   [
						   'name' => $filename,
						   'link' => resource_path('documents/claims/ci/' . $filename)
					   ]);
		}*/

		if($coverage->product_name == Enum::PRODUCT_NAME_DISABILITY){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_DISABILITY);
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_DEATH){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_DEATH);
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_CRITICAL_ILLNESS){
			$docs = Helpers::getDocs(Enum::PRODUCT_NAME_CRITICAL_ILLNESS);;
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_ACCIDENT ){
             if($claim->claimantName==$claim->ownerName){
                $docs = Helpers::getDocs(Enum::PRODUCT_NAME_DISABILITY);}
             else{
             $docs = Helpers::getDocs(Enum::PRODUCT_NAME_DEATH);}
         }
		
        return $docs;
	}
}
