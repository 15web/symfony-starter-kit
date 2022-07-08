<?php

declare(strict_types=1);

namespace App\User;

use App\User\Model\User;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('app_user_provider')
        ->entity()
        ->class(User::class)
        ->property('userEmail.value');

    $mainFirewall = $security->firewall('main');
    $mainFirewall->jsonLogin()
        ->checkPath('sign-in')
        ->usernamePath('email')
        ->passwordPath('password');
};
