<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Infrastructure\Message;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\Framework\Messenger\RoutingConfig;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config, ContainerConfigurator $containerConfigurator): void {
    /** @var RoutingConfig $messengerRouting */
    $messengerRouting = $config->messenger()->routing(Message::class);

    if ($containerConfigurator->env() === 'dev') {
        $messengerRouting->senders(['async']);
    }

    if ($containerConfigurator->env() === 'test') {
        $messengerRouting->senders(['sync']);
    }

    if ($containerConfigurator->env() === 'prod') {
        $messengerRouting->senders(['async']);
    }

    $config->translator()
        ->paths(['%kernel.project_dir%/src/Mailer/translations']);
};
