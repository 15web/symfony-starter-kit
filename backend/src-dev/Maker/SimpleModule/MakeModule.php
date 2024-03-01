<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use Dev\Maker\CustomStr;
use Dev\Maker\Vendor\EntityGenerator;
use Override;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Команда создания простого CRUD модуля
 */
final class MakeModule extends AbstractMaker implements InputAwareMakerInterface
{
    public function __construct(
        private readonly CRUDGenerator $crudGenerator,
        private readonly EntityGenerator $entityGenerator,
        private readonly FunctionalTestsGenerator $functionalTestsGenerator,
        ?string $projectDirectory = null,
    ) {
        if ($projectDirectory !== null) {
            @trigger_error(
                message: 'The $projectDirectory constructor argument is no longer used since 1.41.0',
                error_level: E_USER_DEPRECATED
            );
        }
    }

    #[Override]
    public static function getCommandName(): string
    {
        return 'make:module';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates simple CRUD module';
    }

    #[Override]
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        /** @var non-empty-string $helpFile */
        $helpFile = file_get_contents(__DIR__.'/../Resources/help/MakeModule.txt');

        $command
            ->addArgument(
                name: 'module-name',
                mode: InputArgument::OPTIONAL,
                description: sprintf(
                    'Name of module (e.g. <fg=yellow>%s</>)',
                    CustomStr::asClassName(CustomStr::getRandomTerm()),
                ),
            )
            ->addArgument(
                name: 'name',
                mode: InputArgument::OPTIONAL,
                description: sprintf(
                    'Class name of the entity to create or update (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm()),
                ),
            )
            ->setHelp($helpFile);

        $inputConfig->setArgumentAsNonInteractive('name');
    }

    #[Override]
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('name') !== null) {
            return;
        }

        $argument = $command->getDefinition()->getArgument('name');
        $question = $this->entityGenerator->createEntityClassQuestion($argument->getDescription());

        /** @var mixed $entityClassName */
        $entityClassName = $io->askQuestion($question);

        $input->setArgument('name', $entityClassName);
    }

    #[Override]
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var non-empty-string $moduleName */
        $moduleName = $input->getArgument('module-name');

        /**
         * $fields - массив полей сущности (имя поля и ее тип)
         *
         * @var ClassNameDetails $entityClassDetails
         * @var list<array<string, string>> $fields
         */
        [$entityClassDetails, $fields] = $this->entityGenerator->generate($input, $io);

        $this->crudGenerator->generate(
            namespacePrefix: $moduleName.'\\Http\\',
            entityClass: $entityClassDetails,
            repoClassName: $entityClassDetails->getShortName().'s',
            fields: $fields,
        );

        $this->functionalTestsGenerator->generate($moduleName, $entityClassDetails, $fields);

        $this->writeMessage($io);
    }

    public function writeMessage(ConsoleStyle $io): void
    {
        $this->writeSuccessMessage($io);
        $io->text([
            'Next: When you\'re ready, create a migration with <info>php bin/console make:migration</info>',
            '',
        ]);
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
