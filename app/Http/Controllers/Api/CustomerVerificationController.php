<?php     

namespace App\Http\Controllers\Api;

use App\CustomerVerification;
use App\CustomerVerificationDetail;
use App\Helpers;
use App\Http\Controllers\User\VerificationController;
use App\Beneficiary;
use App\Individual;
use App\SelfieMatch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Config;

class CustomerVerificationController extends Controller
{
    public function set(Request $request)
    {

        $user = $request->user()->profile;
        $userProfile = $user;
        $userType = '';
        if(empty($user->profile->nationality) && empty($userProfile->nationality))
            $userType = 'Malaysian';
        else {
            $userType = $user->nationality != null ? 
                $user->nationality : 
                $user->profile->nationality;
        }

        $nric = $userProfile->nric;
        
        //  if($user->verification != null) return ['status' => 'error', 'message' => 'Already Done.'];

        if($request->input('type') == 'child_verification'){
            $user = Individual::OnlyChild()->where("uuid",$request->input('user_id'))->first();
            //unAuthorized(empty($user));
        }
//        $request->validate([
//            //   'selfie' => 'required|mimes:jpg,jpeg,png,bmp|max:5000',
//            //  'myKad' => 'required|mimes:jpg,jpeg,png,bmp|max:5000',
////            'visa_page' => 'mimes:jpg,jpeg,png,bmp|max:5000', //if foreigner need visa
//        ]);

        $customerVerification = CustomerVerification::updateOrCreate(['individual_id' => $user->id]);
        $customerVerification->updated_at = now();
        $customerVerification->save();

        if($customerVerification->lastUserDetail && $customerVerification->lastDetail->id == $customerVerification->lastUserDetail->id){

            $customerVerificationDetail   = CustomerVerificationDetail::where("kyc_id",$customerVerification->id)->where("status","Pending")->orderBy("created_at","desc")->first();

        }

        if(empty($customerVerificationDetail)) {

            $customerVerificationDetail             = new CustomerVerificationDetail();

        }

        $customerVerificationDetail->kyc_id         = $customerVerification->id;
        $customerVerificationDetail->status         = 'Pending';
        $customerVerificationDetail->note           = '';
        $customerVerificationDetail->description    = '';
        $customerVerificationDetail->type           = 'user';
        $customerVerificationDetail->created_by     = $user->user->id ?? 0;
        $customerVerificationDetail->created_at     = now();
        $customerVerificationDetail->updated_at     = now();
        $customerVerificationDetail->save();

        //EKYC changes
        $fromSelfieScreen = false;
        $fromVisaScreen = false;
        if($request->hasFile('selfie')) {
            $fromSelfieScreen = true;
            $customerVerificationDetail->documents()->where("type","selfie")->delete();
            $selfie = Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $customerVerificationDetail, 'selfie');
        }else{
            $selfie = $customerVerificationDetail->documents()->where("type","selfie")->first();
        }
        if($request->hasFile('myKad')) {
            $customerVerificationDetail->documents()->where("type","myKad")->delete();
            $myKad = Helpers::crateDocumentFromUploadedFile($request->file('myKad'), $customerVerificationDetail, 'myKad');
        }else{
            $myKad = $customerVerificationDetail->documents()->where("type","myKad")->first();
        }
        if($request->hasFile('visa')) {
            //$fromSelfieScreen = true;
            $fromVisaScreen = true;
            $customerVerificationDetail->documents()->where("type","visa")->delete();
            $visa = Helpers::crateDocumentFromUploadedFile($request->file('visa'), $customerVerificationDetail, 'visa');
        }else{
            $visa = $customerVerificationDetail->documents()->where("type","visa")->first();
        }

        if ($fromSelfieScreen == true || 
            ($userType != "Malaysian" && $fromVisaScreen != true)) {
            $isMatch = false;
            return [
                'status' => 'success',
                'message' => $isMatch ? __('web/messages.documents_uploaded') : __('web/messages.your_mykad_selfie_dosent_match'),
                'data' => [
                    'message' => $isMatch ? null : __('web/messages.your_mykad_selfie_dosent_match'),
                    'next_page' => $user->isOld() ? 'dashboard_page' : 'order_review_page',
                    'config'=>app(UserController::class)->getStatus($request,$request->user())
                ]
            ];
        }

        $validate = new \stdClass;
        
        try {
            if (!$fromSelfieScreen && isset($myKad->path)) {
                $tgtfile = $myKad->path;
                app(VerificationController::class)->extractTextAndCmpMyKadOrPassport($tgtfile, $validate, $userType, $nric);
            }
        } catch (\Exception $e){
        
            // print_r("Error Occured at mykad/passport ".$e);
        }

        // echo "<pre>";
        // print_r($validate);

        try {
            if ($validate && 
                    (Config::getValue('ekyc_strict_comparision') == 'deactive' || 
                    ($validate->textExtracted != null && !str_contains($validate->textExtracted, 'Not a Geniune'))
                    )
                ) {
                app(VerificationController::class)->validateSelfie($selfie, 
                            $myKad,$request->user()->uuid, $fromSelfieScreen, $validate);
            }
        } catch (\Exception $e){
        }
        
        // echo "<pre>";
        // print_r($validate);
        // exit;
        if(!empty($user->user)) {
            $msg = $user->user->messages()->whereJsonContains('data->id','verification')->first();
            if(!empty($msg)){
                $msg->is_read = '1';
                $data = json_decode($msg->data,true);
                $data['buttons'] = [];
                $msg->data = json_encode($data);
                $msg->save();
            }
        }
        

        //TODO move this to matched section
        if (!empty($selfie) && !empty($myKad)) {
            //validate the verification
            $customerVerification->status = 'Pending';
            $customerVerification->save();


            //change a parameter to call static::saved function in Individual.php
            $user = Individual::WithChild()->find($user->id);
            $user->save();

//                $selfie->delete();
//                $myKad->delete();
        }

        // echo "<pre>";
      //  print_r($validate->textExtracted);
                
        // if(!empty($validate->status) &&  $validate->similarity > 95){
    if(!str_contains($validate->textExtracted, 'Not a Geniune') && !str_contains($validate->textExtracted, 'MyKad') && !str_contains($validate->textExtracted, 'Passport') && !str_contains($validate->textExtracted, 'Pasport') && !str_contains($validate->textExtracted, '再试') && !str_contains($validate->textExtracted, '您输入的护照号码是')){    
        if(!empty($validate->similarity > 90)){

            $user->selfieMatch()->delete();

            $match = new SelfieMatch();
            $match->individual_id = $user->id;
            // $match->face_id = $validate->face->Face->FaceId;
            // $match->image_id = $validate->face->Face->ImageId;
            $match->similarity = $validate->similarity;

            // if (isset($validate->textExtracted)) {
                // $match->textExtracted = $validate->textExtracted;
                // $match->textMatchFoundwithReg = $textMatchFoundwithReg;
                $match->image_id = (!empty($validate->textExtracted) ? $validate->textExtracted : "");
                $match->face_id = (!empty($validate->textMatchFoundwithReg) ? $validate->textMatchFoundwithReg: "");
            // }
            // $match->collection = $validate->collection;
            // $match->face = $validate->face;
            $match->save();
            $isMatch = true;

            //Auto Approve
            // if (!empty($validate) && isset($validate->autoapprove) && $validate->autoapprove == true) {
            if (!empty($validate->similarity)) {

                $customerVerificationDetail->status         = 'Accepted';
                $customerVerificationDetail->note           = 'Auto approved, extractedText from img '.$validate->textExtracted;
                $customerVerificationDetail->description    = 'Auto approved ';
                $customerVerificationDetail->type           = 'auto';
                // $customerVerificationDetail->updated_by     = 0;
                $customerVerificationDetail->updated_at     = now();
                $customerVerificationDetail->save();

                
                $customerVerification->status = 'Accepted';
                $customerVerification->save();
                //Dev-634 test
                $userProfile = $user;
                $email =$userProfile->email;
                $beneficiary = Beneficiary::where("email",$email)->get();
                if($beneficiary!=null){
                   foreach($beneficiary as $bn){
            
                       $bn->status     = 'registered';
                       $bn->save();
             
        }
    }

            }

        }else if (!empty($validate->similarity) && ($validate->similarity == -1 || $validate->similarity)) {

            $user->selfieMatch()->delete();
            $match = new SelfieMatch();
            $match->individual_id = $user->id;
            $match->similarity = $validate->similarity != -1 ? $validate->similarity : 0;
            
            // if (isset($validate->textExtracted)) {
                // $match->textExtracted = $validate->textExtracted;
                // $match->textMatchFoundwithReg = $textMatchFoundwithReg;
                $match->image_id = (!empty($validate->textExtracted) ? $validate->textExtracted : "");
                $match->face_id = (!empty($validate->textMatchFoundwithReg) ? $validate->textMatchFoundwithReg: "");
            // }

            $match->save();
            $isMatch = false;
        } else {           

            $user->selfieMatch()->delete();
            $match = new SelfieMatch();
            $match->individual_id = $user->id;
            $match->similarity = 0;
            $match->image_id = (isset($validate->textExtracted) ? $validate->textExtracted : "");
            $match->face_id = (isset($validate->textMatchFoundwithReg) ? $validate->textMatchFoundwithReg: "");
            $match->save();
            $isMatch = false;
        }
        
    }else {
     

        if(!str_contains($validate->textExtracted, 'Not a Geniune')){ 
            if($validate->similarity < 90){

                $validate->textExtracted = "Not a Geniune";
            }
            
            $match = new SelfieMatch();
            $match->individual_id = $user->id;
            $match->similarity = $validate->similarity;
            $match->save();
        }
        $isMatch = false;
        }
       

        return [
            'status' => 'success',
            'message' => $isMatch ? __('web/messages.documents_uploaded') : $validate->textExtracted,
            'data' => [
                'message' => $isMatch ? '' : $validate->textExtracted,
                'next_page' => $user->isOld() ? 'dashboard_page' : 'order_review_page',
                'config'=>app(UserController::class)->getStatus($request,$request->user())
            ]
        ];
    }

    public function get(Request $request)
    {
        $user = $request->user()->profile;

        return ['status' => 'success', 'data' => ['is_local' => $user->is_local(), 'myKad_exist'=> $user->verification != null , 'selfie_exist' => $user->verification != null]];
    }


    //claims selfie verify
     //dev-499 - unable to register claim , stuck in selfi verify 
    public function verify(Request $request)
    {
        $user = $request->user()->profile;

        //Todo Dynamic Face recognize comparision
        return ['status' => 'success', 'match' => true];
    }

}

