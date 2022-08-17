<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Model;

use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use App\Task\Domain\TaskId;
use App\Task\Domain\TaskName;
use App\User\Domain\User;
use App\User\Domain\UserEmail;
use PHPUnit\Framework\TestCase;

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
}
