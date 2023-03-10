<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use Dev\Maker\CustomStr;
use Dev\Maker\Vendor\EntityGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
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
            @trigger_error('The $projectDirectory constructor argument is no longer used since 1.41.0', E_USER_DEPRECATED);
        }
    }

    public static function getCommandName(): string
    {
        return 'make:module';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates simple CRUD module';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('module-name', InputArgument::OPTIONAL, sprintf('Name of module (e.g. <fg=yellow>%s</>)', CustomStr::asClassName(CustomStr::getRandomTerm())))
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Class name of the entity to create or update (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeModule.txt'));

        $inputConfig->setArgumentAsNonInteractive('name');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('name')) {
            return;
        }

        $argument = $command->getDefinition()->getArgument('name');
        $question = $this->entityGenerator->createEntityClassQuestion($argument->getDescription());
        $entityClassName = $io->askQuestion($question);

        $input->setArgument('name', $entityClassName);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $moduleName = $input->getArgument('module-name');

        // $fields - массив полей сущности (имя поля и ее тип)
        [$entityClassDetails, $fields] = $this->entityGenerator->generate($input, $io);

        $this->crudGenerator->generate(
            $moduleName.'\\Http\\',
            $entityClassDetails,
            $entityClassDetails->getShortName().'s',
            $fields
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

    public function configureDependencies(DependencyBuilder $dependencies, ?InputInterface $input = null): void
    {
        ORMDependencyBuilder::buildDependencies($dependencies);
    }
}
