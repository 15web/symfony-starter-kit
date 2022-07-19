<?php

declare(strict_types=1);

namespace App\User;

use App\User\Command\CreateToken;
use App\User\Command\SingUp\SignUp;
use App\User\Http\ApiTokenAuthenticator;
use App\User\Http\AuthenticationEntryPoint;
use App\User\Http\JsonLoginAuthenticator;
use App\User\Http\LogoutAction;
use App\User\Http\SignInAction;
use App\User\Http\SignUpAction;
use App\User\Model\Users;
use App\User\Model\UserTokens;
use App\User\Notification\SendNewPassword;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(Users::class);
    $services->set(UserTokens::class);
    $services->set(SignUp::class);
    $services->set(CreateToken::class);

    $services->set(ApiTokenAuthenticator::class);
    $services->set(JsonLoginAuthenticator::class);
    $services->set(AuthenticationEntryPoint::class);
    $services->set(SendNewPassword::class);

    $services->set(SignUpAction::class)->tag('controller.service_arguments');
    $services->set(SignInAction::class)->tag('controller.service_arguments');
    $services->set(LogoutAction::class)->tag('controller.service_arguments');
};
