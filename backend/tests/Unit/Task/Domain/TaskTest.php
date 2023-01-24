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
use App\User\SignUp\Domain\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @testdox Тестируемый класс: (App\Tests\Unit\Task\Domain\Task)
 */
final class TaskTest extends TestCase
{
    /**
     * @testdox Проверка метода testAlreadyCompletedTask прошла успешно
     */
    public function testAlreadyCompletedTask(): void
    {
        $task = new Task(new TaskId(), new TaskName('new task'), new UserId());
        $task->markAsDone();

        $this->expectException(TaskAlreadyIsDoneException::class);
        $task->markAsDone();
    }

    /**
     * @testdox Проверка метода testAddCommentToCompletedTask прошла успешно
     */
    public function testAddCommentToCompletedTask(): void
    {
        $task = new Task(new TaskId(), new TaskName('new task'), new UserId());
        $task->markAsDone();

        $comment = new TaskComment($task, new TaskCommentId(), new TaskCommentBody('Комментарий'));

        $this->expectException(AddCommentToCompletedTaskException::class);
        $task->addComment($comment);
    }
}
