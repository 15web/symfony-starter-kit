<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\User\SignIn\Http\Authenticator\ApiTokenAuthenticator;
use App\User\SignIn\Http\Authenticator\AuthenticationEntryPoint;
use App\User\SignIn\Http\Authenticator\JsonLoginAuthenticator;
use App\User\SignIn\Http\Authenticator\UserChecker;
use App\User\SignUp\Domain\User;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('app_user_provider')
        ->entity()
        ->class(User::class)
        ->property('userEmail.value');

    $security->enableAuthenticatorManager(true);
    $security->firewall('main')
        ->customAuthenticators([ApiTokenAuthenticator::class, JsonLoginAuthenticator::class])
        ->entryPoint(AuthenticationEntryPoint::class)
        ->userChecker(UserChecker::class);
};
