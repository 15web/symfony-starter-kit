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
use App\User\Domain\User;
use App\User\Domain\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class TaskTest extends TestCase
{
    public function testAlreadyCompletedTask(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(new TaskId(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        $this->expectException(TaskAlreadyIsDoneException::class);
        $task->markAsDone();
    }

    public function testAddCommentToCompletedTask(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(new TaskId(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        $comment = new TaskComment($task, new TaskCommentId(), new TaskCommentBody('Комментарий'));

        $this->expectException(AddCommentToCompletedTaskException::class);
        $task->addComment($comment);
    }
}
