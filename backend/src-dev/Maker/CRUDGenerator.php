<?php

declare(strict_types=1);

namespace Dev\Maker;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
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
final class CRUDGenerator
{
    public function __construct(private readonly CustomGenerator $generator)
    {
    }

    public function generate(string $namespacePrefix, ClassNameDetails $entityClass, string $repoClassName): void
    {
        $this->generateEntityArgumentValueResolver($namespacePrefix, $repoClassName, $entityClass);
        $this->generateInfoAction($namespacePrefix, $entityClass);
        $this->generateCreateAction($namespacePrefix, $repoClassName, $entityClass);
        $this->generateUpdateAction($namespacePrefix, $entityClass);
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
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/{id}';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/InfoAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'route_path' => $route,
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
            ]
        );
    }

    private function generateCreateAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass
    ): void {
        $this->generateCreateRequest($namespacePrefix, $entityClass);

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
            ]
        );
    }

    private function generateCreateRequest(string $namespacePrefix, ClassNameDetails $entityClass): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            'CreateRequest',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            ApiRequest::class,
            Assert::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/create/CreateRequest.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
            ]
        );
    }

    private function generateUpdateAction(string $namespacePrefix, ClassNameDetails $entityClass): void
    {
        $this->generateUpdateRequest($namespacePrefix, $entityClass);

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
        ]);

        $route = '/api/'.lcfirst($shortEntityClass).'s/{id}/update';
        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/update/UpdateAction.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'route_path' => $route,
            ]
        );
    }

    private function generateUpdateRequest(string $namespacePrefix, ClassNameDetails $entityClass): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            'UpdateRequest',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            ApiRequest::class,
            Assert::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/update/UpdateRequest.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
            ]
        );
    }
}
