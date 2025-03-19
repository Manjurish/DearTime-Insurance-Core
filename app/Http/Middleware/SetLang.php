<?php     

namespace App\Http\Middleware;

use App\User;
use Closure;

class SetLang
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
        $lang = $request->header('Accept-Language');
        if(!in_array($lang,['en','bm','ch']))
            $lang = 'en';

        app()->setLocale($lang);
        return $next($request);
    }
}
