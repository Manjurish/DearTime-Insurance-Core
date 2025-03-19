<?php     

namespace App\Http\Middleware;

use Closure;

class IsPartner
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
        $routes = [
            'partner.auth.login',
            'partner.auth.logout',
            'partner.auth.register',
        ];
        if (!auth()->guard('partner')->check() && !in_array($request->route()->getName(),$routes)) {
            return redirect()->route('partner.auth.login');
        }

        return $next($request);
    }
}
