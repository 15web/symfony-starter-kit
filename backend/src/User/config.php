<?php

declare(strict_types=1);

namespace App\User;

use App\User\Domain\User;
use App\User\Http\ApiTokenAuthenticator;
use App\User\Http\AuthenticationEntryPoint;
use App\User\Http\JsonLoginAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('app_user_provider')
        ->entity()
        ->class(User::class)
        ->property('userEmail.value');

    $security->enableAuthenticatorManager(true);
    $security->firewall('main')
        ->customAuthenticators([ApiTokenAuthenticator::class, JsonLoginAuthenticator::class])
        ->entryPoint(AuthenticationEntryPoint::class);
};
