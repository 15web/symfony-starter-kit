<?php

declare(strict_types=1);

namespace App\Task\Http\Site\Comment;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\Flush;
use App\Infrastructure\Request\ApiRequestValueResolver;
use App\Infrastructure\Response\ApiObjectResponse;
use App\Infrastructure\Response\SuccessResponse;
use App\Task\Command\Comment\Add\AddCommentOnTask;
use App\Task\Command\Comment\Add\AddCommentOnTaskCommand;
use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskCommentId;
use App\Task\Http\Site\TaskArgumentValueResolver;
use App\User\Security\Http\IsGranted;
use App\User\Security\Http\UserIdArgumentValueResolver;
use App\User\User\Domain\UserId;
use App\User\User\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ручка добавления комментария для задачи
 */
#[IsGranted(UserRole::User)]
#[Route('/tasks/{id}/add-comment', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class AddCommentOnTaskAction
{
    public function __construct(
        private AddCommentOnTask $addCommentOnTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        #[ValueResolver(TaskArgumentValueResolver::class)]
        Task $task,
        #[ValueResolver(ApiRequestValueResolver::class)]
        AddCommentOnTaskCommand $addCommentOnTaskCommand,
        #[ValueResolver(UserIdArgumentValueResolver::class)]
        UserId $userId,
    ): ApiObjectResponse {
        if (!$userId->equalTo($task->getUserId())) {
            throw new ApiNotFoundException(['Запись не найдена']);
        }

        try {
            $commentId = new TaskCommentId();
            ($this->addCommentOnTask)(
                command: $addCommentOnTaskCommand,
                task: $task,
                commentId: $commentId,
            );

            ($this->flush)();

            $this->logger->info('Задача прокомментирована', [
                'taskId' => $task->getTaskId(),
                'commentId' => $commentId->getValue(),
                self::class => __FUNCTION__,
            ]);
        } catch (AddCommentToCompletedTaskException) {
            throw new ApiBadRequestException(['Нельзя добавить комментарий в завершенную задачу']);
        }

        return new ApiObjectResponse(
            data: new SuccessResponse(),
        );
    }
}
