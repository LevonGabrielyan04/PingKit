<?php

use App\Models\User;
use App\Support\PasswordDefaults;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Support\Facades\Validator;

function makeUserWithTwoFactor(): User
{
    $user = new User;
    $user->two_factor_secret = encrypt('secret');
    $user->two_factor_confirmed_at = now();

    return $user;
}

test('password requires at least fifteen characters when two factor is not enabled', function () {
    $validator = Validator::make(
        ['password' => 'ShortPass123!'],
        ['password' => ['required', PasswordDefaults::rule()]],
    );

    expect($validator->fails())->toBeTrue();
});

test('password allows eight characters when two factor is enabled', function () {
    $validator = Validator::make(
        ['password' => validPasswordForTwoFactorUser()],
        ['password' => ['required', PasswordDefaults::rule(makeUserWithTwoFactor())]],
    );

    expect($validator->passes())->toBeTrue();
});

test('password rejects values that contain the application name', function () {
    config(['app.name' => 'PingKit']);

    $validator = Validator::make(
        ['password' => 'MyPingKitPassword123'],
        ['password' => ['required', PasswordDefaults::rule()]],
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('application name');
});

test('password rejects compromised passwords', function () {
    $this->app->instance(
        UncompromisedVerifier::class,
        new class implements UncompromisedVerifier
        {
            public function verify($data): bool
            {
                return false;
            }
        },
    );

    $validator = Validator::make(
        ['password' => validPassword()],
        ['password' => ['required', PasswordDefaults::rule()]],
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('password reset uses two factor minimum length for users with two factor enabled', function () {
    $validator = Validator::make(
        ['password' => validPasswordForTwoFactorUser()],
        ['password' => ['required', PasswordDefaults::rule(makeUserWithTwoFactor())]],
    );

    expect($validator->passes())->toBeTrue();
});

test('password reset uses stricter minimum length for users without two factor enabled', function () {
    $validator = Validator::make(
        ['password' => validPasswordForTwoFactorUser()],
        ['password' => ['required', PasswordDefaults::rule(new User)]],
    );

    expect($validator->fails())->toBeTrue();
});
