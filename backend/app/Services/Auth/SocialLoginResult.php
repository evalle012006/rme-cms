<?php

namespace App\Services\Auth;

use App\Models\User;

/**
 * Value object returned by SocialAccountService::resolve() so that callers can
 * distinguish "we have a user, log them in" from "we refused for a specific
 * reason" without relying on exceptions for control flow.
 */
class SocialLoginResult
{
    /** @var User|null */
    public $user;

    /** @var string|null Machine-readable error code, e.g. "email_required". */
    public $errorCode;

    private function __construct($user, $errorCode)
    {
        $this->user = $user;
        $this->errorCode = $errorCode;
    }

    public static function success(User $user): self
    {
        return new self($user, null);
    }

    public static function error(string $code): self
    {
        return new self(null, $code);
    }

    public function ok(): bool
    {
        return $this->user !== null;
    }
}
