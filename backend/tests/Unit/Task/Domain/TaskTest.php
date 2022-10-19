<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\AddCommentToCompletedTaskException;
use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use App\Task\Domain\TaskComment;
use App\Task\Domain\TaskCommentBody;
use App\Task\Domain\TaskCommentId;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class TaskTest extends TestCase
{
    public function testAlreadyCompletedTask(): void
    {
        $userId = Uuid::v4();
        $task = new Task(new TaskId(), new TaskName('new task'), $userId);
        $task->markAsDone();

        $this->expectException(TaskAlreadyIsDoneException::class);
        $task->markAsDone();
    }

    public function testAddCommentToCompletedTask(): void
    {
        $userId = Uuid::v4();
        $task = new Task(new TaskId(), new TaskName('new task'), $userId);
        $task->markAsDone();

        $comment = new TaskComment($task, new TaskCommentId(), new TaskCommentBody('Комментарий'));

        $this->expectException(AddCommentToCompletedTaskException::class);
        $task->addComment($comment);
    }
}
