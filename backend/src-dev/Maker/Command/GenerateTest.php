<?php

declare(strict_types=1);

namespace Dev\Maker\Command;

use Dev\Maker\ClassGenerator;
use Dev\Maker\Entity\EntityFieldsManipulator;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassProperty;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV7;

/**
 * Создает функциональные тесты для модуля
 */
final readonly class GenerateTest
{
    private ClassNameDetails $entityClassDetails;

    /**
     * @var non-empty-string
     */
    private string $moduleName;

    /**
     * @var non-empty-string
     */
    private string $adminPrefix;

    /**
     * @var non-empty-string
     */
    private string $entityTitle;

    /**
     * @var list<ClassProperty>
     */
    private array $fields;

    public function __construct(
        private string $path,
        private ClassGenerator $generator,
        private EntityFieldsManipulator $entityFieldsManipulator,
    ) {}

    /**
     * @param non-empty-string $moduleName
     * @param non-empty-string $entityTitle
     * @param list<ClassProperty> $fields
     */
    public function __invoke(
        string $moduleName,
        ClassNameDetails $entityClassDetails,
        string $entityTitle,
        array $fields,
    ): void {
        $this->moduleName = $moduleName;
        $this->adminPrefix = 'Admin\\';
        $this->entityClassDetails = $entityClassDetails;
        $this->entityTitle = $entityTitle;
        $this->fields = $fields;

        $this->generateSDK();
        $this->generateAdminCreateTest();
        $this->generateAdminInfoTest();
        $this->generateAdminListTest();
        $this->generateAdminUpdateTest();
        $this->generateAdminRemoveTest();

        $this->generator->writeChanges();
    }

    private function generateSDK(): void
    {
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $this->entityClassDetails->getShortName(),
            namespacePrefix: "{$this->path}\\SDK",
        );

        $useStatements = new UseStatementGenerator([
            Response::class,
            Request::class,
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/SDK.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getMethodParametersSignature($this->fields),
            ],
        );
    }

    private function generateAdminInfoTest(): void
    {
        $testName = "Get{$this->entityClassDetails->getShortName()}ActionTest";

        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: "{$this->path}\\{$this->moduleName}\\{$this->adminPrefix}",
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            "Dev\\Tests\\{$this->path}\\SDK\\{$this->entityClassDetails->getShortName()}",
            User::class,
            UuidV7::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            "Dev\\Tests\\{$this->path}\\SDK\\User",
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/InfoTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($this->fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($this->fields),
                'entity_title' => $this->entityTitle,
            ],
        );
    }

    private function generateAdminCreateTest(): void
    {
        $testName = "Create{$this->entityClassDetails->getShortName()}ActionTest";

        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: "{$this->path}\\{$this->moduleName}\\{$this->adminPrefix}",
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            "Dev\\Tests\\{$this->path}\\SDK\\{$this->entityClassDetails->getShortName()}",
            User::class,
            Iterator::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            "Dev\\Tests\\{$this->path}\\SDK\\User",
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/CreateTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($this->fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($this->fields),
                'entity_title' => $this->entityTitle,
            ],
        );
    }

    private function generateAdminRemoveTest(): void
    {
        $testName = "Delete{$this->entityClassDetails->getShortName()}ActionTest";

        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: "{$this->path}\\{$this->moduleName}\\{$this->adminPrefix}",
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            "Dev\\Tests\\{$this->path}\\SDK\\{$this->entityClassDetails->getShortName()}",
            User::class,
            UuidV7::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            "Dev\\Tests\\{$this->path}\\SDK\\User",
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/RemoveTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($this->fields),
                'entity_title' => $this->entityTitle,
            ],
        );
    }

    private function generateAdminUpdateTest(): void
    {
        $testName = "Update{$this->entityClassDetails->getShortName()}ActionTest";

        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: "{$this->path}\\{$this->moduleName}\\{$this->adminPrefix}",
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            "Dev\\Tests\\{$this->path}\\SDK\\{$this->entityClassDetails->getShortName()}",
            User::class,
            UuidV7::class,
            Iterator::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            "Dev\\Tests\\{$this->path}\\SDK\\User",
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/UpdateTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($this->fields),
                'data_for_update' => $this->entityFieldsManipulator->getValuesWithType($this->fields),
                'entity_title' => $this->entityTitle,
            ],
        );
    }

    private function generateAdminListTest(): void
    {
        $testName = "Get{$this->entityClassDetails->getShortName()}ListActionTest";

        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: "{$this->path}\\{$this->moduleName}\\{$this->adminPrefix}",
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            "Dev\\Tests\\{$this->path}\\SDK\\{$this->entityClassDetails->getShortName()}",
            User::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            "Dev\\Tests\\{$this->path}\\SDK\\User",
        ]);

        $this->generator->generate(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/ListTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $this->entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($this->entityClassDetails->getShortName()),
                'entity_fields' => $this->fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($this->fields),
                'data_for_update' => $this->entityFieldsManipulator->getValuesWithType($this->fields),
                'entity_title' => $this->entityTitle,
            ],
        );
    }
}
