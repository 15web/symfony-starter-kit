<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Security\Authenticator\ApiToken\ApiTokenAuthenticator;
use App\Infrastructure\Security\Authenticator\AuthenticationEntryPoint;
use App\Infrastructure\Security\Authenticator\JsonLoginAuthenticator;
use App\Infrastructure\Security\UserProvider\SecurityUserProvider;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('app_user_provider')
        ->id(SecurityUserProvider::class);

    $security->enableAuthenticatorManager(true);
    $security->firewall('main')
        ->customAuthenticators([ApiTokenAuthenticator::class, JsonLoginAuthenticator::class])
        ->entryPoint(AuthenticationEntryPoint::class);
};
