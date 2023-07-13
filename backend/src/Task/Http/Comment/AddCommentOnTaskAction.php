<?php

declare(strict_types=1);

namespace App\Task\Http\Comment;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\Comment\Add\AddCommentOnTask;
use App\Task\Command\Comment\Add\AddCommentOnTaskCommand;
use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskCommentId;
use App\Task\Http\TaskArgumentValueResolver;
use App\User\SignUp\Domain\UserRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка добавления комментария для задачи
 */
#[IsGranted(UserRole::User->value)]
#[Route('/tasks/{id}/add-comment', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class AddCommentOnTaskAction
{
    public function __construct(
        private AddCommentOnTask $addCommentOnTask,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        #[ValueResolver(TaskArgumentValueResolver::class)] Task $task,
        #[ApiRequest] AddCommentOnTaskCommand $addCommentOnTaskCommand,
    ): SuccessResponse {
        try {
            $commentId = new TaskCommentId();
            ($this->addCommentOnTask)($addCommentOnTaskCommand, $task, $commentId);

            ($this->flush)();

            $this->logger->info('Задача прокомментирована', [
                'taskId' => $task->getTaskId(),
                'commentId' => $commentId->getValue(),
                self::class => __FUNCTION__,
            ]);
        } catch (AddCommentToCompletedTaskException $exception) {
            throw new ApiBadRequestException($exception->getMessage());
        }

        return new SuccessResponse();
    }
}
