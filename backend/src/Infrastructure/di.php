<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\Console\OpenApiRoutesDiffCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(OpenApiRoutesDiffCommand::class);
    $services->set(ResponseEventSubscriber::class);
    $services->set(Flusher::class);

    $services->set(MailerSubscriber::class)
        ->arg('$fromEmail', '%env(string:MAILER_FROM_EMAIL)%')
        ->arg('$fromName', '%env(string:MAILER_FROM_NAME)%');
};
