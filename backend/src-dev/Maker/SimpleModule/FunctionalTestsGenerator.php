<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\User;
use Dev\Maker\EntityFieldsManipulator;
use Dev\Maker\Vendor\CustomGenerator;
use Iterator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
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

    public function generate(string $moduleName, ClassNameDetails $entityClassDetails, array $fields): void
    {
        $this->generateSDK($entityClassDetails, $fields);
        $this->generateInfoTest($moduleName, $entityClassDetails, $fields);
        $this->generateCreateTest($moduleName, $entityClassDetails, $fields);
        $this->generateRemoveTest($moduleName, $entityClassDetails, $fields);
        $this->generateUpdateTest($moduleName, $entityClassDetails, $fields);

        $this->generator->writeChanges();
    }

    private function generateSDK(ClassNameDetails $entityClassDetails, array $fields): void
    {
        $sdkDetails = $this->generator->createClassNameDetails(
            $entityClassDetails->getShortName(),
            $this->path.'\\SDK',
        );

        $useStatements = new UseStatementGenerator([
            Response::class,
        ]);

        $this->generator->generateClass(
            $sdkDetails->getFullName(),
            'tests/SDK.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getMethodParametersSignature($fields),
            ]
        );
    }

    private function generateInfoTest(string $moduleName, ClassNameDetails $entityClassDetails, array $fields): void
    {
        $testName = $entityClassDetails->getShortName().'InfoTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            $testName,
            $this->path.'\\'.$moduleName,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'App\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            Uuid::class,
            UuidV7::class,
        ]);

        $this->generator->generateClass(
            $sdkDetails->getFullName(),
            'tests/InfoTest.tpl.php',
            [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($fields),
            ]
        );
    }

    private function generateCreateTest(string $moduleName, ClassNameDetails $entityClassDetails, array $fields): void
    {
        $testName = $entityClassDetails->getShortName().'CreateTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            $testName,
            $this->path.'\\'.$moduleName,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'App\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            Iterator::class,
        ]);

        $this->generator->generateClass(
            $sdkDetails->getFullName(),
            'tests/CreateTest.tpl.php',
            [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'create_params_with_variables' => $this->entityFieldsManipulator->getVariablesByFields($fields),
            ]
        );
    }

    private function generateRemoveTest(string $moduleName, ClassNameDetails $entityClassDetails, array $fields): void
    {
        $testName = $entityClassDetails->getShortName().'RemoveTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            $testName,
            $this->path.'\\'.$moduleName,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'App\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            Uuid::class,
            UuidV7::class,
        ]);

        $this->generator->generateClass(
            $sdkDetails->getFullName(),
            'tests/RemoveTest.tpl.php',
            [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
            ]
        );
    }

    private function generateUpdateTest(string $moduleName, ClassNameDetails $entityClassDetails, array $fields): void
    {
        $testName = $entityClassDetails->getShortName().'UpdateTest';
        $sdkDetails = $this->generator->createClassNameDetails(
            $testName,
            $this->path.'\\'.$moduleName,
        );

        $useStatements = new UseStatementGenerator([
            ApiWebTestCase::class,
            'App\\Tests\\'.$this->path.'\\SDK\\'.$entityClassDetails->getShortName(), // SDK
            User::class,
            Uuid::class,
            UuidV7::class,
            Iterator::class,
        ]);

        $this->generator->generateClass(
            $sdkDetails->getFullName(),
            'tests/UpdateTest.tpl.php',
            [
                'use_statements' => $useStatements,
                'test_name' => $testName,
                'entity_classname' => $entityClassDetails->getShortName(),
                'entity_classname_small' => lcfirst($entityClassDetails->getShortName()),
                'entity_fields' => $fields,
                'create_params' => $this->entityFieldsManipulator->getValuesByFields($fields),
                'data_for_update' => $this->entityFieldsManipulator->getValuesWithType($fields),
            ]
        );
    }
}
