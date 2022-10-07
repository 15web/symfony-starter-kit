<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

#[AsService]
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
        $container->import('../../config/{packages}/*.yaml');
        $container->import('../../config/{packages}/'.$this->environment.'/*.yaml');

        $container->import('../**/config.php');

        $container->import('./di.php');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../../config/{routes}/*.yaml');
    }

    private function processClass(ContainerBuilder $container, Definition $definition, string $id): void
    {
        $class = $container->getReflectionClass($definition->getClass(), false);

        if ($class === null) {
            return;
        }

        $namespace = $class->getNamespaceName();

        if ($namespace === '' || str_contains($namespace, 'App\\') === false) {
            return;
        }

        $attributeAsService = $class->getAttributes(AsService::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (\count($attributeAsService) > 0) {
            return;
        }

        $attributeAsController = $class->getAttributes(AsController::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (\count($attributeAsController) > 0) {
            return;
        }

        $attributeAsCommand = $class->getAttributes(AsCommand::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (\count($attributeAsCommand) > 0) {
            return;
        }

        $container->removeDefinition($id);
    }
}
