<?php

declare(strict_types=1);

namespace Dev\Infrastructure;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services
        ->load('Dev\\', '../*')
        ->exclude([
            './{di.php}',
            '../**/{di.php}',
            '../**/{config.php}',
            '../Tests',
        ]);
};
