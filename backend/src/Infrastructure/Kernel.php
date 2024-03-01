<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Override;
use ReflectionAttribute;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Подключает конфиги из модулей, заполняет Service Container
 */
#[AsService]
final class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    private const array TAGGED_SERVICE = [
        AsService::class,
        AsController::class,
        AsCommand::class,
    ];

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definitionId => $definition) {
            $this->removeUntaggedServicesFromContainer($container, $definition, $definitionId);
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

    private function removeUntaggedServicesFromContainer(
        ContainerBuilder $container,
        Definition $definition,
        string $definitionId,
    ): void {
        $class = $container->getReflectionClass($definition->getClass(), false);

        if ($class === null) {
            return;
        }

        $namespace = $class->getNamespaceName();
        if ($namespace === '') {
            return;
        }

        if (!str_contains($namespace, 'App\\')) {
            return;
        }

        foreach (self::TAGGED_SERVICE as $serviceName) {
            $attributeAsService = $class->getAttributes(
                name: $serviceName,
                flags: ReflectionAttribute::IS_INSTANCEOF,
            );

            if ($attributeAsService !== []) {
                return;
            }
        }

        $container->removeDefinition($definitionId);
    }
}
