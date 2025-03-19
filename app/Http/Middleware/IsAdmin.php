<?php     

namespace App\Http\Middleware;

use Closure;

class IsAdmin
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
        if (!\Auth::guard('internal_users')->check() && !in_array($request->route()->getName(),array_diff(config('static.allowed_routes'),['admin.dashboard.main']))) {
            return redirect()->route('admin.auth.login');
        }

        return $next($request);
    }
}
