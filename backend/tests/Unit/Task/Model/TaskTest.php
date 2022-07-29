<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Model;

use App\Task\Model\Task;
use App\Task\Model\TaskAlreadyIsDoneException;
use App\Task\Model\TaskName;
use App\User\Model\User;
use App\User\Model\UserEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class TaskTest extends TestCase
{
    public function testCorrectCreation(): void
    {
        $user = new User(new UserEmail('test@example.com'));

        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());

        self::assertTrue($task->isBelongToUser($user->getId()));
    }
    public function testAlreadyCompletedTask(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        $this->expectException(TaskAlreadyIsDoneException::class);
        $task->markAsDone();
    }
}
