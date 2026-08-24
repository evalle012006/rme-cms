<?php

namespace App\Services;

use App\Services\Auth\SocialAccountService;
use Laravel\Socialite\Contracts\User as ProviderUser;
use RuntimeException;

/**
 * Backward-compatibility shim. New code should use App\Services\Auth\SocialAccountService.
 *
 * The old signature returned a User or threw; new code should call resolve()
 * and inspect SocialLoginResult so it can react to specific error codes.
 */
class SocialFacebookAccountService extends SocialAccountService
{
    public function createOrGetUser(ProviderUser $providerUser, $provider)
    {
        $result = $this->resolve($providerUser, (string) $provider);

        if (!$result->ok()) {
            throw new RuntimeException('social_login_failed:' . $result->errorCode);
        }

        return $result->user;
    }
}
