<?php

declare(strict_types=1);

namespace Dev\Maker;

use Dev\Maker\Command\GenerateTest;
use Dev\Maker\Console\MakeModuleCommand;
use Dev\Maker\Entity\GenerateEntityClass;
use Dev\Maker\Entity\GenerateEntityFields;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(MakeModuleCommand::class)
        ->tag('maker.command');

    $services->set(GenerateEntityClass::class)
        ->bind(DoctrineHelper::class, service('maker.doctrine_helper'));

    $services->set(GenerateEntityFields::class)
        ->bind(FileManager::class, service('maker.file_manager'))
        ->bind(DoctrineHelper::class, service('maker.doctrine_helper'));

    $services->set(ClassGenerator::class)
        ->bind('$namespacePrefix', 'App')
        ->bind(FileManager::class, service('maker.file_manager'));

    $services->set('custom_maker.class_generator', ClassGenerator::class)
        ->bind('$namespacePrefix', 'Dev\Tests')
        ->bind(FileManager::class, service('maker.file_manager'));

    $services->set(GenerateTest::class)
        ->bind(ClassGenerator::class, service('custom_maker.class_generator'))
        ->bind('$path', 'Functional');
};
