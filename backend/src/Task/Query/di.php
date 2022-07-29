<?php

declare(strict_types=1);

namespace App\Task\Query;

use App\Task\Query\FindAllByUserId\FindAllTasksByUserId;
use App\Task\Query\FindById\FindTaskById;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(FindAllTasksByUserId::class);
    $services->set(FindTaskById::class);
};
