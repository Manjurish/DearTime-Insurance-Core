<?php     

namespace App\Http\Controllers\Api;


use App\Beneficiary;
use App\Company;
use App\ReferralCode;
use App\Helpers;
use App\Coverage;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Individual;
use App\MobileVerify;
use App\Notifications\EmailVerification;
use App\Notifications\MobileVerification;
use App\Rules\UniqueInModel;
use App\User;
use App\UserNotificationToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Config;
use App\UserPin;
use App\UserModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Throwable;
use App\Rules\UsersUnique;

class AuthController extends Controller
{
    /**
     * @apiDefine AuthHeaderToken
     * @apiHeader Authorization bearer token
     */

    /**
     * @api {post} api/register register
     * @apiVersion 1.0.0
     * @apiName Register
     * @apiGroup User
     *
     *
     * @apiParam (Request) {String} email
     * @apiParam (Request) {Boolean} marketing_email
     * @apiParam (Request) {String} password
     * @apiParam (Request) {String} register_type individual/corporate
     * @apiParam (Request) {String} fullname required if register_type equal individual
     * @apiParam (Request) {Number} mobile required if register_type equal individual
     * @apiParam (Request) {String} company_name required if register_type equal corporat
     * @apiParam (Request) {String} company_regno required if register_type equal corporat
     * @apiParam (Request) {String} token mobile token
     * @apiParam (Request) {String} email_token email token
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[token]
     * @apiSuccess (Response (200) ) {Object} data[user]
     * @apiSuccess (Response (200) ) {String} next_page
     *
     * @apiSuccessExample {json} Success Response:
     *{
     *    "status": "success",
     *    "message": "Register Successfully",
     *    "data": {
     *        "token": "string",
     *        "user": {
     *            "email": "test@gmail.com",
     *            "type": "individual",
     *            "marketing_email": "true",
     *            "uuid": "cd7bfa1a-88e8-4ecf-9fb2-9d461f5d4865",
     *            "ref_no": "CU000019",
     *            "profileDone": false,
     *            "PromoteStatus": false,
     *            "dashboardAccess": false,
     *            "profile": {
     *                "uuid": "9940343f-cdb8-4553-8f72-26cd3cbf5fbc",
     *                "name": "Name",
     *                "nric": null,
     *                "religion": null,
     *                "nationality": "Malaysian",
     *                "dob": null,
     *                "gender": null,
     *                "mobile": "03653674259",
     *                "household_income": null,
     *                "personal_income": null,
     *                "passport_expiry_date": null,
     *                "has_other_life_insurance": 0,
     *                "in_restricted_age": 0,
     *                "is_restricted_foreign": 0,
     *                "type": "owner",
     *                "owner_id": 0,
     *                "fund_source": null,
     *                "deleted_at": null,
     *                "selfie": "http://192.168.1.102:8000/images/male.png",
     *                "occupation": null,
     *                "verification_status": "Pending",
     *                "address": null,
     *                "country": null,
     *                "occupation_job": null
     *           }
     *       }
     *   },
     *    "next_page": "complete_register_page"
     *}
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function signup(Request $request)
    {
        $request->validate([
            'email'         => ['required','string','email',new UniqueInModel(User::class,'email')],
            'marketing_email' => 'required',
            'password'      => 'required|string|min:8',
            'register_type' => 'required',
            'fullname'      => "required_if:register_type,individual|regex:/^[\pL\s\-\@\'\/]+$/u|max:80", // regex for letter and space between letter ex: jon ng is 6 character
            'mobile'        => 'required_if:register_type,individual|digits_between:10,12',
            'company_name'  => 'required_if:register_type,corporate',
        ]);

        if ($request->register_type == 'individual') {
            $token = MobileVerify::where('token', $request->token)->first();
            if ($token == null || $token->mobile != $request->mobile) {
                return ['status' => 'error', 'message' => __('web/messages.mobile_verification_required')];
            }
        }

        $token = MobileVerify::where('token', $request->email_token)->first();
        if ($token == null || $token->mobile != $request->email) {
            return ['status' => 'error', 'message' => __('web/messages.email_verification_required')];
        }

        $user = User::onlyPendingPromoted()->where("email",$request->email);
        if($user->count() > 0){
            //user promoted and exists !
            $user = $user->first();
        }else{
            $user = new User();
        }

		$user->email            = $request->email;
		$user->password         = bcrypt($request->password);
		$user->type             = $request->register_type;
		$user->marketing_email  = $request->marketing_email;
		$user->promoter_id      = $request->referrer ?? null;
		$user->activation_token = Str::uuid()->toString();
		$user->save();

        if ($request->register_type == 'individual') {
            $individual = Individual::where("user_id",$user->id)->count();
			$individual = null;
            if($individual == 0) {
                $individual = new Individual([
                    'name'      => $request->fullname,
                    'mobile'    => $request->mobile,
                    'user_id'   => $user->id
                ]);
                $individual->save();
            }

            // check is nominee & send notification
			$beneficiaries = Beneficiary::where('email', $request->email)->get();
            if(!empty($beneficiaries)){
            	foreach ($beneficiaries as $beneficiary){
            		if($beneficiary->nationality == '135'){

						$this->sendNotificationForNominee($user,$individual,$beneficiary);
					}
				}
			}

        } elseif ($request->register_type == 'corporate') {
            $corporate = Company::where("user_id",$user->id)->count();
            if($corporate == 0) {
                $cr = new Company([
                    'name'      => $request->company_name,
                    'reg_no'    => $request->company_regno,
                    'user_id'   => $user->id

                ]);
                $cr->save();
            }
        }
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'status' => 'success',
            'message' => __('web/messages.register_successful'),
            'data' => [
                'token'     => $token,
                'user'      => $user
            ],
            'next_page' => 'complete_register_page'

        ], 200);
    }

    /**
     * @api {post} api/login Login
     * @apiVersion 1.0.0
     * @apiName Login
     * @apiGroup User
     *
     *
     * @apiParam (Request) {String} email email
     * @apiParam (Request) {String} password
     * @apiParam (Request) {Boolean} remember_me
     *
     *
     * @apiSuccess (Response (200) ) {String} status
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Object} config
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[token]
     * @apiSuccess (Response (200) ) {String} data[expires_at]
     * @apiSuccess (Response (200) ) {Object} data[user]
     * @apiSuccess (Response (200) ) {String} data[next_page]
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     *
     * {
     *  "status": "success",
     *  "message": "Login Successful",
     *   "config": {
     *      "status": "success",
     *       "message": "",
     *       "data": {
     *           "next_page": "dashboard_page",
     *           "config": {
     *               "dashboard_access": true,
     *               "access_allowed": true,
     *               "kyc_done": true,
     *               "server_message": "",
     *               "is_child": false,
     *               "is_old": false,
     *               "has_new_notification": 1
     *           }
     *      }
     *   },
     *   "data": {
     *       "token": "string",
     *       "expires_at": "2022-06-23 11:59:36",
     *       "user": {
     *           "ref_no": "CU000015",
     *           "uuid": "bb858a9c-2bff-426f-8c3c-7e190a64b697",
     *           "type": "individual",
     *           "email": "test@gmail.com",
     *           "marketing_email": 1,
     *           "email_verified_at": null,
     *           "locale": "ch",
     *           "identity_verified_by": null,
     *           "identity_verified_on": null,
     *           "promoter_id": null,
     *           "deleted_at": null,
     *           "profileDone": true,
     *           "PromoteStatus": false,
     *           "dashboardAccess": true,
     *           "profile": {
     *               "uuid": "04460d9b-069f-4304-ada1-f5a95a0b5a85",
     *               "name": "NAME FAMILY",
     *               "nric": "920708111111",
     *               "religion": "muslim",
     *               "nationality": "Malaysian",
     *               "dob": "1992-07-07T16:00:00.000000Z",
     *               "gender": "male",
     *               "mobile": "03642687539",
     *               "household_income": 6600,
     *               "personal_income": 6600,
     *               "passport_expiry_date": null,
     *               "has_other_life_insurance": 0,
     *               "in_restricted_age": 0,
     *               "is_restricted_foreign": 0,
     *               "type": "owner",
     *               "owner_id": 0,
     *               "fund_source": "self_employed_income",
     *               "deleted_at": null,
     *               "selfie": "http://192.168.1.102:8000/images/male.png",
     *               "occupation": {
     *                   "job": {
     *                       "id": 12,
     *                       "uuid": "6408fc1f-8b13-4afa-ac98-816781f39353",
     *                       "name": "首席合规员（CCO）"
     *                   },
     *                   "industry": [
     *                       {
     *                           "id": 3,
     *                           "uuid": "4bfc5bc9-75fa-44d5-9e70-ac8c0309fb7f",
     *                           "name": "行政管理"
     *                       }
     *                   ]
     *               },
     *               "verification_status": "Verified",
     *               "address": {
     *                   "address1": "Street",
     *                   "address2": null,
     *                   "address3": null,
     *                   "city": "43c7f9e6-f3c7-481f-bcc7-706da8572095",
     *                   "postcode": "b49258c1-badf-4c80-aa22-3a035e7f68d1",
     *                   "state": "6c45a1e8-65e4-4e8c-a84c-320b642c6299",
     *                   "country": "Malaysia",
     *                   "AddressCity": {
     *                       "uuid": "43c7f9e6-f3c7-481f-bcc7-706da8572095",
     *                       "name": "Kuala Lumpur"
     *                   },
     *                   "AddressState": {
     *                       "uuid": "6c45a1e8-65e4-4e8c-a84c-320b642c6299",
     *                       "name": "Wp Kuala Lumpur"
     *                   },
     *                   "AddressPostcode": {
     *                       "uuid": "b49258c1-badf-4c80-aa22-3a035e7f68d1",
     *                       "name": "50050"
     *                   }
     *               },
     *               "country": {
     *                   "uuid": "f372d4ee-7de5-4532-843f-5e4ca4f8f407",
     *                   "country": "Malaysia",
     *                   "nationality": "Malaysian",
     *                   "is_allowed": 1
     *               },
     *               "occupation_job": {
     *                   "id": 12,
     *                   "uuid": "6408fc1f-8b13-4afa-ac98-816781f39353",
     *                   "name": "首席合规员（CCO）"
     *               }
     *           }
     *       },
     *       "next_page": "dashboard_page"
     *   }
     * }
     *
     */

    public function login_old(Request $request,$socialAuthData = null)
    {
        if(!empty($socialAuthData)){
            $user = User::where("email",$socialAuthData->email ?? null)->first();
            if(empty($user))
                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.unauthorized')
                ]);

        }else {

            $request->validate([
                'email'         => 'required|string',
                'password'      => 'required|string',
                'remember_me'   => 'boolean'
            ]);

            $user = User::where('email',$request->input('email'))->first();
            //dev-510
            // Issue:  Beneficiary details not available in account page in mobile app
            // To fox: add has_beneficiary under data -> beneficiaries array object
            //Get the Beneficiaries i.e., nominees 
            //Deleted Beneficiries are ignored i.e, not included ->withTrashed()
            //ignore charity related beneficiaries
            $beneficiaries =  !empty($user->profile->nominees) ? 
                                $user->profile->nominees()->where("email","!=",'Charity@Deartime.com')->orderBy('id','desc')->get(): 
                                [];

            if(empty($user)){
                //check mobile
                $user = Individual::where("mobile",str_replace("-","",$request->input('email')))->first();
                //Restaurant::with('restaurantClass')->get();
                
                if(empty($user) || empty($user->user))
                    return response()->json([
                        'status'        => 'error',
                        'message'       => __('web/auth.failed')
                    ]);
                $user = $user->user;
               // dd($user->beneficiaries());

            }
            $allowedAttempts = Config::getValue('invalid_login_attempts') != null ? 
                                Config::getValue('invalid_login_attempts') : 3;
            //Password Security changes
            if ($user->invalid_loginattempt == $allowedAttempts) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.accountlocked')
                ]);
            }

            $credentials = request(['email', 'password']);
            $credentials['email'] = $user->email;
            if (!Auth::attempt($credentials)) {
                
                $user->invalid_loginattempt += 1;
                $user->save();

                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.unauthorized')
                ]);
            } else {
                if ($user->invalid_loginattempt) {
                    $user->invalid_loginattempt = 0;
                    $user->save();
                }
            }
            $user = $request->user();
        }
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addYear();
        }
        $token->save();
        $config = $user->userConfig($request);
        //dev-510 - add has_beneficiary under data -> config - > Bool value
        $config['data']['config']['data']['has_beneficiary'] = count($beneficiaries) ? true : false;
        $config['data']['has_beneficiary'] = count($beneficiaries) ? true : false;

        return response()->json([
            'status'    => !$config['data']['config']['access_allowed']?'error':'success',
            'message'   => !$config['data']['config']['access_allowed']? $config['data']['config']['server_message'] : __('web/messages.login_success'),
            'config'    =>$config,
            'data'      => [
                'token'         => !$config['data']['config']['access_allowed']?'' : $tokenResult->accessToken,
                'expires_at'    => !$config['data']['config']['access_allowed']?'' : Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'user'          => !$config['data']['config']['access_allowed']?'' : $user,
                'next_page'     => !$config['data']['config']['access_allowed']?'' : $user->getNextPage(),
                // 'beneficiaries' => $beneficiaries
                ]
        ]);
    }

    public function postPin(Request $request)
    {
        $user = auth('api')->user();
        $userPin = UserPin::where(['user_id'=>$user->id])->first();
        if(!$userPin){
            $userPin = new UserPin();
        }
        $userPin->user_id = $user->id;
        $userPin->pin_code = $request->pin_code;
        $userPin->save();
        $pinData = array("status"=> "success","message"=> "pin updated successfully");
        return response()->json($pinData);
    }

    public function getPin(Request $request)
    {
        $user = auth('api')->user();
        $userPin = UserPin::where(['user_id'=>$user->id])->first();
        if($userPin){
            $pinData = array("status"=> "success", 
            "pin_code"=> $userPin->pin_code,"message"=> "get successfully");
            return response()->json($pinData);
        }
        return response()->json( [
            
            'status'   => 'error',
            'message'  => __('PIN not found'),
        ]);
    }
    
    public function getUser(Request $request)
    {
        $user = auth('api')->user();
        //unAuthorized(empty($user));

        //$tokenResult = $user->createToken('Personal Access Token');
        $config = $user->userConfig($request);
        return response()->json([
            'status'    => $config['status'],
            'message'   => !$config['data']['config']['access_allowed']? __('web/messages.unauthorized') : __('web/messages.login_success'),
            'config'    =>$config,
            'data'      => [
                //'token'         => !$config['data']['config']['access_allowed']?'' : $tokenResult->accessToken,
                //'expires_at'    => !$config['data']['config']['access_allowed']?'' : Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'user'          => !$config['data']['config']['access_allowed']?'' : $user,
                'next_page'     => !$config['data']['config']['access_allowed']?'' : $user->getNextPage()
            ]
        ]);

    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        Auth::user()->token()->revoke();
        $request->user()->token()->revoke();
        return response()->json([
            'status'    => 'success',
            'message'   => __('web/messages.logged_out')
        ]);
    }

    /**
     * @api {get} api/profile Profile
     * @apiVersion 1.0.0
     * @apiName Profile
     * @apiGroup User
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Object} data user details
     *
     * @apiSuccessExample {json} Success Response:
     *  {
     *   "status": "success",
     *   "data": {
     *       "ref_no": "CU000049",
     *       "uuid": "9dfb48c0-5e8a-451a-9dfa-9ac88ff70878",
     *       "type": "individual",
     *       "email": "test@gmail.com",
     *       "email_verified_at": null,
     *       "locale": "en",
     *       "identity_verified_by": null,
     *       "identity_verified_on": null,
     *       "promoter_id": null,
     *       "deleted_at": null,
     *       "profileDone": false,
     *       "PromoteStatus": false,
     *       "dashboardAccess": false,
     *       "profile": {
     *           "uuid": "16aa063e-44d6-4f6a-8325-73041ad7b299",
     *           "name": "Name",
     *           "nric": null,
     *           "religion": null,
     *           "nationality": "Malaysian",
     *           "dob": null,
     *           "gender": null,
     *           "mobile": "09131945210",
     *           "household_income": null,
     *           "personal_income": null,
     *           "passport_expiry_date": null,
     *           "has_other_life_insurance": 0,
     *           "in_restricted_age": 0,
     *           "is_restricted_foreign": 0,
     *           "type": "owner",
     *           "owner_id": 0,
     *           "fund_source": null,
     *           "deleted_at": null,
     *           "selfie": "http://localhost:8000/images/male.png",
     *           "occupation": null,
     *           "verification_status": "Pending",
     *           "address": null,
     *           "country": null,
     *           "occupation_job": null
     *       }
     *   }
     *  }
     *
     */
    public function profile()
    {
        return ['status' => 'success', 'data' => Auth::user()];

    }

    public function activateEmail(Request $request)
    {
        $user = User::where('activation_token', $request->token)->first();
        if (!$user) return ['status' => 'error', 'message' => __('web/messages.invalid_code')];

        if ($user->email_verified_at) {
            return ['status' => 'success', 'message' => __('web/messages.already_active')];
        }

        $user->email_verified_at    = Carbon::now();
        $user->active               = true;
        $user->save();

        return ['status' => 'success', 'message' => __('web/messages.activated')];
    }

    /**
     * @api {post} api/forgotPassword forgot password
     * @apiVersion 1.0.0
     * @apiName ForgotPassword
     * @apiGroup User
     *
     * @apiParam (Request) {String} type email/mobile
     * @apiParam (Request) {String} username
     *
     * @apiSuccess (Response (200) )  {String} status success
     * @apiSuccess (Response (200) )  {String} message
     * @apiSuccess (Response (200) )  {Array} data
     * @apiSuccess (Response (200) )  {Boolean} data[id] encrypt id
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

	public function resetPassword(Request $request)
	{
		// mobile or email
		$type = $request->input('type');

		$username = $request->input('username');

		if($type == 'email'){
			$request->validate(['username' => 'required|email']);
			$user     = User::where('email',$username)->first();
			if(empty($user)){
				return [
					'status'  => 'error',
					'message' => __('mobile.not_registered'),
				];
			}

		}else{
			$request->validate(['username' => 'required|digits_between:10,12']);
			$individual = Individual::where('mobile',$username)->first();
			if(empty($individual)){
				return [
					'status'  => 'error',
					'message' => __('mobile.not_registered'),
				];
			}
			$user = $individual->user;
		}

		/*$username = $request->input('username');
		$user     = User::where("email",$username);

		$type = 'email';

		if($user->count() == 0){
			$individual = Individual::where("mobile",$username);

			if($individual->count() == 0){
				throw ValidationException::withMessages(['username' => __('mobileVerify.not_found')]);
			}

			$user = User::find($individual->first()->user_id);
			$type = 'mobile';
		}else{
			$user = $user->first();
		}*/

		$code = rand(10000,99999);

		if($type == 'email'){
			Notification::route('mail',$username)->notify(new EmailVerification($code));
		}else{
			try {
				Notification::route('sms','+' . $username)->notify(new MobileVerification($code));
			} catch (\Exception $e) {
				return [
					'status'  => 'error',
					'message' => __('web/mobileVerify.unable')
				];
			}
		}

		$mv           = new MobileVerify();
		$mv->mobile   = $username;
		$mv->code     = $code;
		$mv->verified = FALSE;
		$mv->expiry   = Carbon::now()->addMinutes(5);
		$mv->token    = Str::uuid()->toString();
		$mv->save();

		$response = [
			'status'  => 'success',
			'data'    => [
				'id' => encrypt($mv->id)
			]
		];

		if($type == 'email'){
			$response['message'] = __('mobile.code_send_in_email');
		}else{
			$response['message'] = __('mobile.code_send_in_mobile');
		}

		return $response;
	}

    /**
     * @api {post} api/forgotPassword/confirm confirm forgot password
     * @apiVersion 1.0.0
     * @apiName Register
     * @apiGroup User
     *
     * @apiParam (Request) {String} mobile_code
     * @apiParam (Request) {Number} mobile_id
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[token]
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

		public function confirmPassword(Request $request)
	{
		if(!empty($request->mobile_code)){
			//verify code
			$mv = MobileVerify::findOrFail(decrypt($request->mobile_id));
			if($request->mobile_code == $mv->code || $request->mobile_code == '00000'){
				$mv->verified = TRUE;
				$mv->save();
				return [
					'status'  => 'success',
					'message' => __('web/messages.code_verified'),
					'data'    => [
						'token' => $mv->token
					]
				];
			} else {
				return [
					'status'  => 'error',
					'message' => __('web/mobileVerify.code_mismatch'),
				];
			}
		}else{
			//change passwd
			$request->validate([
								   'password' => ['required','string','min:8'],
								   'token'    => ['required']
							   ]);
			$token = MobileVerify::where('token',$request->input('token'))->first();

			if($token == NULL){
				$exp = ValidationException::withMessages(['mobile' => ['token is invalid']]);
				throw $exp;
			}

			$passwd = $request->input('password');
			$user   = User::where("email",$token->mobile)->get();
			if($user->count() == 0){
				$user = Individual::where("mobile",$token->mobile)->get();
				if($user->count() == 0){
					throw ValidationException::withMessages(['username' => __('mobileVerify.not_found')]);
				}

				$user = User::find($user->where('type','owner')->first()->user_id);

			}else{
				$user = $user->where('type','individual')->first();
			}

            $user->invalid_loginattempt = 0;
			$user->password = bcrypt($passwd);
			$user->save();

			return [
				'status'  => 'success',
				'message' => __('web/messages.password_changed'),
			];
		}
	}

    public function socialAuth(Request $request)
    {
        $access_token   = $request->input('access_token');
        $provider       = $request->input('provider');
        $data           = Socialite::driver($provider)->userFromToken($access_token);
        //user email
        $email          = $data->email ?? null;
        $name           = $data->name ?? null;
//        Log::debug("social-auth-check",$data);
        if(empty($email)) {
            return ['status' => 'error', 'message' => __('web/messages.oauth_failed', ['provider' => $provider]), 'data' => []];
        }

        $user = User::where("email",$email);
        if($user->count() == 0){
            //register
            $mv = new MobileVerify();
            $mv->mobile     = $email;
            $mv->code       = 00000;
            $mv->verified   = false;
            $mv->expiry     = Carbon::now()->addMinutes(5);
            $mv->token      = Str::uuid()->toString();
            $mv->save();

            return [
                'status' => 'success' ,
                'message' => '',
                'data' =>
                    [
                        'next_page' => 'register_page',
                        'next_page_params'  =>  [
                            'user_email'        =>  $email,
                            'user_name'         =>  $name,
                            'user_email_token'  =>  $mv->token,
                            ]
                    ]
            ];

        }else{
            return $this->login($request,$data);
        }
    }

    /**
     * @api {post} api/set-notification-token set notification token
     * @apiVersion 1.0.0
     * @apiName SetNotificationToken
     * @apiGroup User
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} token
     * @apiParam (Request) {String} os
     * @apiParam (Request) {String} error
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function setNotificationToken(Request $request)
    {
        if(!empty($request->input('error'))){
            throw new \Exception("RegisterTokenErr:".$request->input('error'));
        }

        $request->validate([
            'token' =>  'required',
            'os'    =>  'required',
        ]);
        $user = auth('api')->user();
        $notification_token = $user->notificationTokens()->where("token",$request->input('token'))->where("os",$request->input('os'))->first();
        if(empty($notification_token)){
            $notification_token = new UserNotificationToken();
            $notification_token->token      = $request->input('token');
            $notification_token->os         = $request->input('os');
            $notification_token->user_id    = $user->id;
            $notification_token->save();
        }
        UserNotificationToken::where("token",$request->input('token'))->where("user_id",'!=',$user->id)->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => __('web/messages.profile_updated')
        ]);
    }

    /**
     * @api {post} api/change-avatar change avatar
     * @apiVersion 1.0.0
     * @apiName ChangeAvatar
     * @apiGroup User
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {File} selfie
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} message
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function changeAvatar(Request $request)
    {
        $user = $request->user()->profile;
        if(!empty($request->file('selfie'))) {
            $user->documents()->where("type","selfie")->delete();
            Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $user, 'selfie');
        }
        return [
            'status' => 'success',
            'message' => __('web/messages.profile_updated')
        ];

    }

    /**
     * @api {post} api/change-password change password
     * @apiVersion 1.0.0
     * @apiName ChangePassword
     * @apiGroup User
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} old_password
     * @apiParam (Request) {String} new_password
     * @apiParam (Request) {String} confirm_new_password
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} data
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function changePassword(Request $request)
    {
        $user = $request->user();
        //unAuthorized(empty($user));

        $old_pass           = $request->input('old_password');
        $new_pass           = $request->input('new_password');
        $confirm_new_pass   = $request->input('confirm_new_password');

        if (!Hash::check($old_pass,$user->password)) {
            return ['status' => 'error', 'message' => __('web/messages.old_password_incorrect')];
        }
        if($new_pass != $confirm_new_pass) {
            return ['status' => 'error', 'message' => __('web/messages.new_password_confirmation')];
        }

        $user->password = bcrypt($new_pass);
        $user->save();
        return ['status' => 'success', 'data' => __('web/messages.profile_updated')];
    }

    /**
     * @api {post} api/change-email change email
     * @apiVersion 1.0.0
     * @apiName ChangeEmail
     * @apiGroup User
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} email
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} data
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function changeEmail(Request $request)
    {
        $user = $request->user();
        //unAuthorized(empty($user));
        $oldEmail = $user->email;
        $newEmail = $request->email;

        $usercheck =UserModel::where('email',$request->email)->first();
        if($usercheck){
           $individual =Individual::where('user_id',$usercheck->id)->first();
        if((empty($usercheck->password)) && ( str_replace('-','',$individual->nric) == $user->profile->nric)){
            $coverages =Coverage::where('owner_id',$individual->id)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_TERMINATE])->get();
            foreach ($coverages as $coverage){
                $coverage->owner_id = $user->profile->id;
                $coverage->covered_id=$user->profile->id;
                $coverage->save();
            }
            $notification = \App\Notification::where('user_id',$usercheck->id)->first();
            if($notification){
            $notification->user_id =$user->id;
            $notification->save();
            }
            $individual->nric ='dtdelacct_'.$individual->nric;
            $individual->save();
            $usercheck->email = 'dtdelacct_'.$usercheck->email;
            $usercheck->save();
            $individual->mobile = 'dtdelacct_'.$individual->mobile;
            $individual->save();
        }
    }

        $token = MobileVerify::where(['token' => $request->token, 'user_id' => $user->id, 'is_used' => 0])->first();
        if (!$token) {
            return ['status' => 'error', 'message' => __('web/messages.email_verification_required')];
        }
        if(User::WithPendingPromoted()->where("email",$request->input('email'))->count() > 0) {
            return ['status' => 'error', 'message' => __('web/messages.unique_email')];
        }
        $user->email = $request->input('email');
        $user->save();
        
        $token->is_used = 1;
        $token->save();

        // add action
		$actions = [
			'methods'   => '',
			'old_email' => $oldEmail,
			'new_email' => $newEmail,
		];

		Auth()->user()->actions()->create([
			'user_id' => Auth()->id(),
			'type'       => Enum::ACTION_TYPE_AMENDMENT,
			'event'   => Enum::ACTION_EVENT_CHANGE_EMAIL,
			'actions' => $actions,
			'execute_on' => Carbon::now(),
			'status'  => Enum::ACTION_STATUS_EXECUTED
		]);

        return ['status' => 'success', 'data' => 'Email Changed !'];
    }

    /**
     * @api {post} api/change-mobile change mobile
     * @apiVersion 1.0.0
     * @apiName ChangeMobile
     * @apiGroup User
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {Number} mobile
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {String} data
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function changeMobile(Request $request)
    {
        $user = $request->user();
        //unAuthorized(empty($user));

		$oldMobile = $user->profile->mobile;
		$newMobile = $request->mobile;

        $token = MobileVerify::where(['token' => $request->token, 'user_id' => $user->id, 'is_used' => 0])->first();
        $mobile = str_replace("-","",$request->mobile);
        if (!$token)
            return ['status' => 'error', 'message' => __('web/messages.mobile_verification_required')];
        if(Individual::where("mobile",$mobile)->count() > 0)
            return ['status' => 'error', 'message' => __('web/messages.unique_mobile')];

        $user = $user->profile;
        $user->mobile = $mobile;
        $user->save();
        
        $token->is_used = 1;
        $token->save();

		// add action
		$actions = [
			'methods'   => '',
			'old_email' => $oldMobile,
			'new_email' => $newMobile,
		];

		Auth()->user()->actions()->create([
			'user_id' => Auth()->id(),
			'type'       => Enum::ACTION_TYPE_AMENDMENT,
			'event'   => Enum::ACTION_EVENT_CHANGE_MOBILE,
			'actions' => $actions,
			'status'  => Enum::ACTION_STATUS_EXECUTED
		]);

        return ['status' => 'success', 'data' => 'Mobile Changed !'];
    }

	/**
	 * @param User $user
	 * @param Individual|null $individual
	 * @param $beneficiary
	 */
	private function sendNotificationForNominee(User $user,?Individual $individual,$beneficiary): void
	{
		//$title = 'web/messages.nominee_title';
		//$text  = 'web/messages.nominee_text_notification';

        $beneficiary_1 = Beneficiary::where('email', $user->email)->orderBy("created_at","desc")->first()->individual_id;

        $beneficiary_2 = Individual::where("id",$beneficiary_1)->orderBy("created_at","desc")->first()->name;

        // $test = __('mobile.nominee_text_notification',['name'=>$user->profile->name]);

		// $user->sendNotification('mobile.nominee_title','mobile.nominee_text_notification',[
		// 	'translate_data' => ['name' => $user->profile->name],
		// 	'buttons' => [
		// 		['title' => 'ok'],
				
                /*['title' => 'reject','endpoint' => 'beneficiary/add','data' => [
					'individual_uuid' => $individual->uuid,
					'beneficiary_id'  => $beneficiary->id,
					'response'        => 'reject'
				]]*/
		// 	],
		// ]);

        $user->sendNotification('mobile.nominee_title','mobile.nominee_text_notification',[
			'translate_data' => ['name' => $beneficiary_2],
			'buttons' => [
				['title' => 'ok'],
				
                /*['title' => 'reject','endpoint' => 'beneficiary/add','data' => [
					'individual_uuid' => $individual->uuid,
					'beneficiary_id'  => $beneficiary->id,
					'response'        => 'reject'
				]]*/
			],
		]);

	}

   /* -------------------------- refresh access_token -------------------------- */
    public function refreshToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'refresh_token' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $baseUrl = url('');
            $response = Http::post("{$baseUrl}/oauth/token", [
                'refresh_token' => $request->refresh_token,
                'client_id' => env('AUTH_PASSPORT_CLIENT_ID'),
                'client_secret' => env('AUTH_PASSPORT_CLIENT_SECRET'),
                'grant_type' => 'refresh_token'
            ]);

            $result = json_decode($response->getBody(), true);
            if (!$response->ok()) {
                return response()->json(['error' => $result['error_description']], 401);
            }
            return response()->json($result);
        }
        catch(Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function login(Request $request,$socialAuthData = null)
    {
        if(!empty($socialAuthData)){
            $user = User::where("email",$socialAuthData->email ?? null)->first();
            if(empty($user))
                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.unauthorized')
                ]);

        }else {

            $request->validate([
                'email'         => 'required|string',
                'password'      => 'required|string',
                'remember_me'   => 'boolean'
            ]);

            $user = User::where(['email' => $request->input('email'), 'type' => 'individual'])->first();
            
        $length = 10;
        $data = ['1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $res = '';
        for ($i=0;$i<$length;$i++)
        {
            $res .= $data[rand(0,count($data)-1)];
        }
       
        if(!empty($user)){
        $indcheck =Referralcode::where('individual_id',$user->profile->id)->first();
        if(empty($indcheck)){
        $referralcode = new Referralcode;
        $refexist = false;
        $refcheck = Referralcode::where('referralcode',$res)->first();
        $referralcode->referralcode = $res;
        $referralcode->individual_id = Individual::where('user_id',$user->id)->first()->id;
        $referralcode->save();
            Individual::where('id',$referralcode->individual_id)->update(['referral_code' => $res]);
        }
     }

            //dev-510
            // Issue:  Beneficiary details not available in account page in mobile app
            // To fox: add has_beneficiary under data -> beneficiaries array object
            //Get the Beneficiaries i.e., nominees 
            //Deleted Beneficiries are ignored i.e, not included ->withTrashed()
            //ignore charity related beneficiaries
            $beneficiaries =  !empty($user->profile->nominees) ? 
                                $user->profile->nominees()->where("email","!=",'Charity@Deartime.com')->orderBy('id','desc')->get(): 
                                [];

            if(empty($user)){
                //check mobile
                $user = Individual::where("mobile",str_replace("-","",$request->input('email')))->first();
                //Restaurant::with('restaurantClass')->get();
                
                if(empty($user) || empty($user->user))
                    return response()->json([
                        'status'        => 'error',
                        'message'       => __('web/auth.failed')
                    ]);
                $user = $user->user;
               // dd($user->beneficiaries());

            }
            $allowedAttempts = Config::getValue('invalid_login_attempts') != null ? 
                                Config::getValue('invalid_login_attempts') : 3;
            //Password Security changes
            if ($user->invalid_loginattempt == $allowedAttempts) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.accountlocked')
                ]);
            }

            $credentials = request(['email', 'password']);
            $credentials['email'] = $user->email;
            if (!Auth::attempt($credentials)) {
                
                $user->invalid_loginattempt += 1;
                $user->save();

                return response()->json([
                    'status'    => 'error',
                    'message'   => __('web/messages.unauthorized')
                ]);
            } else {
                if ($user->invalid_loginattempt) {
                    $user->invalid_loginattempt = 0;
                    $user->save();
                }
            }
            $user = $request->user();
        }
        
        $baseUrl =url('');
        $response = Http::post("{$baseUrl}/oauth/token", [
            'username' => $credentials['email'],
            'password' => $request->password,
            'client_id' => env('AUTH_PASSPORT_CLIENT_ID'),
            'client_secret' => env('AUTH_PASSPORT_CLIENT_SECRET'),
            'grant_type' => 'password'
        ]);

        $oauthresult = json_decode($response->getBody(), true);
        if (!$response->ok()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $config = $user->userConfig($request);
        //dev-510 - add has_beneficiary under data -> config - > Bool value
        $config['data']['config']['data']['has_beneficiary'] = count($beneficiaries) ? true : false;
        $config['data']['has_beneficiary'] = count($beneficiaries) ? true : false;

        return response()->json([
            'status'    => !$config['data']['config']['access_allowed']?'error':'success',
            'message'   => !$config['data']['config']['access_allowed']? $config['data']['config']['server_message'] : __('web/messages.login_success'),
            'config'    =>$config,
            'data'      => [
                'token'         => !$config['data']['config']['access_allowed']?'' : $oauthresult['access_token'],
                'expires_at'    => !$config['data']['config']['access_allowed']?'' : Carbon::parse(now()->addSeconds($oauthresult['expires_in']))->toDateTimeString(),
                'refresh_token' => $oauthresult['refresh_token'],
                'user'          => !$config['data']['config']['access_allowed']?'' : $user,
                'next_page'     => !$config['data']['config']['access_allowed']?'' : $user->getNextPage(),                
                // 'beneficiaries' => $beneficiaries
                ]
        ]);
    }
    public function signupv2(Request $request)
    {
        $request->validate([
            'email'         => ['required','string','email',new UsersUnique],
            'marketing_email' => 'required',
            'password'      => 'required|string|min:8',
            'register_type' => 'required',
            'fullname'      => "required_if:register_type,individual|regex:/^[\pL\s\-\@\'\/]+$/u|max:80", // regex for letter and space between letter ex: jon ng is 6 character
            'mobile'        => 'required_if:register_type,individual|digits_between:10,12',
            'company_name'  => 'required_if:register_type,corporate',
            'referralcode' => '',
        ]);

        if ($request->register_type == 'individual') {
            $token = MobileVerify::where('token', $request->token)->first();
            if ($token == null || $token->mobile != $request->mobile) {
                return ['status' => 'error', 'message' => __('web/messages.mobile_verification_required')];
            }
        }

        $token = MobileVerify::where('token', $request->email_token)->first();
        if ($token == null || $token->mobile != $request->email) {
            return ['status' => 'error', 'message' => __('web/messages.email_verification_required')];
        }

        if($request->register_type == 'corporate')
            $user = User::onlyPendingPromoted()->where(['email' => $request->email, 'type' => 'corporate']);
        else
            $user = User::onlyPendingPromoted()->where(['email' => $request->email, 'type' => 'individual']);

        if($user->count() > 0){
            //user promoted and exists !
            $user = $user->first();
           // $profile =Individual::where('user_id',$user->id)->first();
           // if($profile){
            //    if($profile->mobile != $request->mobile){
            //        return ['status' => 'error', 'message' => 'mobile number and email mismatch'];
            //    }
          //  }
        }else{
            $user = new User();
        }
        
        if($request->referralcode != null){
          $referralcode = Referralcode::where('referralcode',$request->referralcode)->first();
           if(empty($referralcode)){
            return ['status' => 'error', 'message' => 'Refferal code is not valid'];
        }
    }
        if($user->from_referrer != null){
            $use = $user->from_referrer;
         }

		$user->email            = $request->email;
		$user->password         = bcrypt($request->password);
		$user->type             = $request->register_type;
		$user->marketing_email  = $request->marketing_email;
		$user->from_referrer    = $use ?? null;
		$user->activation_token = Str::uuid()->toString();
        $user->locale = $request->locale;
		$user->save();

        if($request->referralcode != null){
            $referralcode = Referralcode::where('referralcode',$request->referralcode)->first();
            $use = Individual::where('id',$referralcode->individual_id)->first()->user_id;
            $reff_name = Individual::where('id',$referralcode->individual_id)->first()->name;
            User::where('id',$user->id)->update(['from_referrer' => $use,'from_referrer_name' => $reff_name]);
    }

        if ($request->register_type == 'individual') {
            $individual = Individual::where("user_id",$user->id)->count();
            if($individual == 0) {
                $individual = new Individual([
                    'name'      => $request->fullname,
                    'mobile'    => $request->mobile,
                    'user_id'   => $user->id
                ]);
                $individual->save();
            }


         if($request->referralcode != null || $user->from_referrer != null) {
               $user_d = User::where('id',$use)->first();
                $user_d->sendNotification('mobile.ref_notif_title', 'mobile.ref_notif_body', [
                'translate_data' => ['name' => ucwords(strtolower($user->profile->name))],
                'buttons' => [
                    ['title' => 'ok'],
                  ]
             ]);
           }
        
            $length = 10;
            $data = ['1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
            $res = '';
            for ($i=0;$i<$length;$i++)
            {
                $res .= $data[rand(0,count($data)-1)];
            }

            $referralcode = new Referralcode;
            $refexist = false;
            $refcheck = Referralcode::where('referralcode',$res)->first();
            $referralcode->referralcode = $res;
            $referralcode->individual_id = Individual::where('user_id',$user->id)->first()->id;
            // $inref = Individual::where('$referralcode->referralcode',$res)->update();
          if($refcheck){
            $refexist = true;
            // $skipref = true;
             return ['status' => 'error', 'message' => 'Already exist ref code'];
          }
            $indexist = false;
            $indcheck =Referralcode::where('individual_id',$referralcode->individual_id)->first();
            if($indcheck){
                $indexist = true;
                // $skipref = true;
                 return ['status' => 'error', 'message' => 'Already exist individual id'];
            }
            $referralcode->save();
            Individual::where('id',$referralcode->individual_id)->update(['referral_code' => $res]);
            // check is nominee & send notification
			$beneficiaries = Beneficiary::where('email', $request->email)->get();
            if(!empty($beneficiaries)){
            	foreach ($beneficiaries as $beneficiary){
            		if($beneficiary->nationality == '135'){

						$this->sendNotificationForNominee($user,$individual,$beneficiary);
					}
				}
			}

        } elseif ($request->register_type == 'corporate') {
            $corporate = Company::where("user_id",$user->id)->count();
            if($corporate == 0) {
                $cr = new Company([
                    'name'      => $request->company_name,
                    'reg_no'    => $request->company_regno,
                    'user_id'   => $user->id

                ]);
                $cr->save();
            }
        }
        //$token = $user->createToken('Personal Access Token')->accessToken;
        $baseUrl = url('');
        $response = Http::post("{$baseUrl}/oauth/token", [
            'username' => $request->email,
            'password' => $request->password,
            'client_id' => env('AUTH_PASSPORT_CLIENT_ID'),
            'client_secret' => env('AUTH_PASSPORT_CLIENT_SECRET'),
            'grant_type' => 'password'
        ]);

        $oauthresult = json_decode($response->getBody(), true);
        if (!$response->ok()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('web/messages.register_successful'),
            'data' => [
                'token'         => $oauthresult['access_token'],
                'expires_at'    => Carbon::parse(now()->addSeconds($oauthresult['expires_in']))->toDateTimeString(),
                'refresh_token' => $oauthresult['refresh_token'],
                'user'      => $user
            ],
            'next_page' => 'complete_register_page'

        ], 200);
    }
}
