<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use Dev\Maker\EntityFieldsManipulator;
use Dev\Maker\Vendor\CustomGenerator;
use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\User;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV7;

/**
 * Создает функциональные тесты для модуля
 */
final readonly class FunctionalTestsGenerator
{
    private EntityFieldsManipulator $entityFieldsManipulator;

    public function __construct(
        private string $path,
        private CustomGenerator $generator,
    ) {
        $this->entityFieldsManipulator = new EntityFieldsManipulator();
    }

    /**
     * @param non-empty-string $namespacePrefix
     * @param non-empty-string $moduleName
     * @param list<array<string, string>> $fields
     */
    public function generate(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $this->generateSDK($entityClassDetails, $fields);
        $this->generateInfoTest($namespacePrefix, $moduleName, $entityClassDetails, $fields, $entityTitle);
        $this->generateCreateTest($namespacePrefix, $moduleName, $entityClassDetails, $fields, $entityTitle);
        $this->generateRemoveTest($namespacePrefix, $moduleName, $entityClassDetails, $fields, $entityTitle);
        $this->generateUpdateTest($namespacePrefix, $moduleName, $entityClassDetails, $fields, $entityTitle);
        $this->generateListTest($namespacePrefix, $moduleName, $entityClassDetails, $fields, $entityTitle);

        $this->generator->writeChanges();
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateSDK(ClassNameDetails $entityClassDetails, array $fields): void
    {
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $entityClassDetails->getShortName(),
            namespacePrefix: $this->path.'\\SDK',
        );

        $useStatements = new UseStatementGenerator([
            Response::class,
            Request::class,
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/SDK.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getMethodParametersSignature($fields),
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateInfoTest(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $testName = 'Get'.$entityClassDetails->getShortName().'ActionTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: $this->path.'\\'.$moduleName.$namespacePrefix,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            UuidV7::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\User', // SDK User
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/InfoTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($fields),
                'entity_title' => $entityTitle,
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateCreateTest(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $testName = 'Create'.$entityClassDetails->getShortName().'ActionTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: $this->path.'\\'.$moduleName.$namespacePrefix,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            Iterator::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\User', // SDK User
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/CreateTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($fields),
                'entity_title' => $entityTitle,
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateRemoveTest(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $testName = 'Delete'.$entityClassDetails->getShortName().'ActionTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: $this->path.'\\'.$moduleName.$namespacePrefix,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            UuidV7::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\User', // SDK User
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/RemoveTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'entity_title' => $entityTitle,
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateUpdateTest(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $testName = 'Update'.$entityClassDetails->getShortName().'ActionTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: $this->path.'\\'.$moduleName.$namespacePrefix,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            UuidV7::class,
            Iterator::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\User', // SDK User
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/UpdateTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'data_for_update' => $this->entityFieldsManipulator->getValuesWithType($fields),
                'entity_title' => $entityTitle,
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateListTest(string $namespacePrefix, string $moduleName, ClassNameDetails $entityClassDetails, array $fields, string $entityTitle): void
    {
        $testName = 'Get'.$entityClassDetails->getShortName().'ListActionTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            name: $testName,
            namespacePrefix: $this->path.'\\'.$moduleName.$namespacePrefix,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            DataProvider::class,
            TestDox::class,
            Request::class,
            'Dev\\Tests\\'.$this->path.'\\SDK\\User', // SDK User
        ]);

        $this->generator->generateClass(
            className: $sdkDetails->getFullName(),
            templateName: 'tests/ListTest.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'data_for_update' => $this->entityFieldsManipulator->getValuesWithType($fields),
                'entity_title' => $entityTitle,
            ],
        );
    }
}
