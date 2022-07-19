<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiRequestResolver;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(ApiRequestArgumentValueResolver::class);
};
