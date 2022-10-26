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

#[AsService]
final class AddCommentOnTask
{
    public function __construct(private readonly Flush $flush)
    {
    }

    /**
     * @throws AddCommentToCompletedTaskException
     */
    public function __invoke(AddCommentOnTaskCommand $command, Task $task, TaskCommentId $commentId): void
    {
        $comment = new TaskComment($task, $commentId, new TaskCommentBody($command->commentBody));
        $task->addComment($comment);

        ($this->flush)();
    }
}
