<?php     

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\InternalUser;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
    protected $maxAttempts = 3;
    protected $decayMinutes = (365 * 24 * 60);

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $guard = 'internal_users';

    protected function guard()
    {
        return \Auth::guard('internal_users');
    }
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['active'] = '1';
        return $request->only($this->username(), 'password');
    }

    protected function attemptLogin(Request $request)
    {

        $username = $request->input($this->username());
        $username = InternalUser::where($this->username(),$username);

        if($username->count() > 0){
            $username = $username->get()->first();
            if($username->active != 1){
                throw ValidationException::withMessages([
                    $this->username() => [trans('web/auth.banned')],
                ]);
            }

            if($username->is_locked == 1){
                throw ValidationException::withMessages([
                    $this->username() => ['Your account has been locked. Use forgot password to unlock your account.'],
                ]);
            }
        }

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

/************************ Pentest - Session End (After Logout) **********************/

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
     public function logout(Request $request)
        {
            Auth::logout();
    
            $request->session()->invalidate();
    
            $request->session()->regenerateToken();
    
            return redirect('/ops/login');
        }
        
/************************ Pentest - Session End (After Logout) **********************/

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected function redirectTo()
    {
       return route('admin.dashboard.main');
    }
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    protected function sendLoginResponse(Request $request)
    {
        $previous_session = auth('internal_users')->user()->session_id;
        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
            
        }
        
        auth('internal_users')->user()->session_id = Session::getId();
        auth('internal_users')->user()->is_locked  = 0;
        auth('internal_users')->user()->save();
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->route('admin.dashboard.main');           
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
        $username = $request->input($this->username());
        InternalUser::where($this->username(),$username)->update(['is_locked' => 1]);  
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
