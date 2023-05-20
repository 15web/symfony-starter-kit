<?php

declare(strict_types=1);

namespace App\Task\Command\Comment\Add;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\AsService;
use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskComment;
use App\Task\Domain\TaskCommentBody;
use App\Task\Domain\TaskCommentId;

/**
 * Хендлер добавления комментария к задаче
 */
#[AsService]
final class AddCommentOnTask
{
    /**
     * @throws AddCommentToCompletedTaskException
     */
    public function __invoke(#[ApiRequest] AddCommentOnTaskCommand $command, Task $task, TaskCommentId $commentId): void
    {
        $comment = new TaskComment($task, $commentId, new TaskCommentBody($command->commentBody));
        $task->addComment($comment);
    }
}
