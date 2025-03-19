<?php     

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // get all data from menu.json file
        if(auth('internal_users')->check() && request()->is("internal/*"))
            $menuJson = file_get_contents(base_path('resources/json/menu.json'));
        elseif(auth()->check()) {
            $menuJson = file_get_contents(base_path('resources/json/menu.json'));
        }else
            $menuJson = json_encode([]);

        $menuJson = file_get_contents(base_path('resources/json/menu.json'));
        $menuData = json_decode($menuJson);

        // Share all menuData to all the views
        \View::share('menuData', $menuData);
    }
}
