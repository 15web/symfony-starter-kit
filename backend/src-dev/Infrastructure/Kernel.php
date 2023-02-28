<?php

declare(strict_types=1);

namespace Dev\Infrastructure;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Подключает основные конфиги приложения и заполняет Service Container сервисами из src-dev
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache-dev';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log-dev';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../../config/{packages}/*.yaml');
        $container->import('../../config/{packages}/'.$this->environment.'/*.yaml');

        $container->import('./di.php');
    }
}
