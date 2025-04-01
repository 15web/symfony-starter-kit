<?php

declare(strict_types=1);

namespace Dev\Maker\Console;

use Dev\Maker\Command\GenerateAction;
use Dev\Maker\Command\GenerateTest;
use Dev\Maker\Entity\GenerateEntityClass;
use Dev\Maker\Entity\GenerateEntityFields;
use Override;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Команда создания простого CRUD модуля
 */
final class MakeModuleCommand extends AbstractMaker implements InputAwareMakerInterface
{
    public function __construct(
        private readonly GenerateEntityClass $generateEntityClass,
        private readonly GenerateEntityFields $generateEntityFields,
        private readonly GenerateAction $generateAction,
        private readonly GenerateTest $generateTest,
    ) {}

    #[Override]
    public static function getCommandName(): string
    {
        return 'make:module';
    }

    public static function getCommandDescription(): string
    {
        return 'Генерирует простой CRUD модуль';
    }

    #[Override]
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                name: 'module-name',
                mode: InputArgument::REQUIRED,
                description: 'Имя модуля (напр. <fg=yellow>Catalog\Product</>, или <fg=yellow>Vacancy</>)',
            )
            ->addArgument(
                name: 'entity-name',
                mode: InputArgument::REQUIRED,
                description: 'Имя класса сущности (напр. <fg=yellow>Product</>, или <fg=yellow>Category</>)',
            )
            ->addArgument(
                name: 'entity-title',
                mode: InputArgument::OPTIONAL,
                description: 'Наименование сущности (напр. <fg=yellow>Статья</>, или <fg=yellow>Товар</>)',
            );
    }

    #[Override]
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var non-empty-string $moduleName */
        $moduleName = $input->getArgument('module-name');
        $moduleName = ucfirst($moduleName);

        /** @var non-empty-string $entityName */
        $entityName = $input->getArgument('entity-name');
        $entityName = ucfirst($entityName);

        /** @var non-empty-string $entityTitle */
        $entityTitle = $input->getArgument('entity-title') ?? $entityName;

        $entityClassDetails = ($this->generateEntityClass)(
            moduleName: $moduleName,
            entityName: $entityName,
            entityTitle: $entityTitle,
        );

        $entityFields = ($this->generateEntityFields)(
            entityClassDetails: $entityClassDetails,
            io: $io,
        );

        ($this->generateAction)(
            moduleName: $moduleName,
            entityClassDetails: $entityClassDetails,
            entityTitle: $entityTitle,
            fields: $entityFields,
        );

        ($this->generateTest)(
            moduleName: $moduleName,
            entityClassDetails: $entityClassDetails,
            entityTitle: $entityTitle,
            fields: $entityFields,
        );

        $this->writeSuccessMessage($io);

        $io->writeln(
            'Теперь, когда Вы будете готовы, создайте миграцию с помощью команды <info>php bin/console make:migration</info>',
        );
    }

    #[Override]
    public function configureDependencies(DependencyBuilder $dependencies, ?InputInterface $input = null): void
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        ORMDependencyBuilder::buildDependencies($dependencies);
    }
}
