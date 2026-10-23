<?php

declare(strict_types=1);

namespace App\User;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $hashCost = match ($container->env()) {
        'test' => 4,
        'dev' => 8,
        default => 12,
    };

    $container->parameters()
        ->set('app.hash_cost', $hashCost);

    $container->extension('framework', [
        'rate_limiter' => [
            'limiters' => [
                'sign_in' => [
                    'policy' => 'fixed_window',
                    'limit' => 3,
                    'interval' => '1 minute',
                ],
                'change_password' => [
                    'policy' => 'fixed_window',
                    'limit' => 3,
                    'interval' => '1 minute',
                ],
            ],
        ],
    ]);
};
