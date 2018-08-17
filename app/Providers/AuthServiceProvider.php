<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        /*Route::group(['middleware' => 'FeeCheck'], function () {
            Passport::routes(); // <-- Replace this with your own version
        });*/
        Passport::tokensExpireIn(Carbon::now()->addMinutes(300));
        //只要refresh过期时间>token过期时间>storage缓存时间>refresh时间
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(1));
    }
}
