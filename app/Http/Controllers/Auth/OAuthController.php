<?php     

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MobileVerify;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function __construct()
    {
        config()->set('services.facebook.redirect',route('oauth.callback','facebook'));
        config()->set('services.google.redirect',route('oauth.callback','google'));
    }
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    public function callback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
            $email = $user->getEmail();
            if(empty($email))
                throw new \Exception("email");

            $user = User::where("email",$email);
            if($user->count() == 0){
                //register
                $mv = new MobileVerify();
                $mv->mobile = $email;
                $mv->code = 00000;
                $mv->verified = false;
                $mv->expiry = Carbon::now()->addMinutes(5);
                $mv->token = Str::uuid()->toString();
                $mv->save();

                return redirect()->route('register',['token'=>encrypt($mv->id)]);

            }else{
                auth()->login($user->first());
                return redirect()->route('userpanel.dashboard.main');
            }

        }catch (\Exception $e){
            return redirect('login');
        }
    }
}
