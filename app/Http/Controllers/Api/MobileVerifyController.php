<?php     

namespace App\Http\Controllers\Api;

use App\Individual;
use App\MobileVerify;
use App\Notifications\EmailVerification;
use App\Notifications\Email;
use App\Notifications\MobileVerification;
use App\Rules\UniqueInModel;
use App\User;
use App\Helpers\Enum;
use App\Coverage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use phpseclib\Crypt\Random;
use App\Http\Controllers\Controller;
use App\Rules\UsersUnique;


class MobileVerifyController extends Controller
{

    /**
     * @api {post} api/sendVerification send mobile verification
     * @apiVersion 1.0.0
     * @apiName SendVerification
     * @apiGroup User
     *
     * @apiDescription It send sms or email verification code then create token for main register. it's used twice in customer's register with different type
     *
     * @apiParam (Request) {String} type email/mobile/reset-pin/reset-mobile/reset-email
     * @apiParam (Request) {String} [fullname]
     *
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[id] encrypted verify id
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *       "status": "success",
     *       "message": "SMS sent! please key in the code",
     *       "data": {
     *           "id": "eyJpdiI6IlpUaWlPdDg0MmZQZXIrRElPUDNhVnc9PSIsInZhbHVlIjoiU29XOS9lblgrTms0Q3BCK0UvcnBwUT09IiwibWFjIjoiZDNhNTBiZDY1OGZkZmRlNzg0Y2I2ZjhlZWQwNTA5ZDU5YjRjNTkxMTAwNjdkYjE4M2Q5ZjVhN2I1YWU5M2QwNiJ9"
     *       }
     *  }
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function sendVerification(Request $request)
    {
        $code = rand(10000, 99999);
        if($request->type == 'email'){

            /*$validator = Validator::make($request->all(),['email'=>['required','email',new UniqueInModel(User::class,'email')]])->fails();

            if($validator){
                return ['status' => 'error', 'message' => __('web/mobileVerify.email_invalid')];
            }*/

            $request->validate([
                'email' => ['required','email',new UsersUnique],
            ]);

            try {
                Notification::route('mail', $request->email)->notify(new EmailVerification($code));
            }catch (\Exception $e){
                //                return ['status' => 'error', 'message' => __('web/mobileVerify.unable_email')];
            }

        }
        
/***************************** EMAIL Deactivation Coverage *********************************/

        elseif($request->type == 'email_product_cancel'){
            $userid = User::where('ref_no',$request->ref_no)->first()->id;
            $covered_id=Individual::where('user_id',$userid)->first()->id;
            $coverages =Coverage::where("covered_id",$covered_id)->where("product_name",$request->product)->whereIn('status',['active','active-increased'])->get();
              
             $cancelreqdate =Carbon::now();
              foreach($coverages as $coverage){
              $coverage->cancel_request_date = $cancelreqdate;
              $coverage->save();
            }
            
            $data['title'] = __('web/messages.deactivating_this_coverage_title',['product' => $request->product]);

            $data['subject'] = __('web/messages.deactivating_this_coverage_subject',['username' => $request->user, 'product' => $request->product, 'ref_no' => $request->ref_no]);
            $content = __('web/messages.deactivating_this_coverage_content',['username' =>  $request->user,'emailid' => $request->user_emailid,'mobile' => $request->mobile,'nric' => $request->nric, 'cancellation_date' => $request->cancellation_date, 'product' => $request->product,'premium_amount' => $request->premium_amount]);

            $email = __('mobile.recipient');

            $request->validate([
                ]);

                try {
                    Notification::route('mail', $email)->notify(new Email($content, $data));
                }catch (\Exception $e){
                }


        }

/***************************** EMAIL Deactivation Coverage ***************************************/        
        
        
        elseif($request->type == 'mobile') {

            $request->validate([
                'mobile' => 'required|digits_between:10,12',
            ]);

            //$check = Individual::where("mobile",str_replace("-","",str_replace('+6', '',$request->mobile_no)));
            $check = Individual::where("mobile",$request->mobile)->get();
            if($check->count() <= 1) {
                $check = $check->first();
                if(empty($check->user))
                    $check = false;
                else {
                    if($check->user->isPendingPromoted()){
                        $check = false;
                    }
                    else{
                        $check = true;
                    }
                }
            }else{
                $check = true;
            }
            if($check){
                return ['status' => 'error', 'message' => __('web/mobileVerify.mobile_error')];
            }

            try {
                //$mobile_no = str_replace("-","",$request->mobile_no);
                //$mobile_no = str_replace("+6","",$mobile_no);
                if (strpos( $request->mobile, '+91') !== false) {
                    Notification::route('sms', '+91' . $request->mobile)->notify(new MobileVerification($code));
                } else {
                    Notification::route('sms', '+' . $request->mobile)->notify(new MobileVerification($code));
                }
            }catch (\Exception $e){
              \Log::error($e->getMessage());
            }
        }
        elseif($request->type == 'reset-pin'){
            $request->validate([
                'mobile' => 'required|digits_between:10,12',
            ]);
            try {
                Notification::route('sms', '+' . $request->mobile)->notify(new MobileVerification($code));
            }catch (\Exception $e){

            }
        }
        elseif($request->type == 'reset-mobile'){
            //need new mobile number and current email
            $request->validate([
                'mobile' => 'required|digits_between:10,12',
            ]);

            try {
                Notification::route('mail', $request->email)->notify(new EmailVerification($code));
            }catch (\Exception $e){
                //                return ['status' => 'error', 'message' => __('web/mobileVerify.unable_email')];
            }
        }
        elseif($request->type == 'reset-email'){
            //need new email number and current mobile
            $request->validate([
                'email' => ['required','email',new UniqueInModel(User::class,'email')],
            ]);

            try {
                Notification::route('sms', '+' . $request->mobile)->notify(new MobileVerification($code));
            }catch (\Exception $e){
                //                return ['status' => 'error', 'message' => __('web/mobileVerify.unable_email')];
            }
        }

        //dd(str_replace('+6', '',$request->mobile),$request->mobile);

        $userId = 0;
        if($request->hasUser){
            $user = auth('api')->user();
            $userId = $user->id;
        }

        $mv             = new MobileVerify();
        $mv->mobile     = ($request->type == 'email' || $request->type == 'reset-mobile') ? $request->email : $request->mobile;
        $mv->code       = $code;
        $mv->verified   = false;
        $mv->expiry     = Carbon::now()->addMinutes(5);
        $mv->token      = Str::uuid()->toString();
        $mv->user_id    = $userId;
        $mv->is_used    = 0;
        $mv->save();

        return [
            'status'    => 'success',
            'message'   => __('web/mobileVerify.success'),
            'data'      =>[
                'id'    => encrypt($mv->id)
            ]
        ];
    }

    /**
     * @api {post} api/validateVerification validate verification
     * @apiVersion 1.0.0
     * @apiName ValidateVerification
     * @apiGroup User
     *
     * @apiDescription It checks code that user enter in app
     *
     * @apiParam (Request) {String} mobile_id encrypted mobile id
     * @apiParam (Request) {String} mobile_code
     *
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[token]
     *
     * @apiSuccessExample {json} Success-Response:
     *{
     *  "status": "success",
     *  "message": "Code Verified",
     *  "data": {
     *      "token": "9fe1d03c-2438-45c3-8131-0833a788893f"
     *  }
     *}
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function validateVerification(Request $request) {

        $mv = MobileVerify::findOrFail(decrypt($request->mobile_id));
        
        if($request->hasUser){
            $user = auth('api')->user();
            $otpVerified = ($mv->expiry > Carbon::now() && $request->mobile_code == $mv->code && $mv->verified==0 && $mv->user_id==$user->id)?true:false;
        }else{
            $otpVerified =  ($mv->expiry > Carbon::now() && $request->mobile_code == $mv->code && $mv->verified==0 && $mv->user_id==0)?true:false;
        }
        
        
        if($otpVerified){
            $mv->verified = 1;
            $mv->save();
            return [
                'status'   => 'success',
                'message'  => __('web/messages.code_verified'),
                'data'     => [
                    'token'    => $mv->token
                ]
            ];
        }


        return [
            'status'   => 'error',
            'message'  => __('web/mobileVerify.code_mismatch'),
        ];

    }

    public function userSendVerification(Request $request)
    {
        $input = $request->all();
        $input['hasUser'] = true;
        $newRequest = new Request($input);
        return $this->sendVerification($newRequest);
    }
    
    public function userValidateVerification(Request $request) {
        $input = $request->all();
        $input['hasUser'] = true;
        $newRequest = new Request($input);
        return $this->validateVerification($newRequest);
    }
}
