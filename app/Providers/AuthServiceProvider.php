<?php     

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Auth\Notifications\ResetPassword;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // Grant "Super Admin" users all permissions (assuming they are verified using can() and other gate-related functions):
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('SuperAdmin')) {
                return true;
            }
        });
        if (!$this->app->routesAreCached()) {
            Passport::routes(); 
        }
        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addMinutes(15));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(15));

        ResetPassword::createUrlUsing(function ($user, string $token) {
            //echo "<pre>";print_r($user);die;
            //return 'https://example.com/reset-password?token='.$token;
            if(str_contains(url()->current(), '/ops/password'))
            {
                //echo 1;die;
               return route('admin.password.reset', ['token' => $token,'email'=>$user->email]);
            }else
            {
                //echo 2;die;
               return route('partner.password.reset', ['token' => $token,'email'=>$user->email]);
            }
        });

    }
}
