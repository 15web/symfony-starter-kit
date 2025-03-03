<?php

declare(strict_types=1);

namespace Dev\Maker\Vendor;

use App\Infrastructure\AsService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * @internal
 *
 * Создает сущность и репозиторий в нужной директории - App\ModuleName\Domain, добавляет суффикс к репозиторию (s)
 * Почти копия EntityClassGenerator(MakerBundle)
 */
final readonly class EntityClassGeneratorForModule
{
    public function __construct(
        private CustomGenerator $generator,
        private DoctrineHelper $doctrineHelper,
    ) {}

    public function generateEntityClass(
        string $moduleName,
        string $entityTitle,
        ClassNameDetails $entityClassDetails,
        bool $generateRepositoryClass = true,
    ): string {
        $repoClassDetails = $this->generator->createClassNameDetails(
            name: $entityClassDetails->getRelativeName(),
            namespacePrefix: $moduleName.'\\Domain\\',
            suffix: 'Repository',
        );

        /** @psalm-suppress InternalMethod */
        $tableName = $this->doctrineHelper->getPotentialTableName($entityClassDetails->getFullName());

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore-next-line
         */
        $useStatements = new UseStatementGenerator([
            Uuid::class,
            UuidV7::class,
            ['Doctrine\\ORM\\Mapping' => 'ORM'],
            DateTimeImmutable::class,
        ]);

        /** @psalm-suppress InternalMethod */
        $entityPath = $this->generator->generateClass(
            className: $entityClassDetails->getFullName(),
            templateName: 'domain/Entity.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'repository_class_name' => $repoClassDetails->getShortName(),
                'api_resource' => false,
                'broadcast' => false,
                'should_escape_table_name' => $this->doctrineHelper->isKeyword($tableName),
                'table_name' => $tableName,
                'entity_title' => $entityTitle,
            ],
        );

        if ($generateRepositoryClass) {
            $this->generateRepository(
                repositoryClass: $repoClassDetails->getFullName(),
                entityClass: $entityClassDetails->getFullName(),
            );
        }

        return $entityPath;
    }

    public function generateRepository(string $repositoryClass, string $entityClass): void
    {
        $shortEntityClass = Str::getShortClassName($entityClass);
        $entityVariableName = strtolower($shortEntityClass);
        $entityAlias = strtolower($shortEntityClass[0]);

        $useStatements = new UseStatementGenerator([
            AsService::class,
            EntityManagerInterface::class,
            Uuid::class,
        ]);

        $this->generator->generateClass(
            className: $repositoryClass,
            templateName: 'domain/Repository.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'entity_class_name' => $shortEntityClass,
                'entity_alias' => $entityAlias,
                'entity_variable_name' => $entityVariableName,
                'include_example_comments' => true,
            ],
        );
    }
}
