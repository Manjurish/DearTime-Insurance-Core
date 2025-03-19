<?php     

namespace App\Http\Middleware;

use App\User;
use Closure;

class Localization
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

        if(session()->has('locale'))
            $locale = session()->get('locale') ?? 'en';
        elseif(auth()->check())
            $locale = auth()->user()->locale ?? 'en';
        else
            $locale = 'en';

        if(!empty($request->input('set_locale')))
            $locale = $request->input('set_locale');

        $availableLocales = ['en','bm','ch'];
        if(!in_array($locale,$availableLocales))
            $locale = 'en';  
        session()->put('locale',$locale);
        if(auth()->check()){
            $user = User::find(auth()->id());
            //To handle Null Check when the user is not loaded - Page keep on loading issue in Corp User Dbd
            if ($user != null) {
                $user->locale = $locale;
                $user->save();
            }
        }
        app()->setLocale($locale);
        if(!empty($request->input('set_locale')))
            return redirect(url()->current());

        return $next($request);
    }
}
