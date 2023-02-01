<?php

declare(strict_types=1);

namespace App\Task\Command\Comment\Add;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskComment;
use App\Task\Domain\TaskCommentBody;
use App\Task\Domain\TaskCommentId;
use Psr\Log\LoggerInterface;

/**
 * Хендлер добавления комментария к задаче
 */
#[AsService]
final class AddCommentOnTask
{
    public function __construct(
        private readonly Flush $flush,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AddCommentToCompletedTaskException
     */
    public function __invoke(AddCommentOnTaskCommand $command, Task $task, TaskCommentId $commentId): void
    {
        $comment = new TaskComment($task, $commentId, new TaskCommentBody($command->commentBody));
        $task->addComment($comment);

        ($this->flush)();

        $this->logger->info('Задача прокомментирована', [
            'taskId' => $task->getTaskId(),
            'commentId' => $commentId->getValue(),
            self::class => __FUNCTION__,
        ]);
    }
}
