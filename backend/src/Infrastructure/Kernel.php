<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Override;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Подключает конфиги из модулей
 */
final class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        // todo разобраться как можно вынести в отдельный конфиг
        if ($this->environment === 'test') {
            $container->getDefinition('cache.adapter.array')->setClass(KeepCacheArrayAdapter::class);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../../config/{packages}/*.yaml');
        $container->import('../../config/{packages}/'.$this->environment.'/*.yaml');

        $container->import('../**/config.php');

        $container->import('./di.php');
        if ($container->env() !== 'prod') {
            $container->import('../../src-dev/**/di.php', null, true);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../../config/{routes}/*.yaml');
    }
}
