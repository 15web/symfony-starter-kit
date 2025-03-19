<?php

declare(strict_types=1);

namespace Dev\Maker\SimpleModule;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\PaginationResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\User\Security\Http\IsGranted;
use App\User\User\Domain\UserRole;
use Dev\Maker\EntityFieldsManipulator;
use Dev\Maker\Vendor\CustomGenerator;
use InvalidArgumentException;
use Override;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassProperty;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Webmozart\Assert\Assert;

/**
 * Создает HTTP слой для модуля
 */
#[AsService]
final readonly class CRUDGenerator
{
    public function __construct(
        private CustomGenerator $generator,
        private EntityFieldsManipulator $entityFieldsManipulator,
        private Filesystem $filesystem,
    ) {}

    /**
     * @param list<array<string, string>> $fields
     */
    public function generate(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $repoClassName,
        array $fields,
        string $entityTitle,
    ): void {
        $this->generateEntityArgumentValueResolver($namespacePrefix, $repoClassName, $entityClass, $entityTitle);
        $this->generateInfoAction($namespacePrefix, $entityClass, $entityTitle);
        $this->generateCreateAction($namespacePrefix, $repoClassName, $entityClass, $entityTitle, $fields);
        $this->generateUpdateAction($namespacePrefix, $entityClass, $entityTitle, $fields);
        $this->generateRemoveAction($namespacePrefix, $repoClassName, $entityClass, $entityTitle);
        $this->generateListAction($namespacePrefix, $repoClassName, $entityClass, $entityTitle);
        $this->generateOpenApi($entityClass, $entityTitle, $fields);

        $this->generator->writeChanges();
    }

    private function generateEntityArgumentValueResolver(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass,
        string $entityTitle,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            $entityClass->getShortName().'ArgumentValueResolver',
            $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'Repository',
            AsService::class,
            Assert::class,
            ApiNotFoundException::class,
            ArgumentMetadata::class,
            ValueResolverInterface::class,
            Request::class,
            Uuid::class,
            Override::class,
            InvalidArgumentException::class,
        ]);

        $this->generator->generateClass(
            $createActionDetails->getFullName(),
            'http/EntityArgumentValueResolver.tpl.php',
            [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_repository' => $repoClassName,
                'entity_title' => $entityTitle,
            ],
        );
    }

    private function generateInfoAction(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $entityTitle,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Get'.$entityClass->getShortName().'Action',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            AsController::class,
            Route::class,
            Request::class,
            ValueResolver::class,
            ApiObjectResponse::class,
            IsGranted::class,
            UserRole::class,
        ]);

        $route = '/admin/'.lcfirst($shortEntityClass).'s/{id}';
        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/InfoAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_GET]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateRemoveAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass,
        string $entityTitle,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Delete'.$entityClass->getShortName().'Action',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'Repository',
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            SuccessResponse::class,
            UserRole::class,
            Request::class,
            ValueResolver::class,
            ApiObjectResponse::class,
        ]);

        $route = '/admin/'.lcfirst($shortEntityClass).'s/{id}';
        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/RemoveAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'repository_classname' => $repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_DELETE]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateListAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass,
        string $entityTitle,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Get'.$entityClass->getShortName().'ListAction',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName().'Repository',
            AsController::class,
            Route::class,
            IsGranted::class,
            UserRole::class,
            ApiListObjectResponse::class,
            Request::class,
            PaginationResponse::class,
        ]);

        $route = '/admin/'.lcfirst($shortEntityClass).'s';
        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/ListAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'repository_classname' => $repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_GET]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateCreateAction(
        string $namespacePrefix,
        string $repoClassName,
        ClassNameDetails $entityClass,
        string $entityTitle,
        array $fields,
    ): void {
        $this->generateCreateRequest($namespacePrefix, $entityClass, $entityTitle, $fields);

        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Create'.$entityClass->getShortName().'Action',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            $entityClass->getFullName().'Repository',
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            UserRole::class,
            Request::class,
            ApiRequestValueResolver::class,
            ValueResolver::class,
            ApiObjectResponse::class,
            UuidV7::class,
        ]);

        $route = '/admin/'.lcfirst($shortEntityClass).'s';
        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/create/CreateAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'repository_classname' => $repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_POST]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateCreateRequest(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $entityTitle,
        array $fields,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Create'.$entityClass->getShortName().'Request',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());

        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/create/CreateRequest.tpl.php',
            variables: [
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'entity_fields' => $fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($fields),
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateUpdateAction(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $entityTitle,
        array $fields,
    ): void {
        $this->generateUpdateRequest($namespacePrefix, $entityClass, $entityTitle, $fields);

        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Update'.$entityClass->getShortName().'Action',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());
        $useStatements = new UseStatementGenerator([
            $entityClass->getFullName(),
            AsController::class,
            Route::class,
            IsGranted::class,
            Flush::class,
            UserRole::class,
            Request::class,
            ApiRequestValueResolver::class,
            ValueResolver::class,
            ApiObjectResponse::class,
        ]);

        $route = '/admin/'.lcfirst($shortEntityClass).'s/{id}';
        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/update/UpdateAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_PUT]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    /**
     * @param list<array<string, string>> $fields
     */
    private function generateUpdateRequest(
        string $namespacePrefix,
        ClassNameDetails $entityClass,
        string $entityTitle,
        array $fields,
    ): void {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: 'Update'.$entityClass->getShortName().'Request',
            namespacePrefix: $namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($entityClass->getShortName());

        $this->generator->generateClass(
            className: $createActionDetails->getFullName(),
            templateName: 'http/update/UpdateRequest.tpl.php',
            variables: [
                'entity_classname' => $shortEntityClass,
                'entity_title' => $entityTitle,
                'entity_fields' => $fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($fields),
            ],
        );
    }

    /**
     * @param list<ClassProperty> $fields
     */
    private function generateOpenApi(
        ClassNameDetails $entityClass,
        string $entityTitle,
        array $fields,
    ): void {
        $loader = new FilesystemLoader('src-dev/Maker/Resources');
        $twig = new Environment($loader);

        $moduleName = lcfirst($entityClass->getShortName());
        $result = $twig->render('openapi.yaml.twig', [
            'tagName' => "admin-{$moduleName}",
            'name' => $entityTitle,
            'id' => 'admin'.$entityClass->getShortName().'Id',
            'listName' => 'getAdmin'.$entityClass->getShortName().'List',
            'infoName' => 'getAdmin'.$entityClass->getShortName(),
            'createName' => 'createAdmin'.$entityClass->getShortName(),
            'updateName' => 'updateAdmin'.$entityClass->getShortName(),
            'deleteName' => 'deleteAdmin'.$entityClass->getShortName(),
            'uri' => "/admin/{$moduleName}s",
            'fields' => $fields,
        ]);

        $this->filesystem->dumpFile(
            filename: "src-dev/OpenApi/resources/admin/{$moduleName}.yaml",
            content: $result,
        );
    }
}
