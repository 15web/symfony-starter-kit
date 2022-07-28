<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Model;

use App\Task\Model\Task;
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

        $task = new Task($taskId = Uuid::v4(), new TaskName($expectedName = 'new task'), $user->getId());

        self::assertSame($taskId, $task->getId());
        self::assertSame($expectedName, $task->getTaskName()->getValue());
        self::assertSame($user->getId(), $task->getUserId());
        self::assertFalse($task->isCompleted());
        self::assertNotNull($task->getCreatedAt());
        self::assertNull($task->getUpdatedAt());
        self::assertNull($task->getCompletedAt());
    }

    public function testCorrectChangeName(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());
        $task->changeTaskName(new TaskName($expectedNewName = 'changed task'));

        self::assertSame($expectedNewName, $task->getTaskName()->getValue());
        self::assertNotNull($task->getUpdatedAt());
    }

    public function testMarkAsDone(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        self::assertTrue($task->isCompleted());
        self::assertNotNull($task->getUpdatedAt());
        self::assertNotNull($task->getCompletedAt());
    }

    public function testAlreadyCompletedTask(): void
    {
        $user = new User(new UserEmail('test@example.com'));
        $task = new Task(Uuid::v4(), new TaskName('new task'), $user->getId());
        $task->markAsDone();

        $this->expectException(\DomainException::class);
        $task->markAsDone();
    }
}
