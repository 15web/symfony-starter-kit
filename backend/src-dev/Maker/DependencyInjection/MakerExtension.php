<?php

declare(strict_types=1);

namespace Symfony\Bundle\MakerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Bundle\MakerBundle\DependencyInjection\CompilerPass\MakeCommandRegistrationPass;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Переопределяет MakerExtension из MakerBundle, нужен для подгрузки и тегирования собственных Maker'ов с нашей конфигурацией
 * Конфигурация для Make комманд MakerBundle сюда копируется из самого MakerBundle, просто добавляются дополнительные наши команды
 * В целом - полная копия MakerExtension из MakerBundle
 */
final class MakerExtension extends Extension
{
    /**
     * @deprecated remove this block when removing make:unit-test and make:functional-test
     */
    private const TEST_MAKER_DEPRECATION_MESSAGE = 'The "%service_id%" service is deprecated, use "Maker.Maker.make_test" instead.';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator([
            __DIR__.'/../Resources/config',
            __DIR__.'/../../../vendor/symfony/maker-bundle/src/Resources/config',
        ]));
        $loader->load('services.xml');
        $loader->load('makers.xml');
        $loader->load('custom_services.xml');
        $loader->load('custom_makers.xml');

        /**
         * @deprecated remove this block when removing make:unit-test and make:functional-test
         */
        $deprecParams = method_exists(Definition::class, 'getDeprecation') ? ['symfony/maker-bundle', '1.29', self::TEST_MAKER_DEPRECATION_MESSAGE] : [true, self::TEST_MAKER_DEPRECATION_MESSAGE];
        $container
            ->getDefinition('maker.maker.make_unit_test')
            ->setDeprecated(...$deprecParams);
        $container
            ->getDefinition('maker.maker.make_functional_test')
            ->setDeprecated(...$deprecParams);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $rootNamespace = trim((string) $config['root_namespace'], '\\');

        $autoloaderFinderDefinition = $container->getDefinition('maker.autoloader_finder');
        $autoloaderFinderDefinition->replaceArgument(0, $rootNamespace);

        $makeCommandDefinition = $container->getDefinition('maker.generator');
        $makeCommandDefinition->replaceArgument(1, $rootNamespace);

        $doctrineHelperDefinition = $container->getDefinition('maker.doctrine_helper');
        $doctrineHelperDefinition->replaceArgument(0, $rootNamespace.'\\Entity');

        $container->registerForAutoconfiguration(MakerInterface::class)
            ->addTag(MakeCommandRegistrationPass::MAKER_TAG);
    }
}
