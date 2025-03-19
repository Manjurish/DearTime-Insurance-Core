<?php     

namespace App\Http\Middleware;

use Closure;

class Clinic
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
        //accessDenied(auth()->user()->isIndividual());
        //accessDenied(empty(auth()->user()->profile) || !auth()->user()->profile->isClinic());

        return $next($request);
    }
}
