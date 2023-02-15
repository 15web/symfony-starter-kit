<?php

declare(strict_types=1);

namespace Dev\Maker\Maker;

use Dev\Maker\CRUDGenerator;
use Dev\Maker\CustomGenerator;
use Dev\Maker\CustomStr;
use Dev\Maker\Doctrine\EntityClassGeneratorForModule;
use Dev\Maker\EntityGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\PhpCompatUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Команда создания простого CRUD модуля
 */
final class MakeModule extends AbstractMaker implements InputAwareMakerInterface
{
    private CustomGenerator $generator;
    private EntityClassGeneratorForModule $entityClassGenerator;
    private PhpCompatUtil $phpCompatUtil;

    public function __construct(
        private readonly FileManager $fileManager,
        private readonly DoctrineHelper $doctrineHelper,
        ?string $projectDirectory = null,
        ?CustomGenerator $generator = null,
        ?EntityClassGeneratorForModule $entityClassGenerator = null,
        ?PhpCompatUtil $phpCompatUtil = null,
    ) {
        if ($projectDirectory !== null) {
            @trigger_error('The $projectDirectory constructor argument is no longer used since 1.41.0', E_USER_DEPRECATED);
        }

        if ($generator === null) {
            @trigger_error(sprintf('Passing a "%s" instance as 4th argument is mandatory since version 1.5.', CustomGenerator::class), E_USER_DEPRECATED);
            $this->generator = new CustomGenerator($fileManager, 'App\\');
        } else {
            $this->generator = $generator;
        }

        if ($entityClassGenerator === null) {
            @trigger_error(sprintf('Passing a "%s" instance as 5th argument is mandatory since version 1.15.1', EntityClassGeneratorForModule::class), E_USER_DEPRECATED);
            $this->entityClassGenerator = new EntityClassGeneratorForModule($generator, $this->doctrineHelper);
        } else {
            $this->entityClassGenerator = $entityClassGenerator;
        }

        if ($phpCompatUtil === null) {
            @trigger_error(sprintf('Passing a "%s" instance as 6th argument is mandatory since version 1.41.0', PhpCompatUtil::class), E_USER_DEPRECATED);
            $this->phpCompatUtil = new PhpCompatUtil($this->fileManager);
        } else {
            $this->phpCompatUtil = $phpCompatUtil;
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
        $question = $this->createEntityClassQuestion($argument->getDescription());
        $entityClassName = $io->askQuestion($question);

        $input->setArgument('name', $entityClassName);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityGenerator = new EntityGenerator(
            $this->fileManager,
            $this->doctrineHelper,
            $this->generator,
            $this->entityClassGenerator,
            $this->phpCompatUtil,
        );

        $entityGenerator->generate($input, $io, $this);

        $moduleName = $input->getArgument('module-name');
        $crudGenerator = new CRUDGenerator($this->generator);
        $crudGenerator->generate(
            $moduleName.'\\Http\\',
            $entityGenerator->getEntityClassName(),
            $entityGenerator->getRepositoryClassName(),
        );
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

    private function createEntityClassQuestion(string $questionText): Question
    {
        $question = new Question($questionText);
        $question->setValidator(static fn (?string $value = null): string => \Symfony\Bundle\MakerBundle\Validator::notBlank($value));
        $question->setAutocompleterValues($this->doctrineHelper->getEntitiesForAutocomplete());

        return $question;
    }
}
