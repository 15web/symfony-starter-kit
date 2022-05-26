<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentValueResolver;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(ApiRequestResolver::class);
};
