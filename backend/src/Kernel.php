<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $this->processClass($container, $definition, $id);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        $container->import('./**/{config}.php');

        $container->import('./**/{di}.php');
        $container->import("./**/{di}_{$this->environment}.php");

        $container->import('./{di}.php');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');
    }

    private function processClass(ContainerBuilder $container, Definition $definition, string $id): void
    {
        $class = $container->getReflectionClass($definition->getClass(), false);

        if ($class === null) {
            return;
        }

        $attributes = $class->getAttributes(ExcludeFromDI::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (\count($attributes) === 0) {
            return;
        }

        $container->removeDefinition($id);
    }
}
