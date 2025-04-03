<?php

declare(strict_types=1);

namespace Dev\Maker\Command;

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
use Dev\Maker\ClassGenerator;
use Dev\Maker\Entity\EntityFieldsManipulator;
use InvalidArgumentException;
use Override;
use Symfony\Bundle\MakerBundle\Doctrine\EntityRelation;
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
final class GenerateAction
{
    private ClassNameDetails $entityClassDetails;

    private string $namespacePrefix;

    private string $entityTitle;

    private string $repoClassName;

    /**
     * @var list<ClassProperty|EntityRelation>
     */
    private array $fields;

    public function __construct(
        private readonly ClassGenerator $generator,
        private readonly EntityFieldsManipulator $entityFieldsManipulator,
        private readonly Filesystem $filesystem,
    ) {}

    /**
     * @param list<ClassProperty|EntityRelation> $fields
     */
    public function __invoke(
        string $moduleName,
        ClassNameDetails $entityClassDetails,
        string $entityTitle,
        array $fields,
    ): void {
        $this->namespacePrefix = "{$moduleName}\\Http\\Admin\\";
        $this->entityClassDetails = $entityClassDetails;
        $this->entityTitle = $entityTitle;
        $this->repoClassName = "{$entityClassDetails->getShortName()}Repository";
        $this->fields = $fields;

        $this->generateEntityArgumentValueResolver();
        $this->generateInfoAction();
        $this->generateCreateAction();
        $this->generateUpdateAction();
        $this->generateRemoveAction();
        $this->generateListAction();
        $this->generateOpenApi();

        $this->generator->writeChanges();
    }

    private function generateEntityArgumentValueResolver(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            "{$this->entityClassDetails->getShortName()}ArgumentValueResolver",
            $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            $this->entityClassDetails->getFullName(),
            "{$this->entityClassDetails->getFullName()}Repository",
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

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/EntityArgumentValueResolver.tpl.php',
            variables: [
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_repository' => $this->repoClassName,
                'entity_title' => $this->entityTitle,
            ],
        );
    }

    private function generateInfoAction(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Get{$this->entityClassDetails->getShortName()}Action",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            $this->entityClassDetails->getFullName(),
            AsController::class,
            Route::class,
            Request::class,
            ValueResolver::class,
            ApiObjectResponse::class,
            IsGranted::class,
            UserRole::class,
        ]);

        $route = \sprintf('/admin/%ss/{id}', lcfirst($shortEntityClass));

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/InfoAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_GET]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateRemoveAction(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Delete{$this->entityClassDetails->getShortName()}Action",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            $this->entityClassDetails->getFullName(),
            $this->entityClassDetails->getFullName().'Repository',
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

        $route = \sprintf('/admin/%ss/{id}', lcfirst($shortEntityClass));

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/RemoveAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'repository_classname' => $this->repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_DELETE]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateListAction(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Get{$this->entityClassDetails->getShortName()}ListAction",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            "{$this->entityClassDetails->getFullName()}Repository",
            AsController::class,
            Route::class,
            IsGranted::class,
            UserRole::class,
            ApiListObjectResponse::class,
            Request::class,
            PaginationResponse::class,
        ]);

        $route = \sprintf('/admin/%ss', lcfirst($shortEntityClass));

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/ListAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'repository_classname' => $this->repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_GET]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateCreateAction(): void
    {
        $this->generateCreateRequest();

        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Create{$this->entityClassDetails->getShortName()}Action",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            $this->entityClassDetails->getFullName(),
            "{$this->entityClassDetails->getFullName()}Repository",
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

        $route = \sprintf('/admin/%ss', lcfirst($shortEntityClass));

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/create/CreateAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'fields' => $this->fields,
                'repository_classname' => $this->repoClassName,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_POST]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateCreateRequest(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Create{$this->entityClassDetails->getShortName()}Request",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/create/CreateRequest.tpl.php',
            variables: [
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'entity_fields' => $this->fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($this->fields),
            ],
        );
    }

    private function generateUpdateAction(): void
    {
        $this->generateUpdateRequest();

        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Update{$this->entityClassDetails->getShortName()}Action",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $useStatements = new UseStatementGenerator([
            $this->entityClassDetails->getFullName(),
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

        $route = \sprintf('/admin/%ss/{id}', lcfirst($shortEntityClass));

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/update/UpdateAction.tpl.php',
            variables: [
                'action_classname' => $createActionDetails->getShortName(),
                'use_statements' => $useStatements,
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'fields' => $this->fields,
                'route_path' => $route,
                'method' => 'methods: [Request::METHOD_PUT]',
                'role' => 'UserRole::Admin',
            ],
        );
    }

    private function generateUpdateRequest(): void
    {
        $createActionDetails = $this->generator->createClassNameDetails(
            name: "Update{$this->entityClassDetails->getShortName()}Request",
            namespacePrefix: $this->namespacePrefix,
        );

        $shortEntityClass = Str::getShortClassName($this->entityClassDetails->getShortName());

        $this->generator->generate(
            className: $createActionDetails->getFullName(),
            templateName: 'http/update/UpdateRequest.tpl.php',
            variables: [
                'entity_classname' => $shortEntityClass,
                'entity_title' => $this->entityTitle,
                'entity_fields' => $this->fields,
                'properties' => $this->entityFieldsManipulator->getConstructorProperties($this->fields),
            ],
        );
    }

    private function generateOpenApi(): void
    {
        $twig = new Environment(
            new FilesystemLoader('src-dev/Maker/Resources'),
        );

        $moduleName = lcfirst($this->entityClassDetails->getShortName());

        $result = $twig->render('openapi.yaml.twig', [
            'tagName' => "admin-{$moduleName}",
            'name' => $this->entityTitle,
            'id' => "admin{$this->entityClassDetails->getShortName()}Id",
            'listName' => "getAdmin{$this->entityClassDetails->getShortName()}List",
            'infoName' => "getAdmin{$this->entityClassDetails->getShortName()}",
            'createName' => "createAdmin{$this->entityClassDetails->getShortName()}",
            'updateName' => "updateAdmin{$this->entityClassDetails->getShortName()}",
            'deleteName' => "deleteAdmin{$this->entityClassDetails->getShortName()}",
            'uri' => "/admin/{$moduleName}s",
            'fields' => $this->fields,
        ]);

        $this->filesystem->dumpFile(
            filename: "src-dev/OpenApi/resources/admin/{$moduleName}.yaml",
            content: $result,
        );
    }
}
