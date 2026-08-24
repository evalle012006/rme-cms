<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * OAuth entry points used by the React CMS admin login. Distinct from the
 * public-site LoginController because it:
 *   - uses a different redirect URI (/cms/auth/{provider}/callback) so we can
 *     tell the two flows apart in the OAuth consoles and in logs;
 *   - refuses to auto-provision users (a CMS admin must already exist);
 *   - requires the resolved user to have the `admin` role;
 *   - terminates by issuing a Passport bearer token and redirecting the
 *     browser back to the React CMS with the token in the URL fragment (so it
 *     is never sent to any server as a query string).
 */
class CmsSocialLoginController extends Controller
{
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'], true)) {
            abort(404);
        }

        $driver = Socialite::driver($provider)->redirectUrl($this->callbackUrl($provider));

        if ($provider === 'google') {
            $driver->scopes(['openid', 'profile', 'email']);
        } else {
            $driver->scopes(['email']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider, SocialAccountService $service)
    {
        if (!in_array($provider, ['google', 'facebook'], true)) {
            abort(404);
        }

        if ($request->has('error')) {
            $code = $request->get('error') === 'access_denied' ? 'cancelled' : 'provider_error';
            Log::warning('CMS social login provider error', [
                'provider' => $provider,
                'error'    => $request->get('error'),
                'desc'     => $request->get('error_description'),
            ]);
            return $this->redirectToCms(null, $code);
        }

        try {
            $providerUser = $this->fetchProviderUser($provider);
        } catch (InvalidStateException $e) {
            Log::warning('CMS social login invalid state', ['provider' => $provider]);
            return $this->redirectToCms(null, 'invalid_state');
        } catch (\Throwable $e) {
            Log::error('CMS social login provider fetch failed', [
                'provider' => $provider,
                'message'  => $e->getMessage(),
            ]);
            return $this->redirectToCms(null, 'provider_error');
        }

        // CMS login must never auto-provision users — pass allowCreate=false.
        $result = $service->resolve($providerUser, $provider, false);

        if (!$result->ok()) {
            Log::info('CMS social login refused', [
                'provider' => $provider,
                'code'     => $result->errorCode,
                'email'    => $providerUser->getEmail(),
            ]);
            return $this->redirectToCms(null, $result->errorCode);
        }

        $user = $result->user;

        if (!$user->hasRole('admin')) {
            Log::info('CMS social login rejected: not admin', [
                'provider' => $provider,
                'user_id'  => $user->id,
                'email'    => $user->email,
            ]);
            return $this->redirectToCms(null, 'not_admin');
        }

        $tokenResult = $user->createToken('CMS Social');

        // Load roles so the CMS auth store gets the same shape as the password
        // login response.
        $user->load('roles');

        return $this->redirectToCms([
            'access_token' => $tokenResult->accessToken,
            'user'         => $user->toArray(),
        ], null);
    }

    private function fetchProviderUser(string $provider)
    {
        $driver = Socialite::driver($provider)->redirectUrl($this->callbackUrl($provider));

        if ($provider === 'facebook') {
            $driver->fields(['name', 'first_name', 'last_name', 'email']);
        }

        return $driver->user();
    }

    private function callbackUrl(string $provider): string
    {
        return route('cms.social.callback', ['provider' => $provider]);
    }

    /**
     * Redirect back into the React CMS.
     *
     * On success the token + user payload is placed in the URL fragment so it
     * is never sent to any server as a query string or logged by upstream
     * proxies. On failure we use a normal query string carrying only the error
     * code — no PII involved.
     */
    private function redirectToCms(?array $payload, ?string $errorCode)
    {
        $cmsUrl = rtrim(config('services.cms.url') ?: config('app.url'), '/');

        if ($errorCode) {
            return redirect()->away($cmsUrl . '/login?social_error=' . rawurlencode($errorCode));
        }

        $fragment = 'access_token=' . rawurlencode($payload['access_token'])
            . '&user=' . rawurlencode(json_encode($payload['user']));

        return redirect()->away($cmsUrl . '/auth/social-callback#' . $fragment);
    }
}
