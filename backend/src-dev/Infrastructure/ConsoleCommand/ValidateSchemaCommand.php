<?php

declare(strict_types=1);

namespace Dev\Infrastructure\ConsoleCommand;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Консольная команда для валидации схемы
 *
 * @todo Временное решение пока не починят валидатор
 *       https://github.com/doctrine/migrations/issues/1406
 *
 * @see \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand
 */
#[AsCommand(name: 'app:schema:validate')]
final class ValidateSchemaCommand extends Command
{
    private const array DATABASE_EXCLUDE_PATTERNS = [
        '/DROP TABLE doctrine_migration_versions/',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ui = (new SymfonyStyle($input, $output))->getErrorStyle();
        $validator = new SchemaValidator($this->em, true);

        $exit = Command::SUCCESS;

        $ui->section('Mapping');

        $errors = $validator->validateMapping();
        if ($errors !== []) {
            foreach ($errors as $className => $errorMessages) {
                $ui->text(sprintf('<error>[FAIL]</error> The entity-class <comment>%s</comment> mapping is invalid:', $className));
                $ui->listing($errorMessages);
                $ui->newLine();
            }

            $exit = Command::FAILURE;
        } else {
            $ui->success('The mapping files are correct.');
        }

        $ui->section('Database');

        $filterCallback = static function (string $sql): bool {
            foreach (self::DATABASE_EXCLUDE_PATTERNS as $pattern) {
                if (preg_match($pattern, $sql) === 1) {
                    return false;
                }
            }

            return true;
        };

        $filteredSqlList = array_filter(
            array: $validator->getUpdateSchemaList(),
            callback: $filterCallback,
        );

        if ($filteredSqlList !== []) {
            $ui->error('The database schema is not in sync with the current mapping file.');

            $ui->comment(sprintf('<info>%d</info> schema diff(s) detected:', \count($filteredSqlList)));

            $ui->text(
                array_map(
                    callback: static fn (string $sql): string => sprintf('    %s;', $sql),
                    array: $filteredSqlList,
                ),
            );

            $exit = Command::FAILURE;
        } else {
            $ui->success('The database schema is in sync with the mapping files.');
        }

        return $exit;
    }
}
