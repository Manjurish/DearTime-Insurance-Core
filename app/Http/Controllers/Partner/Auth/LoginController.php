<?php     

namespace App\Http\Controllers\Partner\Auth;

use App\Http\Controllers\Controller;
use App\Partner;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function showLoginForm()
    {
        return view('partner.auth.login');
    }


    /**
     * Where to redirect users after login.
     *
     * @var string
     */

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
        $this->middleware('guest:partner')->except('logout');
    }

    public function username()
    {
        return "username";
    }
    protected function guard()
    {
        return auth()->guard('partner');
    }
    protected function credentials(Request $request)
    {
        $partner = Partner::where("code",$request->input('domain'))->first();
        if(empty($partner))
            throw ValidationException::withMessages([
                'domain'=>'Domain is invalid',
            ]);
        return array_merge($request->only($this->username(), 'password'),['partner_id'=>$partner->id]);
    }
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->route('partner.dashboard.main');
    }
}
