<?php

declare(strict_types=1);

namespace App\Task\Query;

use App\Task\Query\Task\FindAllTasksByUserId;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(FindAllTasksByUserId::class);
};
