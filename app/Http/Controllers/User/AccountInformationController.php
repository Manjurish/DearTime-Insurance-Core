<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Api\MobileVerifyController;
use App\MobileVerify;
use App\Company;
use App\Individual;
use App\User;
use App\Rules\UniqueInModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mmeshkatian\Ariel\ActionContainer;
use Mmeshkatian\Ariel\FormBuilder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use App\Rules\UsersUnique;
class AccountInformationController extends Controller
{
    public function changePassword()
    {
        
        $form = new FormBuilder(true,new ActionContainer('userpanel.account.change-password.store'));
        $form->addField('old_password',__('web/account.old_password'))->setType('password')->required();
        $form->addField('new_password',__('web/account.new_password'))->setType('password')->required()->rules('min:8');
        $form->addField('new_password_confirmation',__('web/account.new_password_confirmation'))->setType('password')->required();

        return $form->render()->with('title', 'Change Password');
    }

    public function changePasswordStore(Request $request)
    {
        $user = auth()->user();
        if(!Hash::check($request->input('old_password'),$user->password))
            throw ValidationException::withMessages([
                'old_password'=>[__('web/account.old_password_match')]
            ]);
        $request->validate([
        'new_password'=>[
                'required',
                'string',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'confirmed'
            ],
        ],
        [
           'new_password.regex'=> __('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.')
        ]
        );
        $user = User::findOrfail($user->id);
        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        return redirect()->back()->with("success_alert",__('web/account.password_changed_success'));
    }

    public function changeEmail(Request $request)
    {
        $user = auth()->user();
        $form = new FormBuilder(true,new ActionContainer('userpanel.account.change-email.store'));
        $form->setTile(__('mobile.change_email'));

        $form->addField('old_email',__('web/account.old_email'))->setValue($user->email)->setType('read_only');
        $form->addField('new_email',__('web/account.new_email'))->required()->setType(!empty($request->input('token')) ? 'read_only' : 'text');
        if(!empty($request->input('token'))){
            $form->addField('verification_code',__('web/account.verification_code'));
        }
        $form->addField('token','')->setType('hide');

        return $form->render();
    }

    public function changeEmailStore(Request $request)
    {
        if(empty($request->input('token'))){
            //send verification code
            $this->validate($request,[
                'new_email' =>  'required|email',
            ]);
            $email = $request->input('new_email');

            $request->request->replace([
                'type'  =>  'email',
                'email' =>  $email
            ]);
            $verification = app(MobileVerifyController::class)->sendVerification($request);
            if($verification['status'] == 'error')
                throw ValidationException::withMessages([
                    'new_email' =>  $verification['message'],
                ]);

            $token = $verification['data']['id'];
            return redirect()->route('userpanel.account.change-email',['new_email'=>$email,'token'=>$token])->with('info_alert',__('web/auth.verification_code_desc_email'));
        }else{
            $verification_code = str_replace(" ","",$request->input('verification_code'));
            $token = $request->input('token');
            $this->validate($request,[
               'verification_code'  =>  'required',
               'token'              =>  'required',
            ]);

            $request->request->replace([
                'mobile_id'     =>  $token,
                'mobile_code'   =>  $verification_code
            ]);
            $verification = app(MobileVerifyController::class)->validateVerification($request);
            if($verification['status'] == 'error') {
                throw ValidationException::withMessages([
                    'verification_code' => $verification['message'],
                ]);
            }

            $verification_token = $verification['data']['token'];
            $verification_data  = MobileVerify::where("token",$verification_token)->first();
            $new_email = $verification_data->mobile ?? null;
            if(!empty($new_email)){
                $user = User::findOrfail(auth()->id());
                $user->email = $new_email;
                $user->save();
            }
            return  redirect()->route('userpanel.account.change-email')->with("success_alert",__('web/account.email_changed_success'));


        }
    }

    public function changeMobile(Request $request)
    {
        $user = auth()->user();
        $form = new FormBuilder(true,new ActionContainer('userpanel.account.change-mobile.store'));
        $form->setTile(__('mobile.change_mobile'));
        $form->addField('old_mobile',__('web/account.old_mobile'))->setValue($user->phone)->setType('read_only');
        $form->addField('new_mobile',__('web/account.new_mobile'))->required()->setType(!empty($request->input('token')) ? 'read_only' : 'text');
        if(!empty($request->input('token'))){
            $form->addField('verification_code',__('web/account.verification_code'));
        }
        $form->addField('token','')->setType('hide');
        $script = asset('js/account_information.js');
        return $form->render(null,$script);
    }

    public function changeMobileStore(Request $request)
    {
        if(empty($request->input('token'))){
            //send verification code
            $this->validate($request,[
                'new_mobile'    =>  'required',
            ]);
            $mobile = str_replace("-","",$request->input('new_mobile'));

            $request->request->replace([
                'type'      =>  'mobile',
                'mobile_no' =>  $mobile
            ]);
            $verification = app(MobileVerifyController::class)->sendVerification($request);
            if($verification['status'] == 'error') {
                throw ValidationException::withMessages([
                    'new_mobile' => $verification['message'],
                ]);
            }

            $token = $verification['data']['id'];
            return redirect()->route('userpanel.account.change-mobile',['new_mobile'=>$mobile,'token'=>$token])->with('info_alert',__('web/auth.verification_code_desc'));
        }else{
            $verification_code = str_replace(" ","",$request->input('verification_code'));
            $token = $request->input('token');
            $this->validate($request,[
                'verification_code' =>  'required',
                'token'             =>  'required',
            ]);

            $request->request->replace([
                'mobile_id'     =>  $token,
                'mobile_code'   =>  $verification_code
            ]);
            $verification = app(MobileVerifyController::class)->validateVerification($request);
            if($verification['status'] == 'error') {
                throw ValidationException::withMessages([
                    'verification_code' => $verification['message'],
                ]);
            }

            $verification_token = $verification['data']['token'];
            $verification_data = MobileVerify::where("token",$verification_token)->first();
            $new_mobile = $verification_data->mobile ?? null;
            if(!empty($new_mobile)){
                $user = User::findOrfail(auth()->id());
                $user = $user->profile;
                if(!empty($user)){
                    $user->mobile = $new_mobile;
                    $user->save();
                }

            }
            return  redirect()->route('userpanel.account.change-mobile')->with("success_alert",__('web/account.mobile_changed_success'));
        }
    }
    public function showRegistrationForm(Request $request)
    {
        #$user = auth()->user()->enable_user_registration;
        #echo "<pre>";print_r($user);
        if(auth()->user()->enable_user_registration != 1)
        {
            return redirect()->route('userpanel.dashboard.main');

        }

        $_token = $request->input('token');
        $email = null;
        $token = null;
        $name = null;
        if(!empty($_token)) {
            try {
                $mv = MobileVerify::find(decrypt($request->token));
                if (empty($mv))
                    return redirect()->route('register');
                $email = $mv->mobile;
                $token = $mv->token;
            }catch (\Exception $e){
                return redirect()->route('register');
            }
        }
        $uuid = $request->input('uuid');
        if(!empty($uuid)){
            try {
                $user = User::OnlyPendingPromoted()->where("uuid", decrypt($uuid))->first();
                if(empty($user))
                    throw new \Exception("email");
                $email = $user->email;
                $name = $user->profile->name;
            }catch (\Exception $e){
                return redirect()->route('register');
            }
        }
        $pid = $request->input('pid');

        return view('user.register.new-user',compact('email','token','name','pid'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:individual,corporate'],
            'mobile_number' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', new UsersUnique],
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ],[
           'password.regex'=> __('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.')
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $type = $data['type'];
        //check if promoted
        $user = User::onlyPendingPromoted()->where(["email" => $data['email'], 'type' => 'corporate']);
        if($user->count() > 0) {
            $user = $user->first();
            $promoted = true;
        }else
            $user = new User();


        $user->email = $data['email'];
        $user->type = $data['type'];
        $user->password = Hash::make($data['password']);
        $user->activation_token = Str::uuid()->toString();
        //promoter
        if(!empty($data['pid'])) {

            $pid = User::whereUuid(($data['pid']))->first();
            if(!empty($pid))
                $user->promoter_id = $pid->id;
        }
        $user->save();


        if($type == 'individual'){
            $individual = Individual::where("user_id",$user->id)->count();
            if($individual == 0) {
                $individual = new Individual();
                $individual->user_id = $user->id;
                $individual->name = $data['name'];
                $individual->mobile = str_replace("+60", "", $data['mobile_number']);
                $individual->save();
            }
        }else{
            $corporate = Company::where("user_id",$user->id)->count();
            if($corporate == 0) {
                $corporate = new Company();
                $corporate->user_id = $user->id;
                $corporate->name = $data['name'];
                $corporate->reg_no = str_replace("+60", "", $data['mobile_number']);
                $corporate->corporate_verified = 0;
                $corporate->save();
            }
        }

        return  $user;

    }
    public function storeUserData(Request $request)
    {
        if($request->input('type') == 'individual'){

            //validate code
            $token = MobileVerify::where('token', $request->input('mobile_token'))->first();

            if ($token == null) {
                $exp = ValidationException::withMessages([
                    'mobile' => ['Mobile Verificiation is required'],
                ]);
                throw $exp;
            }
        }

        $token = MobileVerify::where('token', $request->input('email_token'))->first();

        // if ($token == null) {
        //     $exp = ValidationException::withMessages([
        //         'email' => ['Email Verificiation is required'],
        //     ]);
        //     throw $exp;
        // }

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        //$this->guard()->login($user);
        return  redirect()->route('userpanel.dashboard.main')->with("success_alert",__('web/account.user_registration_success'));
        //return $this->registered($request, $user)
        //    ?: redirect($this->redirectPath());
    }

}
