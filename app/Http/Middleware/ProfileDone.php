<?php     

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProfileDone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $route = $request->route()->getName();

        if(auth()->user()->active != 1) {
            auth()->logout();
            $validator = ValidationException::withMessages([
                'email'=>'Your Account has been disabled !'
            ])->errors();
            return redirect()->route('login')->withErrors($validator);

        }

        if(!auth()->user()->ProfileDone && ($route != 'userpanel.dashboard.profile' && $route != 'userpanel.dashboard.profile.save' && $route != 'userpanel.dashboard.profile.doc' && $route != 'userpanel.dashboard.profile.doc.remove')) {

            return redirect()->route('userpanel.dashboard.profile')->with("info_alert", "Please Complete Your profile information First.");
        }
        return $next($request);
    }
}
