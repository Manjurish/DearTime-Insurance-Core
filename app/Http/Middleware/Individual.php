<?php     

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Individual
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

        //accessDenied(!Auth::user()->isIndividual());
        if(Auth::user()->active != 1){
            //unAuthorized();
        }
        return $next($request);
    }
}
