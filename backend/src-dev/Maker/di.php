<?php

declare(strict_types=1);

namespace Dev\Maker;

use Dev\Maker\SimpleModule\FunctionalTestsGenerator;
use Dev\Maker\SimpleModule\MakeModule;
use Dev\Maker\Vendor\CustomGenerator;
use Dev\Maker\Vendor\EntityClassGeneratorForModule;
use Dev\Maker\Vendor\EntityGenerator;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services
        ->load('Dev\\', '../*')
        ->exclude([
            './{di.php}',
            '../**/{di.php}',
            '../**/{config.php}',
            '../**/bootstrap.php',
            '../cache/**',
        ]);

    $services->set(MakeModule::class)
        ->tag('maker.command');

    $services->set(CustomGenerator::class)
        ->bind('$namespacePrefix', 'App')
        ->bind(FileManager::class, service('maker.file_manager'));

    $services->set('custom_maker.generator_for_tests', CustomGenerator::class)
        ->bind('$namespacePrefix', 'Dev\Tests')
        ->bind(FileManager::class, service('maker.file_manager'));

    $services->set(EntityGenerator::class)
        ->bind(FileManager::class, service('maker.file_manager'))
        ->bind(DoctrineHelper::class, service('maker.doctrine_helper'));

    $services->set(FunctionalTestsGenerator::class)
        ->bind(CustomGenerator::class, service('custom_maker.generator_for_tests'))
        ->bind('$path', 'Functional');

    $services->set(EntityClassGeneratorForModule::class)
        ->bind(DoctrineHelper::class, service('maker.doctrine_helper'));
};
