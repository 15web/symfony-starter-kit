<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Infrastructure\Message;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $senders = match ($container->env()) {
        'test' => ['sync'],
        default => ['async'],
    };

    $container->extension('framework', [
        'messenger' => [
            'routing' => [
                Message::class => $senders,
            ],
        ],
    ]);
};
