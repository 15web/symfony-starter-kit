<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Request\Pagination\PaginationRequest;
use App\Infrastructure\Request\Pagination\PaginationRequestArgumentResolver;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка экспорта задач
 */
#[IsGranted(UserRole::User)]
#[Route('/export/tasks.{format}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ExportTaskAction
{
    public function __construct(private ExportTasks $exportTasks) {}

    public function __invoke(
        #[ValueResolver(BackedEnumValueResolver::class)]
        Format $format,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
        #[ValueResolver(PaginationRequestArgumentResolver::class)]
        PaginationRequest $paginationRequest,
    ): BinaryFileResponse {
        try {
            return ($this->exportTasks)(
                format: $format,
                userId: $userId,
                limit: $paginationRequest->limit,
                offset: $paginationRequest->offset
            );
        } catch (NotFoundTasksForExportException) {
            throw new ApiBadResponseException(
                errors: ['Задачи для экспорта не найдены'],
                apiCode: ApiErrorCode::NotFoundTasksForExport,
            );
        }
    }
}
