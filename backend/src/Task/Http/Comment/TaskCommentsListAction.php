<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Infrastructure\Response\ApiListObjectResponse;
use App\Infrastructure\Response\Pagination\PaginationResponse;
use App\Task\Query\Comment\FindAll\CommentData;
use App\Task\Query\Comment\FindAll\FindAllCommentQuery;
use App\Task\Query\Comment\FindAll\FindAllCommentsByTaskIdAndUserId;
use App\User\SignUp\Domain\UserId;
use App\User\SignUp\Domain\UserRole;
use App\User\SignUp\Http\UserIdArgumentValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Ручка списка комментариев для задачи по пользователю
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/comments', methods: [Request::METHOD_GET])]
#[AsController]
final readonly class TaskCommentsListAction
{
    public function __construct(private FindAllCommentsByTaskIdAndUserId $findAllComments) {}

    public function __invoke(
        #[ValueResolver(UidValueResolver::class)]
        Uuid $id,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ApiListObjectResponse {
        $taskComments = $this->buildResponseData($id, $userId);

        return new ApiListObjectResponse(
            data: $taskComments,
            pagination: new PaginationResponse(total: \count($taskComments))
        );
    }

    /**
     * @return CommentData[]
     */
    private function buildResponseData(Uuid $id, UserId $userId): array
    {
        return ($this->findAllComments)(
            findAllQuery: new FindAllCommentQuery(
                taskId: $id,
                userId: $userId->value,
            ),
        );
    }
}
