<?php     

namespace App\Http\Controllers\User;


// require 'vendor/autoload.php';
require __DIR__.'/../../../../vendor/autoload.php';
use App\Beneficiary;
use App\CharityApplicant;
use App\CustomerVerification;
use App\CustomerVerificationDetail;
use App\Helpers;
use App\Config;
use App\Individual;
use App\SelfieMatch;
use App\Underwriting;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Aws\Rekognition\RekognitionClient;

// use Aws\Credentials\Credentials;


class VerificationController extends Controller
{
    public function index()
    {
        $selfie     = null;
        $passport   = null;
        $visa       = null;
        $user       = auth()->user();
        $profile    = $user->profile;
        $cv         = CustomerVerification::where(['individual_id' => $profile->id])->first();
        $cvDt       = $cv->lastUserDetail;
        $status     = null;

        if(!empty($cv) && !empty($cvDt)){
            $selfie     = $cvDt->documents()->where("type","selfie")->first()->ThumbLink ?? null;
            $passport   = $cvDt->documents()->where("type","myKad")->first()->ThumbLink ?? null;
            $visa       = $cvDt->documents()->where("type","visa")->first()->ThumbLink ?? null;
            $status     = $cv->status;
        }
        return view('user.verification',compact('selfie','passport','profile','visa','status'));
    }

    public function store(Request $request)
    {
        
        $this->validate($request,[
            'selfie'    => 'nullable|sometimes|mimes:jpg,jpeg,png,bmp|max:5000',
            'myKad'     => 'nullable|sometimes|mimes:jpg,jpeg,png,bmp|max:5000',
            'visa'      => 'nullable|sometimes|mimes:jpg,jpeg,png,bmp|max:5000',
        ]);

        $user = auth()->user();
        $profile = $user->profile;

        $userType = '';
        if(empty($profile->nationality))
            $userType = 'Malaysian';
        else    
            $userType = $profile->nationality;

        $nric = $profile->nric;
        
        $customerVerification = CustomerVerification::updateOrCreate(['individual_id' => $profile->id]);

        $customerVerificationDetail                 = new CustomerVerificationDetail();
        $customerVerificationDetail->kyc_id         = $customerVerification->id;
        $customerVerificationDetail->status         = 'Pending';
        $customerVerificationDetail->note           = '';
        $customerVerificationDetail->description    = '';
        $customerVerificationDetail->type           = 'user';
        $customerVerificationDetail->created_by     = $user->id ?? 0;
        $customerVerificationDetail->save();

        $fromSelfieScreen = false;
        if(!empty($request->file('selfie'))) {
            $fromSelfieScreen = true;
            //
//            $customerVerificationDetail->documents()->where("type", "selfie")->delete();
            $selfie = Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $customerVerificationDetail, 'selfie');
        }else{
            $selfie = $customerVerificationDetail->documents()->where("type", "selfie")->first();
        }

        if(!empty($request->file('myKad'))) {
//            $customerVerificationDetail->documents()->where("type", "myKad")->delete();
            $myKad = Helpers::crateDocumentFromUploadedFile($request->file('myKad'), $customerVerificationDetail, 'myKad');
        }else{
            $myKad = $customerVerificationDetail->documents()->where("type", "myKad")->first();
        }

        if(!empty($request->file('visa'))) {
//            $customerVerificationDetail->documents()->where("type", "visa")->delete();
            $visa = Helpers::crateDocumentFromUploadedFile($request->file('visa'), $customerVerificationDetail, 'visa');
        }else{
            $visa = $customerVerificationDetail->documents()->where("type", "visa")->first();
        }

        if(!empty($user)) {
            $msg = $user->messages()->whereJsonContains('data->id','verification')->first();
            if(!empty($msg)){
                $msg->is_read       = '1';
                $data               = json_decode($msg->data,true);
                $data['buttons']    = [];
                $msg->data          = json_encode($data);
                $msg->save();
            }
        }

        // $validate = null;
        $validate = new \stdClass;
        
        try {
            if (!$fromSelfieScreen) {
                $tgtfile = $myKad;
                // echo $tgtfile = isset($mykad->path) && $mykad->path != null ? $mykad->path : $visa->path;exit;
                $this->extractTextAndCmpMyKadOrPassport($tgtfile, $validate, $userType, $nric);
                // echo "<pre>";
                // print_r($validate);
            }
        } catch (\Exception $e){

        }

        try {
            if (Config::getValue('ekyc_strict_comparision') == 'deactive'  ||
                  ( $validate->textExtracted != null && 
                    !str_contains($validate->textExtracted, 'Not a Geniune')
                  )
                ) {
                $this->validateSelfie($selfie, $myKad, $user->uuid, $fromSelfieScreen, $validate);
            }
            // echo "<pre>";
            // print_r($validate);
        } catch (\Exception $e){

        }


        //TODO move this to matched section
        if (!empty($selfie) && !empty($myKad)) {
            //validate the verification
            $customerVerification->status = 'Pending';
            $customerVerification->save();
            //change a parameter to call static::saved function in Individual.php
            $user = Individual::find($profile->id);
            $user->save();

//                $selfie->delete();
//                $myKad->delete();
        }

        if(!empty($validate->status) &&  $validate->status == 'match'){
            $profile->selfieMatch()->delete();

            $match = new SelfieMatch();
            $match->individual_id   = $profile->id;
            $match->match_uuid      = $validate->uid;
            $match->distance        = $validate->distance;
            $match->save();
            
            return 'success';
        }else{

            session()->flash('danger_alert',__('mobile.selfie_mismatch'));
            return 'success';
//            return 'failed';
        }
    }

    public function extractTextAndCmpMyKadOrPassport($tgtfile, $validate, $userType, $nric) {
        // print_r($validate);exit;
        if ($validate == null) {
            $validate = new \stdClass;
        }
        //Get Rekognition Access
        $rekognitionClient = RekognitionClient::factory(array(
            'region'	=> "ap-southeast-1",
            'version'	=> 'latest',
            // 'credentials' => $credentials
        ));
        
        $results = $rekognitionClient->detectText(
            [
                'Image' => [// REQUIRED
                    'S3Object' => [
                        // 'Bucket' => 'dt-insurance-dev-bucket',
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        // 'Name' => 'documents/2022-01-15/20180906_085445.jpg',
                        'Name'  => $tgtfile
                        // 'Version' => 'latest',
                    ],
                ],
            ]
        );
        
        $string = '';
        if (isset($results['TextDetections'])) {
            foreach ($results['TextDetections'] as $phrase) {
                $string .=  $phrase['DetectedText'];
            }
        }
        // print_r($string);
        // exit;

        //MyKad Match Regex 
        $matches = array();
        $textMatchFoundwithReg = false;
        $autoapprove = false;
        $validate->textExtracted = '';
        if ($userType == "Malaysian") {
            $genuineMyKad = false;
            //IT should not be a driving license / pad , ensure its a valid MY KAD check
            if ($string && str_contains($string, "KAD PENGENALAN")) {
                $genuineMyKad = true;
            } else {
                $validate->textExtracted = "Not a Geniune My KAD";
                return $validate;
                exit;
            }
            //Extrac the NRIC number
            if ($genuineMyKad) {
                // $validate->genuineMyKad = true;
                preg_match('/\d{6}-\d{2}-\d{4}/', $string, $matches);
            }
        // print_r($matches);
        } else {
            $genuinePassport = false;
            //IT should not be a driving license / pad , ensure its a valid MY KAD check
            if ($string && (str_contains($string, "passport contains") || 
                                str_contains($string, "Passport") )) {
                $genuinePassport = true;
            } else {
                $validate->textExtracted = "Not a Geniune";
                return $validate;
                exit;
            }

            //Passport Match Regex 
            //Extrac the NRIC number
            if ($genuinePassport) {
                preg_match('/[A-Z$]\d{7,10}/', $string, $matches);
            }
        }
        
        $custommsg  = '';
        if (count($matches) > 0) {
            $validate->textExtracted = $matches[0];
            $tgtword = str_replace("-", "", $matches[0]);
            //Auto approval flag
            if ($tgtword == $nric) {
                $textMatchFoundwithReg = true;
                if ($userType == "Malaysian"){  
                    $autoapprove = true;
            } 
            }else {
            
                    if ($userType != "Malaysian"){
                        $custommsg = str_replace("<nnnnnnnnnn>", $nric, __('mobile.kyc_ppt_notmach'));
                        $validate->textExtracted = str_replace("<nnnnnnnnnn>", $nric, __('mobile.kyc_ppt_notmach'));
                      //  print_r("Captured NRIC Number".$tgtword);
                      // print_r("Validation - IF ".$validate->textExtracted);
                        return $validate;
                        exit;
                    }else{
                      //  print_r("Entered NRIC Number".$nric);
                          $nric = substr_replace($nric,"-",6,0);
                          $nric = substr_replace($nric,"-",9,0);
                        $custommsg = str_replace("<yymmdd-nn-xxxx>", $nric, __('mobile.kyc_mykad_notmach'));
                        $validate->textExtracted = str_replace("<yymmdd-nn-xxxx>", $nric, __('mobile.kyc_mykad_notmach'));
                     // print_r("Entered NRIC Number".$nric);
                     // print_r("Validation - ELSE ".$validate->textExtracted);
                      //  return $validate;
                     //   exit;
                    }
                
            
                // if ($userType != "Malaysian")
                //     $custommsg = str_replace("<nnnnnnnnnn>", $nric, __('mobile.kyc_ppt_notmach'));
                // else 
                //     $custommsg = str_replace("<yymmdd-nn-xxxx>", $nric, __('mobile.kyc_mykad_notmach'));
            }
        } else if ($validate->textExtracted == '') {
            $validate->textExtracted = "Not able to extract text / No text exist";
        }

        // echo  $custommsg ;exit;
        if ($textMatchFoundwithReg && !empty($validate)) $validate->textMatchFoundwithReg = $textMatchFoundwithReg;
        if ($autoapprove && !empty($validate)) $validate->autoapprove = $autoapprove;
        // print_r($validate);
        // exit;
    }

    public function compareSelfieWithMyKadPassportOrVisa($selfie, $tgtfile, $validate) {
        //Credentials for access AWS Service code parameter
        // $credentials = new Aws\Credentials\Credentials('AWSKEY', 'AWSSECRETGOESHERE');
        // $tgtfile = ($mykad != null ? $mykad : $visa);
        //Get Rekognition Access
        $rekognitionClient = RekognitionClient::factory(array(
                'region'	=> "ap-southeast-1",
                'version'	=> 'latest',
                // 'credentials' => $credentials
        ));
        
        $compareFaceResults = $rekognitionClient->compareFaces([
            'SimilarityThreshold' => 90,
            'SourceImage' => [
                'S3Object' => [
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    // 'Bucket' => 'dt-insurance-dev-bucket',
                    // 'Name' => 'documents/2022-01-15/regina-mykad.png',
                    // 'Name' => 'documents/2022-01-15/myphoto.png',
                    'Name'  => $selfie
                ],
            ],
            'TargetImage' => [
                'S3Object' => [
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    // 'Bucket' => 'dt-insurance-dev-bucket',
                    //'Name' => 'documents/2022-01-15/regina-photo.png',
                    // 'Name' => 'documents/2022-01-15/20180906_085445.jpg',
                    'Name'  => $tgtfile
                ],
            ],
        ]);


        // echo "<pre>";
        // print_r($compareFaceResults);
        // exit;

        // // //Response to JSON Data
        $FaceMatchesResult = $compareFaceResults['FaceMatches'];
        // $SimilarityResult =  $FaceMatchesResult['Similarity'] ; //Here You will get similarity
        // $sourceImageFace = $compareFaceResults['SourceImageFace'];
        // $sourceConfidence = $sourceImageFace['Confidence']; //Here You will get confidence of the picture
        
        // $validate = array("status" => "false", "similarity" => 0);
        // $validate = new \stdClass;
        $validate->status = false;
        $validate->similarity = -1;

        // echo "Similarity diff";
        // print_r($FaceMatchesResult);
        if (isset($compareFaceResults['FaceMatches']) && count($compareFaceResults['FaceMatches']) > 0  
                && isset($compareFaceResults['FaceMatches'][0]['Similarity'])) {
            $SimilarityResult =  $compareFaceResults['FaceMatches'][0]['Similarity'] ; //Here You will get similarity
            $validate->status = 'match';
            $validate->similarity = $SimilarityResult;
        }
        // echo "<pre>";
        // print_r($validate);
        // print_r($results);
        // print_r($validate);
        return $validate;
    }

    public function validateSelfie($selfie, $mykad, $uuid, $fromSelfieScreen, $validate)
    {
        
        //Mobile view - Selfie we should not compare with OLD / previous image uploaded for mykad / passport
        if ($fromSelfieScreen) {
            return 'empty';
        }

        //mykad contains mykad for localites / passport for foreign user type
        if(empty($selfie) || (empty($mykad) && empty($visa))) {
            // echo "EMPTY";
            return 'empty';
        }
        
        // skip for now until develop new face api
        // skip for dev env /  when this is disabled 
        if (Config::getValue('face_comparision') == 'deactive') {
            $result =  Config::getValue('default_face_compare_result') ?  Config::getValue('default_face_compare_result') : false;
            return ['status' => $result == "fail" ? false : true];
        }
        
        // return ['status' => false];
        //Todo Make this flag configurable

        //var_dump("file system bucket"+config('filesystems.disks.s3.bucket'));
        // return ['status' => false, 'bucket' => config('filesystems.disks.s3.bucket'),
        // 'selfie' => $selfie->path,
        // 'mykad' => $mykad->path,
        // 'user' => $uuid];

        // $params = [
        //     'bucket' => config('filesystems.disks.s3.bucket'),
        //     'selfie' => $selfie->path,
        //     'mykad' => $mykad->path,
        //     'user' => $uuid,
        // ];

        $tgtfile = isset($mykad->path) && $mykad->path != null ? $mykad->path : $visa->path;
        $this->compareSelfieWithMyKadPassportOrVisa($selfie->path, $tgtfile, $validate);

        // $response = Http::asForm()->post(env('FACE_API_URL') . '/compare', $params);
        // return $response->json();
        // return $validate;
    }
}

