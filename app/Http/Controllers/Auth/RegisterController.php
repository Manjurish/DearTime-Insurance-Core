<?php     

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Individual;
use App\MobileVerify;
use App\Rules\UniqueInModel;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;


    public function showRegistrationForm(Request $request)
    {
        return redirect()->route('partner.login');
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

        return view('auth.register',compact('email','token','name','pid'));
    }

    public function redirectTo()
    {
        return route('userpanel.dashboard.main');
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
            'email' => ['required', 'string', 'email', 'max:255', new UniqueInModel(User::class,'email')],
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
        $user = User::onlyPendingPromoted()->where("email",$data['email']);
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
    public function register(Request $request)
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

        if ($token == null) {
            $exp = ValidationException::withMessages([
                'email' => ['Email Verificiation is required'],
            ]);
            throw $exp;
        }

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
