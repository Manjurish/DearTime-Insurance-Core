<?php     

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Individual;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    protected $maxAttempts  = 3;
    protected $decayMinutes = (365 * 24 * 60);

    public $type;

    public function showLoginForm()
    {
        return abort(404);
        //return view('auth.login');
        //return redirect('/partner/login');
        //return redirect()->route('partner.login');
    }
    
    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected function credentials(Request $request)
    {
		$email = $request->input('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $individual = Individual::where("mobile",$email)->first();
            if(!empty($individual)){
            	$this->type = 'mobile';
                return [
                    'email' => $individual->user->email ?? '',
                    'password' => $request->input('password')
                ];
            }
        }
		$this->type = 'email';
        return [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'type' => 'corporate'
        ];
    }

	protected function attemptLogin(Request $request)
	{
		if($this->type == 'mobile'){
			$individual = Individual::where("mobile",$request->email)->first();
			if(!empty($individual)){
				if($individual->user->type == Enum::USER_TYPE_INDIVIDUAL){
					Auth::logout();
					Session::flash('error', __('web/messages.denied_login_individual'));
					return redirect()->route('login');
				}
			}
		}else{
			$user = User::where(["email" => $request->email, 'type' => 'corporate'])->first();
			if(!empty($user)){
                if($user->is_locked == 1){
                    throw ValidationException::withMessages([
                        'email' => ['Your account has been locked. Use forgot password to unlock your account.'],
                    ]);
                }
				if($user->type == Enum::USER_TYPE_INDIVIDUAL){
					Auth::logout();
					Session::flash('error', __('web/messages.denied_login_individual'));
					return redirect()->route('login');
				}
			}
		}
		return $this->guard()->attempt(
			$this->credentials($request), $request->filled('login')
		);
	}

    public function redirectTo()
    {
		return route('userpanel.dashboard.main');
    }

    /**
     *
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //echo config('app.url');die;
        $this->middleware('guest')->except('logout');
    }
    
    /**
    * Send the response after the user was authenticated.
    * Remove the other sessions of this user
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    protected function sendLoginResponse(Request $request)
    {
        if(isset(Auth::user()->session_id)){
            $previous_session = Auth::User()->session_id;
            if ($previous_session) {
                Session::getHandler()->destroy($previous_session);
            }
            
            Auth::user()->session_id = Session::getId();
            Auth::user()->is_locked  = 0;
            Auth::user()->save();
        }
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    public function checkLogin()
    {
        
        //$this->middleware('guest')->except('logout');
        return view('auth.login');
        //return redirect('/partner/login');
    }
    public function checkpartner()
    {
        return redirect()->route('partner.login');
        //$this->middleware('guest')->except('logout');
        //return view('auth.login');
        //return redirect('/partner/login');
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $username = $request->input('email');
        User::where('email',$username)->update(['is_locked' => 1]);  
        $this->clearLoginAttempts($request);      
        throw ValidationException::withMessages([
            'email' => ['Your account has been locked. Use forgot password to unlock your account.'],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);                
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $attempts_count = $this->limiter()->attempts($this->throttleKey($request)); // number of attempts performed
        $remaining = $this->maxAttempts - $attempts_count;

        if($remaining <= 0)
        {
            throw ValidationException::withMessages([
                'email' => ['Your account has been locked. Use forgot password to unlock your account.'],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        throw ValidationException::withMessages([
            'email' => [trans('web/auth.failed') . ". You have only {$remaining} attempt left."],
        ]);
    }
}
