<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\Pagination\PaginationRequest;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка экспорта задач
 */
#[IsGranted(UserRole::User->value)]
#[Route('/export/tasks.{format}', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class ExportTaskAction
{
    public function __construct(private ExportTasks $exportTasks)
    {
    }

    public function __invoke(Format $format, UserId $userId, PaginationRequest $paginationRequest): BinaryFileResponse
    {
        try {
            return ($this->exportTasks)(
                $format,
                $userId,
                $paginationRequest->limit,
                $paginationRequest->offset
            );
        } catch (NotFoundTasksForExportException $e) {
            throw new ApiBadResponseException($e->getMessage(), ApiErrorCode::NotFoundTasksForExport);
        }
    }
}
