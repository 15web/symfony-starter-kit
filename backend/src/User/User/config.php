<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $hashCost = match ($container->env()) {
        'test' => 4,
        'dev' => 8,
        default => 12,
    };

    $container->parameters()
        ->set('app.hash_cost', $hashCost);
};
