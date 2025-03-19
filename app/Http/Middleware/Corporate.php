<?php     

namespace App\Http\Middleware;

use Auth;
use Closure;

class Corporate
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
        ////accessDenied(Auth::user()->isIndividual());
        return $next($request);
    }
}
