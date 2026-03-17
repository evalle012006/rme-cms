<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthenticationController extends BaseController
{
    /**
     * AuthenticationController constructor.
     */
    public function __construct()
    {
    }

    /**
     * AuthenticationController Authentication Method
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if (!Auth::attempt($credentials)) {
            return $this->formatFailureResponse('Unauthorized User');
        }

        $user = $request->user();

        if ($user->hasRole('admin')) {

            $tokenResult = $user->createToken('Access Token');
            $token = $tokenResult->token;
            if ($request->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            }
            $token->save();

            $data = [
                'access_token' => $tokenResult->accessToken,
                'user' => $user
            ];
            return $this->formatSuccessResponse('success', $data);
        } else {
            return $this->formatFailureResponse('Unauthorized User');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->formatSuccessResponse('Logged Out Successfully ', 'success');
    }

    public function resetPassword(Request $request)
    {
        $response = $this->broker()->sendResetLink(['email' => auth()->user()->email]);
        $isEmailSent = $response == Password::RESET_LINK_SENT;
        if ($isEmailSent) {
            $tokenRepository = app('Laravel\Passport\TokenRepository');
            $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

            $tokenId = $this->getTokenId($request);

            $tokenRepository->revokeAccessToken($tokenId);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
        }

        return $this->formatSuccessResponse('Password Reset', ['sent' => $isEmailSent]);
    }

    public function broker()
    {
        return Password::broker();
    }
}
