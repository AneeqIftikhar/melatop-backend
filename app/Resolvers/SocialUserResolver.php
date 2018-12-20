<?php

namespace Melatop\Resolvers;

use Hivokas\LaravelPassportSocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Melatop\User;
class SocialUserResolver implements SocialUserResolverInterface
{
    /**
     * Resolve user by provider credentials.
     *
     * @param string $provider
     * @param string $accessToken
     *
     * @return Authenticatable|null
     */
    public function resolveUserByProviderCredentials(string $provider, string $accessToken): ?Authenticatable
    {
        // Return the user that corresponds to provided credentials.
        // If the credentials are invalid, then return NULL.
        if($provider=='facebook')
        {

        }
    }
}