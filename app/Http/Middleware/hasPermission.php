<?php     

namespace App\Http\Middleware;

use App\Helpers;
use App\Helpers\Helper;
use Closure;

class hasPermission
{

    public function handle($request, Closure $next)
    {
        $route = $request->route()->getName();
        if(Helpers::hasPermission($route)){
            return $next($request);
        }
        //accessDenied();

    }
}
