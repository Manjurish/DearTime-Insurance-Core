<?php     

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Spatie\TestTime\TestTime;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        parent::register();
        //
        if(request()->is("api*"))
            request()->headers->set('Accept', 'application/json');

        //if(request()->is("internal*")){
            if(request()->is("ops*")){
            Config::set("custom.custom.mainLayoutType","vertical");
            Config::set("static.area","Admin");
        }else{
            Config::set("static.area","User");
        }

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }
}
