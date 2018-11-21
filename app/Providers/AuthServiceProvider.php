<?php

namespace Melatop\Providers;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Carbon\Carbon;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'Melatop\Model' => 'Melatop\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes(function ($router) {
            $router->forAccessTokens();
            $router->forPersonalAccessTokens();
            $router->forTransientTokens();
        });

        Passport::tokensExpireIn(Carbon::now()->addHours(12));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(10));
    }
}
