<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\User\SignUp\Domain\UserRole;
use Dev\Maker\EntityFieldsManipulator;
use Dev\Maker\Vendor\CustomGenerator;
use InvalidArgumentException;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Создает HTTP слой для модуля
 */
#[AsService]
final readonly class CRUDGenerator
{
    public function __construct(
        private CustomGenerator $generator,
        private EntityFieldsManipulator $entityFieldsManipulator
    ) {
    }

    public function generate(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $repoClassName,
        array $fields
    ): void {
        $this->generateEntityArgumentValueResolver($namespacePrefix, $repoClassName, $entityClass);
        $this->generateInfoAction($namespacePrefix, $entityClass);
        $this->generateCreateAction($namespacePrefix, $repoClassName, $entityClass, $fields);
        $this->generateUpdateAction($namespacePrefix, $entityClass, $fields);
        $this->generateRemoveAction($namespacePrefix, $repoClassName, $entityClass);

        $this->generator->writeChanges();
    }

    private function generateEntityArgumentValueResolver(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            $entityClass->getShortName().'ArgumentValueResolver',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'s',
            AsService::class,
            Assert::class,
            ApiBadRequestException::class,
            ApiNotFoundException::class,
            ArgumentMetadata::class,
            InvalidArgumentException::class,
            ValueResolverInterface::class,
            Request::class,
            Uuid::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/EntityArgumentValueResolver.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_repository' => $repoClassName,
            ]
        );
    }

    private function generateInfoAction(string $namespacePrefix, ClassNameDetails $entityClass): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            'InfoAction',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            AsController::class,
            Route::class,
            IsGranted::class,
            UserRole::class,
            Request::class,
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/{id}';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/InfoAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_GET]',
                'role' => 'UserRole::User->value',
            ]
        );
    }

    private function generateRemoveAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            'RemoveAction',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'s',
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            SuccessResponse::class,
            UserRole::class,
            Request::class,
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/{id}/remove';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/RemoveAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'repository_classname' => $repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_POST]',
                'role' => 'UserRole::User->value',
            ]
        );
    }

    private function generateCreateAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass,
        array $fields
    ): void {
        $this->generateCreateRequest($namespacePrefix, $entityClass, $fields);

        $createActionDetails = $this->generator->createClassNameDetails(
            'CreateAction',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'s',
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            SuccessResponse::class,
            UserRole::class,
            Request::class,
            ApiRequest::class,
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/create';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/create/CreateAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'repository_classname' => $repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_POST]',
                'role' => 'UserRole::User->value',
            ]
        );
    }

    private function generateCreateRequest(string $namespacePrefix, ClassNameDetails $entityClass, array $fields): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            'CreateRequest',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            Assert::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/create/CreateRequest.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_fields' => $fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($fields),
            ]
        );
    }

    private function generateUpdateAction(string $namespacePrefix, ClassNameDetails $entityClass, array $fields): void
    {
        $this->generateUpdateRequest($namespacePrefix, $entityClass, $fields);

        $createActionDetails = $this->generator->createClassNameDetails(
            'UpdateAction',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            SuccessResponse::class,
            UserRole::class,
            Request::class,
            ApiRequest::class,
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/{id}/update';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/update/UpdateAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_POST]',
                'role' => 'UserRole::User->value',
            ]
        );
    }

    private function generateUpdateRequest(string $namespacePrefix, ClassNameDetails $entityClass, array $fields): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            'UpdateRequest',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            Assert::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/update/UpdateRequest.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_fields' => $fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($fields),
            ]
        );
    }
}
