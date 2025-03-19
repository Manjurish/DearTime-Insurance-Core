<?php     

namespace App\Http\Controllers\Partner\Auth;

use App\Company;
use App\Individual;
use App\MobileVerify;
use App\Partner;
use App\PartnerUser;
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
        return view('partner.auth.register');
    }

    public function redirectTo()
    {
        return route('partner.dashboard.main');
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:partner');
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
            'name' => ['required', 'string', 'max:255','unique:partners,name'],
            'type' => ['required', 'string', 'in:individual,corporate'],
            'mobile' => ['required','unique:partners,reg_no'],
            'email' => ['required', 'string', 'email', 'max:255','unique:partner_users,username'],
            'password' => ['required', 'string', 'min:8'],
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

        $partner = new Partner();
        $partner->code = 'DP'.rand(1000,9999);
        $partner->name = $data['name'];
        $partner->reg_no = $data['mobile'];
        $partner->corporate_verified = 0;
        $partner->save();

        $user = new PartnerUser();
        $user->partner_id = $partner->id;
        $user->name = $data['name'];
        $user->username = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->active = '1';
        $user->activation_token = Str::uuid()->toString();
        $user->save();


        return  $user;

    }
    protected function guard()
    {
        return auth()->guard('partner');
    }

    public function register(Request $request)
    {

        $token = MobileVerify::where('token', $request->input('email_token'))->first();

        if ($token == null) {
            $exp = ValidationException::withMessages([
                'email' => ['Email Verificiation is required'],
            ]);
            throw $exp;
        }
        

        $this->validator($request->all())->validate();

        // Given password
        // $password = 'user-input-pass';
        $password = $request->input('password');
        //exit;

        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        //echo "!$uppercase || !$lowercase || !$number || !$specialChars";
        //exit;
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            throw ValidationException::withMessages(['password' => __('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.')]);
				
            // throw new \Exception('');
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
