<?php

namespace App\Support;

use App\Models\User;
use App\Rules\PasswordDoesNotContainAppName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class PasswordDefaults
{
    public const MIN_LENGTH_WITHOUT_TWO_FACTOR = 15;

    public const MIN_LENGTH_WITH_TWO_FACTOR = 8;

    /**
     * Register the application's default password validation rules.
     */
    public static function configure(): void
    {
        Password::defaults(fn (): Password => static::rule());
    }

    /**
     * Build the password rule for the given user, or resolve one from the current request.
     */
    public static function rule(?User $user = null): Password
    {
        $user ??= static::resolveUser();

        $minLength = ($user?->hasEnabledTwoFactorAuthentication() ?? false)
            ? static::MIN_LENGTH_WITH_TWO_FACTOR
            : static::MIN_LENGTH_WITHOUT_TWO_FACTOR;

        return Password::min($minLength)
            ->uncompromised()
            ->rules([new PasswordDoesNotContainAppName]);
    }

    /**
     * Resolve the user whose password rules should apply.
     */
    public static function resolveUser(): ?User
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return $user;
        }

        $email = request()->input('email');

        if (! is_string($email) || $email === '') {
            return null;
        }

        return User::query()->where('email', $email)->first();
    }
}
