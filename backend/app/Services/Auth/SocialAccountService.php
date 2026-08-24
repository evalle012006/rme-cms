<?php

namespace App\Services\Auth;

use App\Models\SocialFacebookAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * Resolves a Socialite provider user into an application user.
 *
 * The table `social_facebook_accounts` predates Google support and is kept for
 * backward compatibility (see the accompanying migration for structural fixes).
 * This service uses it for both providers.
 */
class SocialAccountService
{
    /**
     * Find or create the local User for a given provider profile.
     *
     * Rules:
     *   1. If we already have a `(provider, provider_user_id)` row, return its user.
     *   2. If the provider did not return an email, refuse (error_code = email_required).
     *   3. If the email belongs to an existing user, only link the social account
     *      when both sides have verified the email; otherwise refuse
     *      (error_code = email_taken) so the user signs in with a password first.
     *      This closes the "hijack via unverified provider email" hole in the
     *      previous implementation.
     *   4. Otherwise, if $allowCreate is true, create a fresh user with
     *      `email_verified_at = now()` (verified by the provider), status = 1,
     *      role reviewer. If $allowCreate is false (used by the CMS admin flow,
     *      which must never auto-provision), refuse with error_code = no_account.
     */
    public function resolve(ProviderUser $providerUser, string $provider, bool $allowCreate = true): SocialLoginResult
    {
        $providerUserId = (string) $providerUser->getId();

        $account = SocialFacebookAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($account && $account->user) {
            return SocialLoginResult::success($account->user);
        }

        $email = $providerUser->getEmail();
        if (empty($email)) {
            return SocialLoginResult::error('email_required');
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            $safeToLink = $this->providerEmailVerified($providerUser, $provider)
                && $existing->email_verified_at !== null;

            if (!$safeToLink) {
                return SocialLoginResult::error('email_taken');
            }

            return DB::transaction(function () use ($existing, $providerUserId, $provider, $email) {
                $this->linkSocial($existing, $providerUserId, $provider, $email);
                return SocialLoginResult::success($existing);
            });
        }

        if (!$allowCreate) {
            return SocialLoginResult::error('no_account');
        }

        [$firstName, $lastName] = $this->splitName($providerUser, $provider, $email);

        return DB::transaction(function () use ($providerUserId, $provider, $email, $firstName, $lastName) {
            $user = User::create([
                'email'             => $email,
                'firstname'         => $firstName,
                'lastname'          => $lastName,
                'status'            => 1,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('reviewer');
            $this->linkSocial($user, $providerUserId, $provider, $email);
            return SocialLoginResult::success($user);
        });
    }

    protected function linkSocial(User $user, string $providerUserId, string $provider, ?string $email): void
    {
        $account = new SocialFacebookAccount([
            'provider'         => $provider,
            'provider_user_id' => $providerUserId,
            'provider_email'   => $email,
        ]);
        $account->user()->associate($user);
        $account->save();
    }

    /**
     * Google exposes `email_verified` (OIDC) or `verified_email` (older userinfo).
     * Facebook does not expose an equivalent flag we can trust, so for the
     * purposes of "safe to link to an existing account" we treat it as
     * unverified — the user must sign in with their password to link.
     */
    protected function providerEmailVerified(ProviderUser $providerUser, string $provider): bool
    {
        if ($provider !== 'google') {
            return false;
        }
        $raw = (array) $providerUser->user;
        if (array_key_exists('email_verified', $raw)) {
            return (bool) $raw['email_verified'];
        }
        if (array_key_exists('verified_email', $raw)) {
            return (bool) $raw['verified_email'];
        }
        return false;
    }

    /**
     * Facebook exposes first_name / last_name directly. Google gives a single
     * display name we have to split, so we defend against single-word names and
     * missing values.
     *
     * @return array{0:string,1:string}
     */
    protected function splitName(ProviderUser $providerUser, string $provider, string $email): array
    {
        if ($provider === 'facebook') {
            $raw = (array) $providerUser->user;
            $first = $raw['first_name'] ?? null;
            $last  = $raw['last_name'] ?? null;
            if (!empty($first)) {
                return [$first, $last ?: ''];
            }
        }

        $name = trim((string) $providerUser->getName());
        if ($name === '') {
            $name = Str::before($email, '@') ?: 'User';
        }

        if (strpos($name, ' ') === false) {
            return [$name, ''];
        }

        $first = Str::before($name, ' ');
        $last  = trim(Str::after($name, ' '));
        return [$first, $last];
    }
}
